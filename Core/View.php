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

        // 1. Detect layout directive: [layout '...'] or [connect '...'] or {#layout '...'} or @connect('...')
        $layout = null;
        if (preg_match('/^\s*(?:\[(?:layout|connect)\s*[\'"]([^\'"]+)[\'"]\s*\]|\{#layout\s*[\'"]([^\'"]+)[\'"]\s*\}|(?<!@)@(?:connect|Connect)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?)/mi', $rawContent, $layoutMatch)) {
            $layout = trim($layoutMatch[1] ?: ($layoutMatch[2] ?: $layoutMatch[3]));
            // Remove the layout directive line from page content
            $rawContent = preg_replace('/^\s*(?:\[(?:layout|connect)\s*[\'"]([^\'"]+)[\'"]\s*\]|\{#layout\s*[\'"]([^\'"]+)[\'"]\s*\}|(?<!@)@(?:connect|Connect)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?)\r?\n?/mi', '', $rawContent, 1);
        }

        // 2. Detect title directive: [title '...'] or {#title '...'} or @title('...')
        if (preg_match('/^\s*(?:\[title\s*[\'"]([^\'"]+)[\'"]\s*\]|\{#title\s*[\'"]([^\'"]+)[\'"]\s*\}|(?<!@)@title\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?)/mi', $rawContent, $titleMatch)) {
            $data['title'] = $titleMatch[1] ?: ($titleMatch[2] ?: $titleMatch[3]);
            $rawContent = preg_replace('/^\s*(?:\[title\s*[\'"]([^\'"]+)[\'"]\s*\]|\{#title\s*[\'"]([^\'"]+)[\'"]\s*\}|(?<!@)@title\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?)\r?\n?/mi', '', $rawContent, 1);
        }

        // 3. Extract named content sections: [block 'name']...[end] or {#block 'name'}...{/block} or @contentConnect('name')...@endContentConnect
        $sections = [];
        $sectionPattern = '/(?:\[(?:block|section)\s*[\'"]([^\'"]+)[\'"]\s*\](.*?)(?:\[(?:end|\/(?:block|section))\]|\{end\})|\{#(?:block|section)\s*[\'"]([^\'"]+)[\'"]\s*\}(.*?)\{\/(?:block|section)\}|(?<!@)@(?:contentConnect|contentCconnect|connectContent|connectCcontent)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)(.*?)(?<!@)@(?:endContentConnect|endContentCconnect|endConnectContent|endConnectCcontent)\b)/si';

        $rawContent = preg_replace_callback($sectionPattern, function ($m) use (&$sections, $data) {
            $name = trim($m[1] ?: ($m[3] ?: $m[5]));
            $blockRaw = $m[2] ?: ($m[4] ?: $m[6]);
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

        // 1. Replace named slots: [slot 'name', 'default'], [slot 'name'], [content 'name'], {#slot 'name'}, or @content('name')
        $compiledLayout = preg_replace_callback(
            '/(?:\[(?:slot|content)\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*([\'"].*?[\'"]|[^\]]+))?\s*\]|\{#slot\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*([\'"].*?[\'"]|[^}]+))?\s*\}|(?<!@)@content\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*([\'"].*?[\'"]|[^)]+))?\s*\))/i',
            function ($m) {
                $slot = addslashes(trim($m[1] ?: ($m[3] ?: $m[5])));
                $defaultParam = trim($m[2] ?: ($m[4] ?: ($m[6] ?? '')));
                if ($defaultParam !== '') {
                    return "<?= \$sections['{$slot}'] ?? ({$defaultParam}) ?>";
                }
                return "<?= \$sections['{$slot}'] ?? '' ?>";
            },
            $compiledLayout
        );

        // 2. Replace generic slot: [slot], [content], {#slot}, or @content
        $compiledLayout = preg_replace(
            '/(?:\[(?:slot|content)\]|\{#slot\}|(?<!@)@content\b(?!\s*\())/i',
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
     * Supports Srin Tags ([if], [each], [else], [empty], [end]), Svelte-style ({#if}, {#each}),
     * and legacy Blade-style (@if, @foreach) for full backward compatibility.
     * Enforces strict XSS escaping on all {{ $expression }} outputs.
     */
    public static function compileDirectives(string $template): string
    {
        // 1. Strip comments: {{-- ... --}}, [-- ... --], or {#-- ... --#}
        $template = preg_replace('/\{\{--.*?--\}\}/s', '', $template);
        $template = preg_replace('/\[--.*?--\]/s', '', $template);
        $template = preg_replace('/\{#--.*?--#\}/s', '', $template);

        // 2. Temporarily protect escaped braces: @{{ ... }} or @{!! ... !!} (for Vue.js, Alpine, React)
        $escapedTokens = [];
        $template = preg_replace_callback('/@(\{\{.*?\}\}|\{!!.*?!!\})/s', function ($m) use (&$escapedTokens) {
            $token = '%%ESCAPED_BRACES_' . count($escapedTokens) . '%%';
            $escapedTokens[$token] = $m[1]; // Store literal brace content without '@'
            return $token;
        }, $template);

        // 3. Temporarily protect escaped directives (@@Connect, @@csrf, @@if, \[[if]], etc.)
        $escaped = [
            '@@Connect'              => '%%ESCAPED_AT_CONNECT%%',
            '@@connect'              => '%%ESCAPED_AT_CONNECT%%',
            '@@title'                => '%%ESCAPED_AT_TITLE%%',
            '@@include'              => '%%ESCAPED_AT_INCLUDE%%',
            '@@content'              => '%%ESCAPED_AT_CONTENT%%',
            '@@contentConnect'       => '%%ESCAPED_AT_CONTENTCONNECT%%',
            '@@contentconnect'       => '%%ESCAPED_AT_CONTENTCONNECT%%',
            '@@connectContent'       => '%%ESCAPED_AT_CONNECTCONTENT%%',
            '@@connectcontent'       => '%%ESCAPED_AT_CONNECTCONTENT%%',
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
            '@@paginate'             => '%%ESCAPED_AT_PAGINATE%%',
            '@@while'                => '%%ESCAPED_AT_WHILE%%',
            '@@endwhile'             => '%%ESCAPED_AT_ENDWHILE%%',
            '@@unless'               => '%%ESCAPED_AT_UNLESS%%',
            '@@endunless'            => '%%ESCAPED_AT_ENDUNLESS%%',
        ];
        $template = strtr($template, $escaped);

        // 4. Raw / unescaped output: {!! $expression !!}
        $template = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?= $1 ?>', $template);

        // 5. Built-in safe directive helpers in {{ }} (assets, route, url, csrf_field, honeypot_field)
        $template = preg_replace_callback('/\{\{\s*([^{}]+?)\s*\}\}/', function ($m) {
            $expr = trim($m[1]);
            if ($expr === '') {
                return '{{ }}';
            }
            $expr = rtrim($expr, ';');
            if (preg_match('/^(?:assets?|route|url|csrf_field|honeypot_field)\s*\(.*?\)$/i', $expr)) {
                return "<?= {$expr} ?>";
            }
            return "<?= htmlspecialchars((string)({$expr}), ENT_QUOTES, 'UTF-8') ?>";
        }, $template);

        // 6. Standalone Directives: [csrf], [honeypot], [paginate], [include], [flash]
        // CSRF: [csrf] or {#csrf} or @csrf
        $template = preg_replace('/(?:\[csrf\]|\{#csrf\}|(?<!@)@csrf\b)/i', '<?= csrf_field() ?>', $template);

        // Honeypot: [honeypot] or {#honeypot} or @honeypot
        $template = preg_replace('/(?:\[honeypot\]|\{#honeypot\}|(?<!@)@honeypot\b)/i', '<?= honeypot_field() ?>', $template);

        // Paginate: [paginate $items] or {#paginate $items} or @paginate($items)
        $template = preg_replace_callback('/(?:\[paginate\s+(.+?)\]|\{#paginate\s+(.+?)\}|(?<!@)@paginate\s*\(\s*(.+?)\s*\);?)/i', function ($m) {
            $var = trim($m[1] ?: ($m[2] ?: $m[3]));
            return "<?= (is_object({$var}) && method_exists({$var}, 'links') ? ({$var})->links() : '') ?>";
        }, $template);

        // Include: [include 'file', ['key' => $val]] or {#include 'file'} or @include('file')
        $template = preg_replace_callback(
            '/(?:\[include\s+[\'"]([^\'"]+)[\'"](?:\s*,\s*(.+?))?\s*\]|\{#include\s+[\'"]([^\'"]+)[\'"](?:\s*,\s*(.+?))?\s*\}|(?<!@)@include\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*(.+?))?\s*\);?)/i',
            function ($m) {
                $file = addslashes($m[1] ?: ($m[3] ?: $m[5]));
                $data = trim($m[2] ?: ($m[4] ?: ($m[6] ?? '')));
                if ($data !== '') {
                    return "<?= \\Core\\View::renderPartial('{$file}', array_merge(get_defined_vars(), (array)({$data}))) ?>";
                }
                return "<?= \\Core\\View::renderPartial('{$file}', get_defined_vars()) ?>";
            },
            $template
        );

        // Flash: [flash 'key'] or {#flash 'key'} or @flash('key')
        $template = preg_replace_callback(
            '/(?:\[flash\s+[\'"]([^\'"]+)[\'"]\s*\]|\{#flash\s+[\'"]([^\'"]+)[\'"]\s*\}|(?<!@)@flash\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\);?)/i',
            function ($m) {
                $key = addslashes($m[1] ?: ($m[2] ?: $m[3]));
                return "<?php if (\$flashMsg = flash('{$key}')): ?><div class=\"alert alert-{$key}\"><?= htmlspecialchars(\$flashMsg, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>";
            },
            $template
        );

        // 7. Control Flow Engine with Universal [end] / {end} Stack (Balanced PCRE Brackets)
        $controlPattern = '/('
            . '\[(if|elseif|each|for|while|unless|isset)\s+((?:[^\[\]]++|\[(?3)\])*)\]'
            . '|\[(else|empty|end|endif|\/if|endeach|\/each|endfor|\/for|endwhile|\/while|endunless|\/unless|endisset|\/isset|endempty|\/empty)\]'
            . '|\{#(if|each|for|while|unless|isset)\s+((?:[^{}]++|\{(?6)\})*)\}'
            . '|\{:(elseif\s+[^{}]+|else|empty)\}'
            . '|\{\/(if|each|for|while|unless|isset)\}'
            . '|\{end\}'
            . '|(?<!@)@(if|elseif|foreach|for|while|unless|isset|empty)\s*\((?<at_expr>(?:[^()]++|\((?&at_expr)\))*)\)'
            . '|(?<!@)@(else|empty|endif|endforeach|endfor|endwhile|endunless|endisset|endempty)\b'
            . ')/i';

        $stack = [];
        $eachCounter = 0;

        $template = preg_replace_callback($controlPattern, function ($m) use (&$stack, &$eachCounter) {
            $full = $m[1];

            // 1. [tag expr]
            if (!empty($m[2])) {
                $tag = strtolower($m[2]);
                $expr = trim($m[3]);
                if ($tag === 'if') {
                    $stack[] = ['type' => 'if'];
                    return "<?php if ({$expr}): ?>";
                }
                if ($tag === 'elseif') {
                    return "<?php elseif ({$expr}): ?>";
                }
                if ($tag === 'each') {
                    $eachCounter++;
                    $var = "__srin_empty_" . $eachCounter;
                    $stack[] = ['type' => 'each', 'has_empty' => false, 'empty_var' => $var];
                    return "<?php \${$var} = true; foreach ({$expr}): \${$var} = false; ?>";
                }
                if ($tag === 'for') {
                    $stack[] = ['type' => 'for'];
                    return "<?php for ({$expr}): ?>";
                }
                if ($tag === 'while') {
                    $stack[] = ['type' => 'while'];
                    return "<?php while ({$expr}): ?>";
                }
                if ($tag === 'unless') {
                    $stack[] = ['type' => 'unless'];
                    return "<?php if (!({$expr})): ?>";
                }
                if ($tag === 'isset') {
                    $stack[] = ['type' => 'isset'];
                    return "<?php if (isset({$expr})): ?>";
                }
            }

            // 2. [closing / standalone]
            if (!empty($m[4])) {
                $tag = strtolower($m[4]);
                if ($tag === 'else') {
                    return "<?php else: ?>";
                }
                if ($tag === 'empty') {
                    if (!empty($stack) && end($stack)['type'] === 'each') {
                        $top = count($stack) - 1;
                        $stack[$top]['has_empty'] = true;
                        $var = $stack[$top]['empty_var'];
                        return "<?php endforeach; if (\${$var}): ?>";
                    }
                    return "<?php else: ?>";
                }
                // Closings: end, endif, /if, endeach, /each, etc.
                if (!empty($stack)) {
                    $closed = array_pop($stack);
                    if ($closed['type'] === 'each') {
                        return !empty($closed['has_empty']) ? "<?php endif; ?>" : "<?php endforeach; ?>";
                    }
                    if ($closed['type'] === 'for') return "<?php endfor; ?>";
                    if ($closed['type'] === 'while') return "<?php endwhile; ?>";
                    return "<?php endif; ?>";
                }
                return "<?php endif; ?>";
            }

            // 3. {#tag expr}
            if (!empty($m[5])) {
                $tag = strtolower($m[5]);
                $expr = trim($m[6]);
                if ($tag === 'if') {
                    $stack[] = ['type' => 'if'];
                    return "<?php if ({$expr}): ?>";
                }
                if ($tag === 'each') {
                    $eachCounter++;
                    $var = "__srin_empty_" . $eachCounter;
                    $stack[] = ['type' => 'each', 'has_empty' => false, 'empty_var' => $var];
                    return "<?php \${$var} = true; foreach ({$expr}): \${$var} = false; ?>";
                }
            }

            // 4. {:else} or {:empty} or {:elseif expr}
            if (!empty($m[7])) {
                $tag = strtolower(trim($m[7]));
                if ($tag === 'else') return "<?php else: ?>";
                if ($tag === 'empty') {
                    if (!empty($stack) && end($stack)['type'] === 'each') {
                        $top = count($stack) - 1;
                        $stack[$top]['has_empty'] = true;
                        $var = $stack[$top]['empty_var'];
                        return "<?php endforeach; if (\${$var}): ?>";
                    }
                    return "<?php else: ?>";
                }
                if (str_starts_with($tag, 'elseif')) {
                    $expr = trim(substr($tag, 6));
                    return "<?php elseif ({$expr}): ?>";
                }
            }

            // 5. {/tag} or {end}
            if (!empty($m[8]) || $full === '{end}') {
                if (!empty($stack)) {
                    $closed = array_pop($stack);
                    if ($closed['type'] === 'each') {
                        return !empty($closed['has_empty']) ? "<?php endif; ?>" : "<?php endforeach; ?>";
                    }
                    return "<?php endif; ?>";
                }
                return "<?php endif; ?>";
            }

            // 6. @tag(expr)
            if (!empty($m[9])) {
                $tag = strtolower($m[9]);
                $expr = trim($m[10]);
                if ($tag === 'if') {
                    $stack[] = ['type' => 'if'];
                    return "<?php if ({$expr}): ?>";
                }
                if ($tag === 'elseif') return "<?php elseif ({$expr}): ?>";
                if ($tag === 'foreach') {
                    $eachCounter++;
                    $var = "__srin_empty_" . $eachCounter;
                    $stack[] = ['type' => 'each', 'has_empty' => false, 'empty_var' => $var];
                    return "<?php \${$var} = true; foreach ({$expr}): \${$var} = false; ?>";
                }
                if ($tag === 'for') {
                    $stack[] = ['type' => 'for'];
                    return "<?php for ({$expr}): ?>";
                }
                if ($tag === 'while') {
                    $stack[] = ['type' => 'while'];
                    return "<?php while ({$expr}): ?>";
                }
                if ($tag === 'unless') {
                    $stack[] = ['type' => 'unless'];
                    return "<?php if (!({$expr})): ?>";
                }
                if ($tag === 'isset') {
                    $stack[] = ['type' => 'isset'];
                    return "<?php if (isset({$expr})): ?>";
                }
            }

            // 7. @else / @endif / @endforeach
            if (!empty($m[11])) {
                $tag = strtolower($m[11]);
                if ($tag === 'else') return "<?php else: ?>";
                if ($tag === 'empty') {
                    if (!empty($stack) && end($stack)['type'] === 'each') {
                        $top = count($stack) - 1;
                        $stack[$top]['has_empty'] = true;
                        $var = $stack[$top]['empty_var'];
                        return "<?php endforeach; if (\${$var}): ?>";
                    }
                    return "<?php else: ?>";
                }
                if (!empty($stack)) {
                    $closed = array_pop($stack);
                    if ($closed['type'] === 'each') {
                        return !empty($closed['has_empty']) ? "<?php endif; ?>" : "<?php endforeach; ?>";
                    }
                    if ($closed['type'] === 'for') return "<?php endfor; ?>";
                    if ($closed['type'] === 'while') return "<?php endwhile; ?>";
                    return "<?php endif; ?>";
                }
                return "<?php endif; ?>";
            }

            return $full;
        }, $template);

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
            '%%ESCAPED_AT_CONNECTCONTENT%%'     => '@connectContent',
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
            '%%ESCAPED_AT_PAGINATE%%'           => '@paginate',
            '%%ESCAPED_AT_WHILE%%'              => '@while',
            '%%ESCAPED_AT_ENDWHILE%%'           => '@endwhile',
            '%%ESCAPED_AT_UNLESS%%'             => '@unless',
            '%%ESCAPED_AT_ENDUNLESS%%'          => '@endunless',
        ];
        $template = strtr($template, $unEscape);

        return $template;
    }

    /**
     * Evaluate compiled template code in an isolated scope.
     */
    public static function evaluate(string $code, array $data): string
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
