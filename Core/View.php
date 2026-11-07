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

        // 3. Extract named content sections (@contentConnect or @contentCconnect)
        $sections = [];
        $sectionPattern = '/(?<!@)@(?:contentConnect|contentCconnect)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)(.*?)(?<!@)@(?:endContentConnect|endContentCconnect|endcontentconnect|endcontentcconnect)\b/si';

        $rawContent = preg_replace_callback($sectionPattern, function ($m) use (&$sections, $data) {
            $name = trim($m[1]);
            $blockRaw = $m[2];
            $compiledBlock = self::compileDirectives($blockRaw);
            $sections[$name] = self::evaluate($compiledBlock, $data);
            return '';
        }, $rawContent);

        // 4. Compile and evaluate remaining content (default @content)
        $compiledPage = self::compileDirectives($rawContent);
        $body = self::evaluate($compiledPage, $data);

        // Register default content aliases
        $sections['default'] = $body;
        $sections['content'] = $sections['content'] ?? $body;
        $sections['main']    = $sections['main'] ?? $body;
        $sections['body']    = $sections['body'] ?? $body;

        // 5. If layout was specified via @Connect or $data['layout']
        $layoutToUse = $layout ?? ($data['layout'] ?? null);

        if ($layoutToUse) {
            return self::renderLayout($layoutToUse, $body, $data, $sections);
        }

        return $body;
    }

    /**
     * Render layout wrapping the page content and injecting named sections.
     */
    public static function renderLayout(string $layout, string $content, array $data = [], array $sections = []): string
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

        // Compile layout directives (@include, {{ }}, @csrf, etc.)
        $compiledLayout = self::compileDirectives($layoutRaw);

        // 1. Replace named @content('slot_name', 'optional default') directives
        $compiledLayout = preg_replace_callback(
            '/(?<!@)@content\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*([\'"].*?[\'"]|[^)]+))?\s*\)/i',
            function ($m) {
                $slot = addslashes(trim($m[1]));
                if (isset($m[2]) && trim($m[2]) !== '') {
                    $default = trim($m[2]);
                    return "<?= \$sections['{$slot}'] ?? ({$default}) ?>";
                }
                return "<?= \$sections['{$slot}'] ?? '' ?>";
            },
            $compiledLayout
        );

        // 2. Replace generic @content directive with $content variable output
        $compiledLayout = preg_replace(
            '/(?<!@)@content\b(?!\s*\()/i',
            "<?= \$content ?? (\$sections['content'] ?? '') ?>",
            $compiledLayout
        );

        $layoutData = array_merge($data, [
            'content'  => $content,
            'sections' => $sections,
        ]);

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
     * Enforces strict XSS escaping on all {{ $expression }} outputs.
     */
    protected static function compileDirectives(string $template): string
    {
        // 1. Strip secure template comments: {{-- anything --}}
        $template = preg_replace('/\{\{--.*?--\}\}/s', '', $template);

        // 2. Temporarily protect escaped braces: @{{ ... }} or @{!! ... !!} (for Vue.js, Alpine, React)
        $escapedTokens = [];
        $template = preg_replace_callback('/@(\{\{.*?\}\}|\{!!.*?!!\})/s', function ($m) use (&$escapedTokens) {
            $token = '%%ESCAPED_BRACES_' . count($escapedTokens) . '%%';
            $escapedTokens[$token] = $m[1]; // Store literal brace content without '@'
            return $token;
        }, $template);

        // 3. Temporarily protect escaped directives (@@Connect, @@csrf, @@honeypot, @@if, etc.)
        $escaped = [
            '@@Connect'              => '%%ESCAPED_AT_CONNECT%%',
            '@@connect'              => '%%ESCAPED_AT_CONNECT%%',
            '@@title'                => '%%ESCAPED_AT_TITLE%%',
            '@@include'              => '%%ESCAPED_AT_INCLUDE%%',
            '@@content'              => '%%ESCAPED_AT_CONTENT%%',
            '@@contentConnect'       => '%%ESCAPED_AT_CONTENTCONNECT%%',
            '@@contentconnect'       => '%%ESCAPED_AT_CONTENTCONNECT%%',
            '@@contentCconnect'      => '%%ESCAPED_AT_CONTENTCCONNECT%%',
            '@@contentcconnect'      => '%%ESCAPED_AT_CONTENTCCONNECT%%',
            '@@endContentConnect'    => '%%ESCAPED_AT_ENDCONTENTCONNECT%%',
            '@@endcontentconnect'    => '%%ESCAPED_AT_ENDCONTENTCONNECT%%',
            '@@endContentCconnect'   => '%%ESCAPED_AT_ENDCONTENTCCONNECT%%',
            '@@endcontentcconnect'   => '%%ESCAPED_AT_ENDCONTENTCCONNECT%%',
            '@@csrf'                 => '%%ESCAPED_AT_CSRF%%',
            '@@honeypot'             => '%%ESCAPED_AT_HONEYPOT%%',
            '@@flash'                => '%%ESCAPED_AT_FLASH%%',
            '@@if'                   => '%%ESCAPED_AT_IF%%',
            '@@elseif'               => '%%ESCAPED_AT_ELSEIF%%',
            '@@else'                 => '%%ESCAPED_AT_ELSE%%',
            '@@endif'                => '%%ESCAPED_AT_ENDIF%%',
            '@@foreach'              => '%%ESCAPED_AT_FOREACH%%',
            '@@endforeach'           => '%%ESCAPED_AT_ENDFOREACH%%',
            '@@for'                  => '%%ESCAPED_AT_FOR%%',
            '@@endfor'               => '%%ESCAPED_AT_ENDFOR%%',
            '@@isset'                => '%%ESCAPED_AT_ISSET%%',
            '@@endisset'             => '%%ESCAPED_AT_ENDISSET%%',
            '@@empty'                => '%%ESCAPED_AT_EMPTY%%',
            '@@endempty'             => '%%ESCAPED_AT_ENDEMPTY%%',
        ];
        $template = strtr($template, $escaped);

        // 4. Raw / unescaped output: {!! $expression !!}
        $template = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?= $1 ?>', $template);

        // 5. Built-in safe directive helpers (URLs and HTML fields)
        $template = preg_replace(
            '/\{\{\s*assets?\s*\(\s*([\'"][^\'"]+[\'"])\s*\)\s*\}\}/i',
            '<?= assets($1) ?>',
            $template
        );

        $template = preg_replace(
            '/\{\{\s*route\s*\(\s*(.+?)\s*\)\s*\}\}/i',
            '<?= route($1) ?>',
            $template
        );

        $template = preg_replace(
            '/\{\{\s*url\s*\(\s*([\'"][^\'"]+[\'"])\s*\)\s*\}\}/i',
            '<?= url($1) ?>',
            $template
        );

        $template = preg_replace(
            '/\{\{\s*csrf_field\s*\(\s*\)\s*\}\}/i',
            '<?= csrf_field() ?>',
            $template
        );

        $template = preg_replace(
            '/\{\{\s*honeypot_field\s*\(\s*\)\s*\}\}/i',
            '<?= honeypot_field() ?>',
            $template
        );

        // 6. Generic {{ $expression }} -> Auto-escaped with htmlspecialchars for bulletproof XSS protection!
        $template = preg_replace_callback('/\{\{\s*([^{}]+?)\s*\}\}/', function ($m) {
            $expr = trim($m[1]);
            if ($expr === '') {
                return '{{ }}';
            }
            $expr = rtrim($expr, ';');
            return "<?= htmlspecialchars((string)({$expr}), ENT_QUOTES, 'UTF-8') ?>";
        }, $template);

        // 7. Security & Template Directives:
        // @csrf
        $template = preg_replace('/(?<!@)@csrf\b/i', '<?= csrf_field() ?>', $template);

        // @honeypot
        $template = preg_replace('/(?<!@)@honeypot\b/i', '<?= honeypot_field() ?>', $template);

        // @include('path.file') or @include('path.file', ['extra' => 'data'])
        $template = preg_replace_callback(
            '/(?<!@)@include\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*(.+?))?\s*\);?/i',
            function ($m) {
                $partial = addslashes($m[1]);
                if (isset($m[2]) && trim($m[2]) !== '') {
                    $extra = trim($m[2]);
                    return "<?= \\Core\\View::renderPartial('{$partial}', array_merge(get_defined_vars(), (array)({$extra}))) ?>";
                }
                return "<?= \\Core\\View::renderPartial('{$partial}', get_defined_vars()) ?>";
            },
            $template
        );

        // @flash('key')
        $template = preg_replace(
            '/(?<!@)@flash\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?/i',
            '<?php if ($flashMsg = flash(\'$1\')): ?><div class="alert alert-$1"><?= htmlspecialchars($flashMsg, ENT_QUOTES, \'UTF-8\') ?></div><?php endif; ?>',
            $template
        );

        // @if(condition)
        $template = preg_replace('/(?<!@)@if\s*\((.+?)\)/i', '<?php if ($1): ?>', $template);
        $template = preg_replace('/(?<!@)@elseif\s*\((.+?)\)/i', '<?php elseif ($1): ?>', $template);
        $template = preg_replace('/(?<!@)@else\b/i', '<?php else: ?>', $template);
        $template = preg_replace('/(?<!@)@endif\b/i', '<?php endif; ?>', $template);

        // @foreach($items as $item)
        $template = preg_replace('/(?<!@)@foreach\s*\((.+?)\)/i', '<?php foreach ($1): ?>', $template);
        $template = preg_replace('/(?<!@)@endforeach\b/i', '<?php endforeach; ?>', $template);

        // @for(...)
        $template = preg_replace('/(?<!@)@for\s*\((.+?)\)/i', '<?php for ($1): ?>', $template);
        $template = preg_replace('/(?<!@)@endfor\b/i', '<?php endfor; ?>', $template);

        // @isset($var)
        $template = preg_replace('/(?<!@)@isset\s*\((.+?)\)/i', '<?php if (isset($1)): ?>', $template);
        $template = preg_replace('/(?<!@)@endisset\b/i', '<?php endif; ?>', $template);

        // @empty($var)
        $template = preg_replace('/(?<!@)@empty\s*\((.+?)\)/i', '<?php if (empty($1)): ?>', $template);
        $template = preg_replace('/(?<!@)@endempty\b/i', '<?php endif; ?>', $template);

        // 8. Restore escaped tokens as literal
        if (!empty($escapedTokens)) {
            $template = strtr($template, $escapedTokens);
        }

        $unEscape = [
            '%%ESCAPED_AT_CONNECT%%'            => '@Connect',
            '%%ESCAPED_AT_TITLE%%'              => '@title',
            '%%ESCAPED_AT_INCLUDE%%'            => '@include',
            '%%ESCAPED_AT_CONTENT%%'            => '@content',
            '%%ESCAPED_AT_CONTENTCONNECT%%'     => '@contentConnect',
            '%%ESCAPED_AT_CONTENTCCONNECT%%'    => '@contentCconnect',
            '%%ESCAPED_AT_ENDCONTENTCONNECT%%'  => '@endContentConnect',
            '%%ESCAPED_AT_ENDCONTENTCCONNECT%%' => '@endContentCconnect',
            '%%ESCAPED_AT_CSRF%%'               => '@csrf',
            '%%ESCAPED_AT_HONEYPOT%%'           => '@honeypot',
            '%%ESCAPED_AT_FLASH%%'              => '@flash',
            '%%ESCAPED_AT_IF%%'                 => '@if',
            '%%ESCAPED_AT_ELSEIF%%'             => '@elseif',
            '%%ESCAPED_AT_ELSE%%'               => '@else',
            '%%ESCAPED_AT_ENDIF%%'              => '@endif',
            '%%ESCAPED_AT_FOREACH%%'            => '@foreach',
            '%%ESCAPED_AT_ENDFOREACH%%'         => '@endforeach',
            '%%ESCAPED_AT_FOR%%'                => '@for',
            '%%ESCAPED_AT_ENDFOR%%'             => '@endfor',
            '%%ESCAPED_AT_ISSET%%'              => '@isset',
            '%%ESCAPED_AT_ENDISSET%%'           => '@endisset',
            '%%ESCAPED_AT_EMPTY%%'              => '@empty',
            '%%ESCAPED_AT_ENDEMPTY%%'           => '@endempty',
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
     * Resolve full file path for a view or partial name.
     * Enforces strictly the .srin.php template format.
     * Supports dot-notation e.g. 'partials.header' -> 'Pages/partials/header.srin.php'
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

        // Support dot notation: 'partials.header' or 'components.card' -> 'partials/header'
        $clean = str_replace(['.', '/'], DIRECTORY_SEPARATOR, $clean);
        $clean = str_replace('..' . DIRECTORY_SEPARATOR, '', $clean);

        // 1. Direct path check
        $srinFile = $base . $clean . '.srin.php';
        if (file_exists($srinFile)) {
            return $srinFile;
        }

        // 2. Fallbacks for partials / components / includes / layouts
        foreach (['partials', 'includes', 'components', 'layouts'] as $sub) {
            $fallback = $base . $sub . DIRECTORY_SEPARATOR . $clean . '.srin.php';
            if (file_exists($fallback)) {
                return $fallback;
            }
        }

        return $srinFile;
    }
}
