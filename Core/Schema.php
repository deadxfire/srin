<?php

namespace Core;

/**
 * ============================================================================
 * Srin Engine - Database Schema Builder
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
class Schema
{
    /**
     * Create a new database table.
     */
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $driver = Database::getDriver();
        $sql = $blueprint->toSql($driver);

        Database::statement($sql);
    }

    /**
     * Drop a database table.
     */
    public static function drop(string $table): void
    {
        $driver = Database::getDriver();
        $ident = ($driver === 'pgsql') ? "\"{$table}\"" : "`{$table}`";
        Database::statement("DROP TABLE {$ident};");
    }

    /**
     * Drop a table if it exists.
     */
    public static function dropIfExists(string $table): void
    {
        $driver = Database::getDriver();
        $ident = ($driver === 'pgsql') ? "\"{$table}\"" : "`{$table}`";
        Database::statement("DROP TABLE IF EXISTS {$ident};");
    }

    /**
     * Determine if a given table exists in the database.
     */
    public static function hasTable(string $table): bool
    {
        $driver = Database::getDriver();

        if ($driver === 'sqlite') {
            $rows = Database::select("SELECT name FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
            return !empty($rows);
        }

        if ($driver === 'pgsql') {
            $rows = Database::select("SELECT table_name FROM information_schema.tables WHERE table_name = ?", [$table]);
            return !empty($rows);
        }

        // MySQL
        $db = config('database.connections.mysql.database');
        $rows = Database::select("SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_name = ?", [$db, $table]);
        return !empty($rows);
    }

    /**
     * Determine if a given column exists in a table.
     */
    public static function hasColumn(string $table, string $column): bool
    {
        $driver = Database::getDriver();

        if ($driver === 'sqlite') {
            $cols = Database::select("PRAGMA table_info(`{$table}`)");
            foreach ($cols as $col) {
                if (strcasecmp($col['name'], $column) === 0) {
                    return true;
                }
            }
            return false;
        }

        if ($driver === 'pgsql') {
            $rows = Database::select("SELECT column_name FROM information_schema.columns WHERE table_name = ? AND column_name = ?", [$table, $column]);
            return !empty($rows);
        }

        // MySQL
        $db = config('database.connections.mysql.database');
        $rows = Database::select("SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?", [$db, $table, $column]);
        return !empty($rows);
    }
}
