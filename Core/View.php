<?php

namespace Core;

use Exception;

class View
{
    /**
     * Base directory where view pages are located.
     */
    protected static string $pagesPath = __DIR__ . '/../Pages';

    /**
     * Render a view file with optional data.
     */
    public static function render(string $page, array $data = []): string
    {
        $pageFile = self::resolveFilePath($page);
        $realPages = realpath(self::$pagesPath);
        $realPageFile = realpath($pageFile);

        if (!$realPageFile || !file_exists($realPageFile)) {
            throw new Exception("Srin template [{$page}] not found at {$pageFile}. All templates must use the [.srin.php] extension.");
        }

        // Security check: Template must reside strictly within Pages directory
        if (!$realPages || !str_starts_with($realPageFile, $realPages)) {
            throw new Exception("Security violation: Template [{$page}] is located outside the Pages directory.");
        }

        $rawContent = file_get_contents($realPageFile);

        // Detect @Connect directive at the start of a line (case-insensitive: @connect or @Connect, ignoring @@Connect)
        $layout = null;
        if (preg_match('/^\s*(?<!@)@(?:connect|Connect)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?/mi', $rawContent, $layoutMatch)) {
            $layout = trim($layoutMatch[1]);
            // Remove the @connect directive line from page content
            $rawContent = preg_replace('/^\s*(?<!@)@(?:connect|Connect)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?\r?\n?/mi', '', $rawContent, 1);
        }

        // Detect @title directive at the start of a line (ignoring @@title)
        if (preg_match('/^\s*(?<!@)@title\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?/mi', $rawContent, $titleMatch)) {
            $data['title'] = $titleMatch[1];
            $rawContent = preg_replace('/^\s*(?<!@)@title\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?\r?\n?/mi', '', $rawContent, 1);
        }

        // Compile custom helper directives into PHP
        $compiledPage = self::compileDirectives($rawContent);

        // Render the page body
        $body = self::evaluate($compiledPage, $data);

        // If layout was specified via @Connect or $data['layout']
        $layoutToUse = $layout ?? ($data['layout'] ?? null);

        if ($layoutToUse) {
            return self::renderLayout($layoutToUse, $body, $data);
        }

        return $body;
    }

    /**
     * Render layout wrapping the page content.
     */
    public static function renderLayout(string $layout, string $content, array $data = []): string
    {
        $layoutFile = self::resolveFilePath($layout);

        // If not found in direct path, check inside Pages/layouts/
        if (!file_exists($layoutFile)) {
            $layoutFile = self::resolveFilePath('layouts/' . $layout);
        }

        if (!file_exists($layoutFile)) {
            throw new Exception("Srin layout [{$layout}] not found at {$layoutFile}. All layouts must use the [.srin.php] extension.");
        }

        $layoutRaw = file_get_contents($layoutFile);

        // Compile layout directives (@content, @include, @csrf, etc.)
        $compiledLayout = self::compileDirectives($layoutRaw);

        // Replace @content directive with $content variable output (ignoring @@content)
        $compiledLayout = preg_replace('/(?<!@)@content\b/', '<?= $content ?? \'\' ?>', $compiledLayout);

        $layoutData = array_merge($data, ['content' => $content]);

        return self::evaluate($compiledLayout, $layoutData);
    }

    /**
     * Render a partial view (e.g. navigation, footer, card).
     */
    public static function renderPartial(string $partial, array $data = []): string
    {
        $partialFile = self::resolveFilePath($partial);

        if (!file_exists($partialFile)) {
            $partialFile = self::resolveFilePath('partials/' . $partial);
        }

        if (!file_exists($partialFile)) {
            throw new Exception("Srin partial [{$partial}] not found at {$partialFile}. All partials must use the [.srin.php] extension.");
        }

        $raw = file_get_contents($partialFile);
        $compiled = self::compileDirectives($raw);

        return self::evaluate($compiled, $data);
    }

    /**
     * Compile lightweight template directives.
     */
    protected static function compileDirectives(string $template): string
    {
        // 1. Temporarily protect escaped directives (@@Connect, @@include, @@csrf, @@title, @@flash, @@content, @{{assets...}}, @{{route...}}, @{{url...}})
        $escapedTokens = [];
        $template = preg_replace_callback('/@(\{\{\s*(?:assets?|route|url)\s*\(.+?\)\s*\}\})/i', function ($m) use (&$escapedTokens) {
            $token = '%%ESCAPED_BRACES_' . count($escapedTokens) . '%%';
            $escapedTokens[$token] = $m[1];
            return $token;
        }, $template);

        $escaped = [
            '@@Connect' => '%%ESCAPED_AT_CONNECT%%',
            '@@connect' => '%%ESCAPED_AT_CONNECT%%',
            '@@title'   => '%%ESCAPED_AT_TITLE%%',
            '@@include' => '%%ESCAPED_AT_INCLUDE%%',
            '@@content' => '%%ESCAPED_AT_CONTENT%%',
            '@@csrf'    => '%%ESCAPED_AT_CSRF%%',
            '@@flash'   => '%%ESCAPED_AT_FLASH%%',
        ];
        $template = strtr($template, $escaped);

        // 2. Compile real directives
        // {{assets('/css/style.css')}} or {{asset('images/photo.png')}}
        $template = preg_replace(
            '/\{\{\s*assets?\s*\(\s*([\'"][^\'"]+[\'"])\s*\)\s*\}\}/i',
            '<?= assets($1) ?>',
            $template
        );

        // {{route('name')}} or {{route('name', ['id' => 5])}}
        $template = preg_replace(
            '/\{\{\s*route\s*\(\s*(.+?)\s*\)\s*\}\}/i',
            '<?= route($1) ?>',
            $template
        );

        // {{url('/path')}}
        $template = preg_replace(
            '/\{\{\s*url\s*\(\s*([\'"][^\'"]+[\'"])\s*\)\s*\}\}/i',
            '<?= url($1) ?>',
            $template
        );

        // @include('partials/nav') or @include('nav')
        $template = preg_replace(
            '/(?<!@)@include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?/i',
            '<?= \Core\View::renderPartial(\'$1\', get_defined_vars()) ?>',
            $template
        );

        // @csrf
        $template = preg_replace(
            '/(?<!@)@csrf\b/i',
            '<?= csrf_field() ?>',
            $template
        );

        // @flash('key')
        $template = preg_replace(
            '/(?<!@)@flash\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?/i',
            '<?php if ($flashMsg = flash(\'$1\')): ?><div class="alert alert-$1"><?= htmlspecialchars($flashMsg, ENT_QUOTES, \'UTF-8\') ?></div><?php endif; ?>',
            $template
        );

        // 3. Restore escaped directives as literal
        if (!empty($escapedTokens)) {
            $template = strtr($template, $escapedTokens);
        }

        $unEscape = [
            '%%ESCAPED_AT_CONNECT%%' => '@Connect',
            '%%ESCAPED_AT_TITLE%%'   => '@title',
            '%%ESCAPED_AT_INCLUDE%%' => '@include',
            '%%ESCAPED_AT_CONTENT%%' => '@content',
            '%%ESCAPED_AT_CSRF%%'    => '@csrf',
            '%%ESCAPED_AT_FLASH%%'   => '@flash',
        ];
        $template = strtr($template, $unEscape);

        return $template;
    }

    /**
     * Evaluate compiled template code in an isolated scope.
     */
    protected static function evaluate(string $code, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        try {
            eval('?>' . $code);
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() ?: '';
    }

    /**
     * Resolve full file path for a view name.
     * Enforces strictly the .srin.php template format.
     */
    protected static function resolveFilePath(string $name): string
    {
        // Strip any directory traversal attempts (../ or ..\)
        $name = str_replace(['../', '..\\'], '', $name);
        $clean = ltrim($name, '/\\');
        $base = rtrim(self::$pagesPath, '/\\') . DIRECTORY_SEPARATOR;

        // If user already specified .srin.php or .php, strip it to normalize
        if (str_ends_with($clean, '.srin.php')) {
            $clean = substr($clean, 0, -9);
        } elseif (str_ends_with($clean, '.php')) {
            $clean = substr($clean, 0, -4);
        }

        // Support dot notation: 'errors.404' -> 'errors/404'
        $clean = str_replace(['.', '/'], DIRECTORY_SEPARATOR, $clean);
        $clean = str_replace('..' . DIRECTORY_SEPARATOR, '', $clean);

        // Check for .srin.php (Official template extension for Srin Engine)
        $srinFile = $base . $clean . '.srin.php';
        return $srinFile;
    }
}
