<?php

namespace Core;

use PDO;
use PDOException;
use Exception;

/**
 * ============================================================================
 * Srin Engine - Database Connection Manager & Query Gateway
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
class Database
{
    /**
     * Active PDO connection instances.
     * @var array<string, PDO>
     */
    protected static array $instances = [];

    /**
     * Get or create a PDO connection instance.
     */
    public static function connection(?string $name = null): PDO
    {
        $name = $name ?: config('database.default', 'sqlite');

        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $config = config("database.connections.{$name}");
        if (empty($config)) {
            throw new Exception("Database configuration for [{$name}] not found in Config/database.php.");
        }

        $driver = $config['driver'] ?? 'sqlite';

        try {
            switch ($driver) {
                case 'sqlite':
                    $pdo = self::connectSqlite($config);
                    break;

                case 'mysql':
                    $pdo = self::connectMysql($config);
                    break;

                case 'pgsql':
                    $pdo = self::connectPgsql($config);
                    break;

                default:
                    throw new Exception("Unsupported database driver: [{$driver}]");
            }

            self::$instances[$name] = $pdo;
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed [{$name}]: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Connect to SQLite database.
     */
    protected static function connectSqlite(array $config): PDO
    {
        $dbPath = $config['database'] ?? ':memory:';

        if ($dbPath !== ':memory:') {
            // Resolve relative path to project root
            if (!str_starts_with($dbPath, '/') && !preg_match('/^[a-zA-Z]:[\\\\\/]/', $dbPath)) {
                $dbPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dbPath);
            }

            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            // Create empty file if missing
            if (!file_exists($dbPath)) {
                @touch($dbPath);
            }
        }

        $pdo = new PDO("sqlite:{$dbPath}", null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        if (!empty($config['foreign_key_constraints'])) {
            $pdo->exec('PRAGMA foreign_keys = ON;');
        }

        return $pdo;
    }

    /**
     * Connect to MySQL database.
     */
    protected static function connectMysql(array $config): PDO
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $db = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

        $options = $config['options'] ?? [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', $options);
    }

    /**
     * Connect to PostgreSQL database.
     */
    protected static function connectPgsql(array $config): PDO
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 5432;
        $db = $config['database'] ?? '';

        $dsn = "pgsql:host={$host};port={$port};dbname={$db}";

        $pdo = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        if (!empty($config['schema'])) {
            $pdo->exec("SET search_path TO {$config['schema']}");
        }

        return $pdo;
    }

    /**
     * Start a new query on a database table.
     */
    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder($table);
    }

    /**
     * Execute a raw SQL query with parameter binding.
     */
    public static function raw(string $sql, array $bindings = []): \PDOStatement
    {
        $pdo = self::connection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    /**
     * Run a select query and return all results as associative array.
     */
    public static function select(string $sql, array $bindings = []): array
    {
        return self::raw($sql, $bindings)->fetchAll();
    }

    /**
     * Run a select query and return a single row or null.
     */
    public static function selectOne(string $sql, array $bindings = []): ?array
    {
        $res = self::raw($sql, $bindings)->fetch();
        return $res !== false ? $res : null;
    }

    /**
     * Run a select query and return a single scalar value.
     */
    public static function scalar(string $sql, array $bindings = []): mixed
    {
        $res = self::raw($sql, $bindings)->fetchColumn();
        return $res !== false ? $res : null;
    }

    /**
     * Run an insert statement and return the last insert ID.
     */
    public static function insert(string $sql, array $bindings = []): int|string
    {
        $pdo = self::connection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        return $pdo->lastInsertId();
    }

    /**
     * Run an update statement and return affected rows.
     */
    public static function update(string $sql, array $bindings = []): int
    {
        $stmt = self::raw($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * Run a delete statement and return affected rows.
     */
    public static function delete(string $sql, array $bindings = []): int
    {
        $stmt = self::raw($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * Execute a DDL / schema statement.
     */
    public static function statement(string $sql): bool
    {
        return self::connection()->exec($sql) !== false;
    }

    /**
     * Execute callback inside a database transaction.
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get the current database driver name (sqlite, mysql, pgsql).
     */
    public static function getDriver(): string
    {
        $default = config('database.default', 'sqlite');
        return config("database.connections.{$default}.driver", 'sqlite');
    }

    /**
     * Reset cached PDO connections (useful for tests and CLI tools).
     */
    public static function disconnect(): void
    {
        self::$instances = [];
    }

    /**
     * Fluent proxy instance for object-oriented database access via db().
     */
    protected static ?self $gatewayInstance = null;

    public static function gateway(): static
    {
        if (self::$gatewayInstance === null) {
            self::$gatewayInstance = new static();
        }
        return self::$gatewayInstance;
    }

    public function __call(string $method, array $parameters): mixed
    {
        return static::$method(...$parameters);
    }
}
