<?php

namespace Core;

/**
 * ============================================================================
 * Srin Engine - Migration Schema Blueprint
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
class Blueprint
{
    protected string $table;
    protected array $columns = [];
    protected array $commands = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Auto-incrementing primary key ID.
     */
    public function id(string $name = 'id'): FluentColumn
    {
        return $this->addColumn('id', $name);
    }

    /**
     * VARCHAR string column.
     */
    public function string(string $name, int $length = 255): FluentColumn
    {
        return $this->addColumn('string', $name, ['length' => $length]);
    }

    /**
     * TEXT column.
     */
    public function text(string $name): FluentColumn
    {
        return $this->addColumn('text', $name);
    }

    /**
     * INTEGER column.
     */
    public function integer(string $name): FluentColumn
    {
        return $this->addColumn('integer', $name);
    }

    /**
     * BIGINT column.
     */
    public function bigInteger(string $name): FluentColumn
    {
        return $this->addColumn('bigInteger', $name);
    }

    /**
     * BOOLEAN column.
     */
    public function boolean(string $name): FluentColumn
    {
        return $this->addColumn('boolean', $name);
    }

    /**
     * FLOAT column.
     */
    public function float(string $name): FluentColumn
    {
        return $this->addColumn('float', $name);
    }

    /**
     * DECIMAL column.
     */
    public function decimal(string $name, int $precision = 8, int $scale = 2): FluentColumn
    {
        return $this->addColumn('decimal', $name, ['precision' => $precision, 'scale' => $scale]);
    }

    /**
     * DATE column.
     */
    public function date(string $name): FluentColumn
    {
        return $this->addColumn('date', $name);
    }

    /**
     * DATETIME column.
     */
    public function dateTime(string $name): FluentColumn
    {
        return $this->addColumn('dateTime', $name);
    }

    /**
     * TIMESTAMP column.
     */
    public function timestamp(string $name): FluentColumn
    {
        return $this->addColumn('timestamp', $name);
    }

    /**
     * Add created_at and updated_at nullable timestamps.
     */
    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
    }

    /**
     * Add column definition.
     */
    protected function addColumn(string $type, string $name, array $params = []): FluentColumn
    {
        $col = new FluentColumn($type, $name, $params);
        $this->columns[] = $col;
        return $col;
    }

    /**
     * Compile table creation SQL based on database driver.
     */
    public function toSql(string $driver): string
    {
        $colSqls = [];

        foreach ($this->columns as $col) {
            $colSqls[] = $this->compileColumn($col, $driver);
        }

        $colString = implode(",\n    ", $colSqls);

        if ($driver === 'sqlite') {
            return "CREATE TABLE IF NOT EXISTS `{$this->table}` (\n    {$colString}\n);";
        }

        if ($driver === 'pgsql') {
            $table = str_replace('`', '"', $this->table);
            $colString = str_replace('`', '"', $colString);
            return "CREATE TABLE IF NOT EXISTS \"{$table}\" (\n    {$colString}\n);";
        }

        // MySQL
        return "CREATE TABLE IF NOT EXISTS `{$this->table}` (\n    {$colString}\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }

    /**
     * Compile a single column definition for target driver.
     */
    protected function compileColumn(FluentColumn $col, string $driver): string
    {
        $name = "`{$col->name}`";
        $type = $col->type;
        $params = $col->params;

        switch ($type) {
            case 'id':
                if ($driver === 'sqlite') {
                    return "{$name} INTEGER PRIMARY KEY AUTOINCREMENT";
                }
                if ($driver === 'pgsql') {
                    return "\"{$col->name}\" BIGSERIAL PRIMARY KEY";
                }
                return "{$name} BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";

            case 'string':
                $len = $params['length'] ?? 255;
                $sqlType = "VARCHAR({$len})";
                break;

            case 'text':
                $sqlType = "TEXT";
                break;

            case 'integer':
                $sqlType = "INT";
                break;

            case 'bigInteger':
                $sqlType = "BIGINT";
                break;

            case 'boolean':
                $sqlType = ($driver === 'sqlite') ? 'INTEGER' : (($driver === 'pgsql') ? 'BOOLEAN' : 'TINYINT(1)');
                break;

            case 'float':
                $sqlType = "FLOAT";
                break;

            case 'decimal':
                $p = $params['precision'] ?? 8;
                $s = $params['scale'] ?? 2;
                $sqlType = "DECIMAL({$p}, {$s})";
                break;

            case 'date':
                $sqlType = "DATE";
                break;

            case 'dateTime':
            case 'timestamp':
                $sqlType = ($driver === 'sqlite') ? 'DATETIME' : (($driver === 'pgsql') ? 'TIMESTAMP' : 'DATETIME');
                break;

            default:
                $sqlType = "VARCHAR(255)";
        }

        $def = "{$name} {$sqlType}";

        if ($col->isNullable) {
            $def .= " NULL";
        } else {
            $def .= " NOT NULL";
        }

        if ($col->default !== null) {
            $default = is_bool($col->default) ? ($col->default ? '1' : '0') : (is_numeric($col->default) ? $col->default : "'{$col->default}'");
            $def .= " DEFAULT {$default}";
        }

        if ($col->isUnique) {
            $def .= " UNIQUE";
        }

        return $def;
    }
}

/**
 * Fluent Column modifier object.
 */
class FluentColumn
{
    public string $type;
    public string $name;
    public array $params;
    public bool $isNullable = false;
    public bool $isUnique = false;
    public mixed $default = null;

    public function __construct(string $type, string $name, array $params = [])
    {
        $this->type = $type;
        $this->name = $name;
        $this->params = $params;
    }

    public function nullable(): self
    {
        $this->isNullable = true;
        return $this;
    }

    public function unique(): self
    {
        $this->isUnique = true;
        return $this;
    }

    public function default(mixed $value): self
    {
        $this->default = $value;
        return $this;
    }
}
