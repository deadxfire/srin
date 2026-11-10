<?php

namespace Core;

/**
 * ============================================================================
 * Srin Engine - Abstract Base Command
 * ============================================================================
 * Base class for all custom CLI commands and cPanel cron jobs in Command/
 *
 * Provides terminal styling, user interaction helpers, and clean execution
 * lifecycle for background tasks, cron jobs, and custom CLI automation.
 * ============================================================================
 */
abstract class Command
{
    /**
     * The command signature / CLI trigger name (e.g. 'send-weekly-digest').
     */
    public string $name = '';

    /**
     * Brief description of what the command does.
     */
    public string $description = '';

    /**
     * Execute the command.
     *
     * @param array $args Command line arguments passed after the command name.
     * @return int Exit code (0 for success, non-zero for failure)
     */
    public function execute(array $args): int
    {
        if (method_exists($this, 'handle')) {
            return (int) $this->handle($args);
        }

        return 0;
    }

    /**
     * Output standard text line.
     */
    public function line(string $message = ''): void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Output cyan informational message.
     */
    public function info(string $message): void
    {
        echo $this->color('[i] ' . $message, 'cyan') . PHP_EOL;
    }

    /**
     * Output green success message.
     */
    public function success(string $message): void
    {
        echo $this->color('[✓] ' . $message, 'green') . PHP_EOL;
    }

    /**
     * Output yellow warning message.
     */
    public function warning(string $message): void
    {
        echo $this->color('[!] ' . $message, 'yellow') . PHP_EOL;
    }

    /**
     * Output red error message.
     */
    public function error(string $message): void
    {
        echo $this->color('[X] ' . $message, 'red') . PHP_EOL;
    }

    /**
     * Format an ASCII table in the console output.
     *
     * @param array<string> $headers
     * @param array<array<string>> $rows
     */
    public function table(array $headers, array $rows): void
    {
        $colWidths = [];
        foreach ($headers as $i => $h) {
            $colWidths[$i] = strlen((string) $h);
        }

        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $colWidths[$i] = max($colWidths[$i] ?? 0, strlen((string) $cell));
            }
        }

        $separator = '+';
        foreach ($colWidths as $w) {
            $separator .= str_repeat('-', $w + 2) . '+';
        }

        $this->line($separator);
        $headerLine = '|';
        foreach ($headers as $i => $h) {
            $headerLine .= ' ' . str_pad((string) $h, $colWidths[$i]) . ' |';
        }
        $this->line($this->color($headerLine, 'bold'));
        $this->line($separator);

        foreach ($rows as $row) {
            $rowLine = '|';
            foreach ($headers as $i => $h) {
                $cell = $row[$i] ?? '';
                $rowLine .= ' ' . str_pad((string) $cell, $colWidths[$i]) . ' |';
            }
            $this->line($rowLine);
        }

        $this->line($separator);
    }

    /**
     * Ask a yes/no confirmation prompt in interactive CLI.
     */
    public function confirm(string $question, bool $default = false): bool
    {
        $prompt = $default ? '[Y/n]' : '[y/N]';
        echo $this->color("? {$question} {$prompt} ", 'yellow');
        $handle = fopen('php://stdin', 'r');
        $line = trim((string) fgets($handle));
        fclose($handle);

        if ($line === '') {
            return $default;
        }

        return in_array(strtolower($line), ['y', 'yes', 'true', '1'], true);
    }

    /**
     * Ask for input in interactive CLI.
     */
    public function ask(string $question, ?string $default = null): ?string
    {
        $suffix = $default !== null ? " [{$default}]" : '';
        echo $this->color("? {$question}{$suffix}: ", 'yellow');
        $handle = fopen('php://stdin', 'r');
        $line = trim((string) fgets($handle));
        fclose($handle);

        return $line !== '' ? $line : $default;
    }

    /**
     * Color formatting helper for terminal output.
     */
    protected function color(string $text, string $color): string
    {
        $colors = [
            'green'  => "\033[32m",
            'red'    => "\033[31m",
            'yellow' => "\033[33m",
            'blue'   => "\033[34m",
            'cyan'   => "\033[36m",
            'bold'   => "\033[1m",
            'reset'  => "\033[0m",
        ];

        return ($colors[$color] ?? '') . $text . ($colors['reset'] ?? '');
    }
}
