<?php

namespace Core;

use Exception;

/**
 * ============================================================================
 * Srin Engine - Migration Engine & Runner
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
class Migrator
{
    protected string $migrationsPath;
    protected string $table;

    public function __construct(?string $migrationsPath = null)
    {
        $this->migrationsPath = $migrationsPath ?: dirname(__DIR__) . '/database/migrations';
        $this->table = config('database.migrations', 'migrations');
    }

    /**
     * Ensure the internal migrations table exists.
     */
    public function ensureTableExists(): void
    {
        if (!is_dir($this->migrationsPath)) {
            @mkdir($this->migrationsPath, 0777, true);
        }

        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
                $table->timestamp('created_at')->default(date('Y-m-d H:i:s'));
            });
        }
    }

    /**
     * Run all pending migrations.
     *
     * @return array<string> List of migrated filenames
     */
    public function run(?callable $logger = null): array
    {
        $this->ensureTableExists();

        $files = $this->getMigrationFiles();
        $ran = $this->getRanMigrations();

        $pending = array_diff($files, $ran);

        if (empty($pending)) {
            return [];
        }

        $batch = $this->getNextBatchNumber();
        $applied = [];

        foreach ($pending as $file) {
            if ($logger) {
                $logger('migrating', $file);
            }

            $instance = $this->resolveMigrationInstance($file);
            $instance->up();

            Database::table($this->table)->insert([
                'migration'  => $file,
                'batch'      => $batch,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $applied[] = $file;

            if ($logger) {
                $logger('migrated', $file);
            }
        }

        return $applied;
    }

    /**
     * Rollback the last batch of migrations.
     *
     * @return array<string> List of rolled-back filenames
     */
    public function rollback(?callable $logger = null): array
    {
        $this->ensureTableExists();

        $lastBatch = $this->getLastBatchNumber();
        if ($lastBatch === 0) {
            return [];
        }

        $records = Database::table($this->table)
            ->where('batch', $lastBatch)
            ->orderBy('id', 'DESC')
            ->get();

        $rolledBack = [];

        foreach ($records as $record) {
            $file = $record['migration'];

            if ($logger) {
                $logger('rolling_back', $file);
            }

            $instance = $this->resolveMigrationInstance($file);
            $instance->down();

            Database::table($this->table)->where('id', $record['id'])->delete();

            $rolledBack[] = $file;

            if ($logger) {
                $logger('rolled_back', $file);
            }
        }

        return $rolledBack;
    }

    /**
     * Get migration status list.
     *
     * @return array<array{migration: string, ran: bool, batch: ?int, created_at: ?string}>
     */
    public function status(): array
    {
        $this->ensureTableExists();

        $files = $this->getMigrationFiles();
        $records = Database::table($this->table)->get();

        $ranMap = [];
        foreach ($records as $r) {
            $ranMap[$r['migration']] = $r;
        }

        $status = [];
        foreach ($files as $file) {
            $isRan = isset($ranMap[$file]);
            $status[] = [
                'migration'  => $file,
                'ran'        => $isRan,
                'batch'      => $isRan ? (int) $ranMap[$file]['batch'] : null,
                'created_at' => $isRan ? ($ranMap[$file]['created_at'] ?? null) : null,
            ];
        }

        return $status;
    }

    /**
     * Drop all tables and re-run all migrations from scratch.
     */
    public function fresh(?callable $logger = null): array
    {
        $driver = Database::getDriver();

        if ($driver === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            Database::disconnect();
            if ($dbPath !== ':memory:' && file_exists($dbPath)) {
                @unlink($dbPath);
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: query information_schema for user tables
            $tables = Database::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
            if (!empty($tables)) {
                foreach ($tables as $t) {
                    $tableName = reset($t);
                    Database::statement("DROP TABLE IF EXISTS \"{$tableName}\" CASCADE;");
                }
            }
        } else {
            // MySQL / MariaDB: drop known tables with foreign key checks toggled
            $tables = Database::select("SHOW TABLES");
            if (!empty($tables)) {
                Database::statement("SET FOREIGN_KEY_CHECKS = 0;");
                foreach ($tables as $t) {
                    $tableName = reset($t);
                    Schema::dropIfExists($tableName);
                }
                Database::statement("SET FOREIGN_KEY_CHECKS = 1;");
            }
        }

        return $this->run($logger);
    }

    /**
     * Get all migration files from database/migrations/ sorted.
     *
     * @return array<string>
     */
    protected function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = scandir($this->migrationsPath);
        if ($files === false) {
            return [];
        }

        $migrations = [];
        foreach ($files as $file) {
            if (str_ends_with($file, '.php')) {
                $migrations[] = substr($file, 0, -4);
            }
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Get list of already executed migrations from database.
     *
     * @return array<string>
     */
    protected function getRanMigrations(): array
    {
        $rows = Database::table($this->table)->select('migration')->get();
        return array_column($rows, 'migration');
    }

    /**
     * Get next migration batch number.
     */
    protected function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * Get the highest batch number recorded.
     */
    protected function getLastBatchNumber(): int
    {
        $max = Database::table($this->table)->max('batch');
        return (int) ($max ?? 0);
    }

    /**
     * Resolve a Migration class instance from a filename.
     */
    protected function resolveMigrationInstance(string $filename): Migration
    {
        $filePath = $this->migrationsPath . '/' . $filename . '.php';
        if (!file_exists($filePath)) {
            throw new Exception("Migration file not found: [{$filePath}]");
        }

        $result = require $filePath;

        // Anonymous class instance: return new class extends Migration { ... };
        if ($result instanceof Migration) {
            return $result;
        }

        // Named class: YYYY_MM_DD_HHMMSS_create_users_table -> CreateUsersTable
        $cleanName = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $filename);
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $cleanName)));

        if (class_exists($className)) {
            $obj = new $className();
            if ($obj instanceof Migration) {
                return $obj;
            }
        }

        throw new Exception("Migration in [{$filename}.php] must return an instance of Core\\Migration or declare a class extending Core\\Migration.");
    }
}
