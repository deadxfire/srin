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
     * Set base directory where view pages are located.
     */
    public static function setPagesPath(string $path): void
    {
        self::$pagesPath = $path;
    }

    /**
     * Get base directory where view pages are located.
     */
    public static function getPagesPath(): string
    {
        return self::$pagesPath;
    }

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
        if (!$realPages || !str_starts_with($realPageFile, $realPages . DIRECTORY_SEPARATOR)) {
            throw new Exception("Security violation: Template [{$page}] is located outside the Pages directory.");
        }

        $rawContent = file_get_contents($realPageFile);

        // 1. Detect layout directive: [layout '...'] or [connect '...']
        $layout = null;
        if (preg_match('/^\s*\[(?:layout|connect)\s*[\'"]([^\'"]+)[\'"]\s*\]/mi', $rawContent, $layoutMatch)) {
            $layout = trim($layoutMatch[1] ?? '');
            // Remove the layout directive line from page content
            $rawContent = preg_replace('/^\s*\[(?:layout|connect)\s*[\'"]([^\'"]+)[\'"]\s*\]\r?\n?/mi', '', $rawContent, 1);
        }

        // 2. Detect title directive: [title '...'] or dynamic [title $post->title . ' - SrinBlog']
        if (preg_match('/^\s*\[title\s+(.+?)\]\r?\n?/mi', $rawContent, $titleMatch)) {
            $rawTitle = trim($titleMatch[1] ?? '');
            $rawContent = preg_replace('/^\s*\[title\s+(.+?)\]\r?\n?/mi', '', $rawContent, 1);

            // If it's a simple literal string with surrounding quotes: '...' or "..." without concatenation or $
            if (preg_match('/^([\'"])(.*?)\1$/s', $rawTitle, $qMatch) && !str_contains($qMatch[2], '$') && !str_contains($rawTitle, '.')) {
                $data['title'] = $qMatch[2];
            } else {
                // Dynamic PHP expression (e.g. $post->title . ' - SrinBlog' or "Post: {$post->title}")
                try {
                    $data['title'] = self::evaluate("<?= (string)({$rawTitle}) ?>", $data);
                } catch (\Throwable $e) {
                    $data['title'] = trim($rawTitle, '\'"');
                }
            }
            $sections['title'] = $data['title'];
        }

        // 3. Extract named content sections: [block 'name']...[blockend] or [/block]
        $sections = [];
        $sectionPattern = '/\[(?:block|section)\s*[\'"]([^\'"]+)[\'"]\s*\](.*?)\[(?:blockend|sectionend|endblock|endsection|\/(?:block|section))\]/si';

        $rawContent = preg_replace_callback($sectionPattern, function ($m) use (&$sections, $data) {
            $name = trim($m[1] ?? '');
            $blockRaw = $m[2] ?? '';
            $compiledBlock = self::compileDirectives($blockRaw);
            $sections[$name] = self::evaluate($compiledBlock, $data);
            return '';
        }, $rawContent);

        // If child page defined a [block 'title'], set $data['title'] automatically
        if (!empty($sections['title']) && empty($data['title'])) {
            $data['title'] = trim(strip_tags($sections['title']));
        }

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
     * Render a raw template string with compiled directives and data.
     */
    public static function renderString(string $template, array $data = []): string
    {
        $compiled = self::compileDirectives($template);
        return self::evaluate($compiled, $data);
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

        // 1. Replace named slots & blockincludes: [slot 'name', 'default'], [blockinclude 'name'], [slot 'name']
        $compiledLayout = preg_replace_callback(
            '/\[(?:slot|content|blockinclude|block_include|blockcontent|yield)\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*([\'"].*?[\'"]|[^\]]+))?\s*\]/i',
            function ($m) {
                $slot = addslashes(trim($m[1] ?? ''));
                $defaultParam = trim($m[2] ?? '');
                if ($defaultParam !== '') {
                    return "<?= \$sections['{$slot}'] ?? ({$defaultParam}) ?>";
                }
                return "<?= \$sections['{$slot}'] ?? '' ?>";
            },
            $compiledLayout
        );

        // 2. Replace generic slot / blockinclude: [slot], [content], [blockinclude]
        $compiledLayout = preg_replace(
            '/\[(?:slot|content|blockinclude|block_include|blockcontent|yield)\]/i',
            "<?= \$content ?? (\$sections['content'] ?? (\$sections['main'] ?? '')) ?>",
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
        // 0. Strict check: Disallow Laravel Blade syntax (only when used as actual directives, not plain text like "@for")
        if (preg_match('/(?<![a-zA-Z0-9_@])@(if|elseif|foreach|for|while|unless|isset|empty)\s*\(/i', $template, $bladeMatch)
            || preg_match('/(?<![a-zA-Z0-9_@])@(endif|endforeach|endfor|endwhile|endunless|endisset|endempty|csrf|honeypot)\b/i', $template, $bladeMatch)) {
            throw new Exception("Srin Template Error: Blade directive '{$bladeMatch[0]}' is not supported in Srin Engine. Please use Srin Tags instead (e.g. [if ...], [each ...], [end], [layout '...'], [include '...']).");
        }

        // 1. Strip comments: [-- ... --]
        $template = preg_replace('/\[--.*?--\]/s', '', $template);

        // 2. Temporarily protect escaped brackets: @[[ ... ]] or @[{ ... }]
        $escapedTokens = [];
        $template = preg_replace_callback('/@(\[\[.*?\]\]|\[\{.*?\}\])/s', function ($m) use (&$escapedTokens) {
            $token = '%%ESCAPED_TOKENS_' . count($escapedTokens) . '%%';
            $escapedTokens[$token] = $m[1]; // Store literal content without '@'
            return $token;
        }, $template);

        // Support {{ ... }} as an alias for safe double brackets [[ ... ]]
        $template = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '[[ $1 ]]', $template);

        // 3. Raw / unescaped HTML output: [{ $expression }] (Srin standard)
        $template = preg_replace('/\[\{\s*(.+?)\s*\}\]/s', '<?= $1 ?>', $template);

        // 4. Double brackets safe output: [[ $expression ]] (Srin standard, supports nested brackets like $a[$b[0]])
        $safeOutputPattern = '/\[\[\s*((?:[^\[\]"\']++|"[^"\\\\]*+(?:\\\\.[^"\\\\]*+)*+"|\'[^\'\\\\]*+(?:\\\\.[^\'\\\\]*+)*+\'|\[(?1)\])*)\s*\]\]/s';
        $template = preg_replace_callback($safeOutputPattern, function ($m) {
            $expr = trim($m[1]);
            if ($expr === '') {
                return '[[ ]]';
            }
            $expr = rtrim($expr, ';');
            // Only raw HTML field generators are emitted without escaping;
            // url(), route(), and asset() go through htmlspecialchars to prevent attribute breakout/XSS.
            if (preg_match('/^(?:csrf_field|honeypot_field)\s*\(.*?\)$/i', $expr)) {
                return "<?= {$expr} ?>";
            }
            return "<?= htmlspecialchars((string)({$expr}), ENT_QUOTES, 'UTF-8') ?>";
        }, $template);

        // 5. Standalone Directives: [csrf], [honeypot], [paginate], [include], [flash]
        // CSRF: [csrf]
        $template = preg_replace('/\[csrf\]/i', '<?= csrf_field() ?>', $template);

        // Honeypot: [honeypot]
        $template = preg_replace('/\[honeypot\]/i', '<?= honeypot_field() ?>', $template);

        // Paginate: [paginate $items]
        $template = preg_replace_callback('/\[paginate\s+(.+?)\]/i', function ($m) {
            $var = trim($m[1] ?? '');
            return "<?= (is_object({$var}) && method_exists({$var}, 'links') ? ({$var})->links() : '') ?>";
        }, $template);

        // Include: [include 'file', ['key' => $val]] or [include 'file']
        $template = preg_replace_callback(
            '/\[include\s+[\'"]([^\'"]+)[\'"](?:\s*,\s*(.+?))?\s*\]/i',
            function ($m) {
                $file = addslashes(trim($m[1] ?? ''));
                $data = trim($m[2] ?? '');
                if ($data !== '') {
                    return "<?= \\Core\\View::renderPartial('{$file}', array_merge(get_defined_vars(), (array)({$data}))) ?>";
                }
                return "<?= \\Core\\View::renderPartial('{$file}', get_defined_vars()) ?>";
            },
            $template
        );

        // Flash: [flash 'key']
        $template = preg_replace_callback(
            '/\[flash\s+[\'"]([^\'"]+)[\'"]\s*\]/i',
            function ($m) {
                $key = addslashes(trim($m[1] ?? ''));
                return "<?php if (\$flashMsg = flash('{$key}')): ?><div class=\"alert alert-{$key}\"><?= htmlspecialchars(\$flashMsg, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>";
            },
            $template
        );

        // 6. Control Flow Engine with Universal & Named Closers Stack (Balanced PCRE Brackets)
        $controlPattern = '/('
            . '\[(if|elseif|each|for|while|unless|isset)\s+((?:[^\[\]]++|\[(?3)\])*)\]'
            . '|\[(else|empty|end|ifend|\/if|endif|eachend|\/each|endeach|unlessend|endless|\/unless|endunless|issetend|\/isset|endisset|forend|\/for|endfor|whileend|\/while|endwhile|blockend|\/block|endblock|sectionend|\/section|endsection)\]'
            . ')/i';

        $stack = [];
        $eachCounter = 0;

        $tagMap = [
            'ifend'     => 'if',
            '/if'       => 'if',
            'endif'     => 'if',
            'eachend'   => 'each',
            '/each'     => 'each',
            'endeach'   => 'each',
            'unlessend' => 'unless',
            'endless'   => 'unless',
            '/unless'   => 'unless',
            'endunless' => 'unless',
            'issetend'  => 'isset',
            '/isset'    => 'isset',
            'endisset'  => 'isset',
            'forend'    => 'for',
            '/for'      => 'for',
            'endfor'    => 'for',
            'whileend'  => 'while',
            '/while'    => 'while',
            'endwhile'  => 'while',
        ];

        $template = preg_replace_callback($controlPattern, function ($m) use (&$stack, &$eachCounter, $tagMap) {
            $full = $m[1] ?? '';

            // 1. [tag expr]
            if (!empty($m[2])) {
                $tag = strtolower($m[2]);
                $expr = trim($m[3] ?? '');
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

                // Block closings that appear standalone in compiled content
                if (in_array($tag, ['blockend', '/block', 'endblock', 'sectionend', '/section', 'endsection'], true)) {
                    return '';
                }

                // Specific named closer: [ifend], [eachend], [endless], [/if], [/each], etc.
                if (isset($tagMap[$tag])) {
                    $expected = $tagMap[$tag];
                    $closingCode = '';
                    while (!empty($stack)) {
                        $top = array_pop($stack);
                        if ($top['type'] === 'each') {
                            $closingCode .= !empty($top['has_empty']) ? "<?php endif; ?>" : "<?php endforeach; ?>";
                        } elseif ($top['type'] === 'for') {
                            $closingCode .= "<?php endfor; ?>";
                        } elseif ($top['type'] === 'while') {
                            $closingCode .= "<?php endwhile; ?>";
                        } else {
                            $closingCode .= "<?php endif; ?>";
                        }
                        if ($top['type'] === $expected) {
                            return $closingCode;
                        }
                    }
                    if ($expected === 'each') return "<?php endforeach; ?>";
                    if ($expected === 'for') return "<?php endfor; ?>";
                    if ($expected === 'while') return "<?php endwhile; ?>";
                    return "<?php endif; ?>";
                }

                // Universal fallback closer: [end]
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

        // 7. Restore escaped tokens as literal
        if (!empty($escapedTokens)) {
            $template = strtr($template, $escapedTokens);
        }

        return $template;
    }

    /**
     * Evaluate compiled template code in an isolated scope.
     */
    public static function evaluate(string $__srin_code__, array $__srin_data__): string
    {
        return (static function () use ($__srin_code__, $__srin_data__) {
            extract($__srin_data__, EXTR_SKIP);
            unset($__srin_data__);

            ob_start();
            try {
                eval('?>' . $__srin_code__);
            } catch (\Throwable $__srin_e__) {
                ob_end_clean();
                throw $__srin_e__;
            }

            return ob_get_clean() ?: '';
        })();
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

        // 3. Normalize singular folder prefixes (e.g. 'layout/main' -> 'layouts/main', 'partial/navbar' -> 'partials/navbar')
        $normalizedPlural = preg_replace('/^(layout|partial|include|component)(' . preg_quote(DIRECTORY_SEPARATOR, '/') . ')/i', '${1}s$2', $clean);
        if ($normalizedPlural !== $clean) {
            $pluralFile = $base . $normalizedPlural . '.srin.php';
            if (file_exists($pluralFile)) {
                return $pluralFile;
            }
        }

        // 4. Check inside layouts/ after stripping 'layout/' or 'layouts/' prefix
        $strippedLayout = preg_replace('/^layouts?(' . preg_quote(DIRECTORY_SEPARATOR, '/') . ')/i', '', $clean);
        if ($strippedLayout !== $clean) {
            $layoutCandidate = $base . 'layouts' . DIRECTORY_SEPARATOR . $strippedLayout . '.srin.php';
            if (file_exists($layoutCandidate)) {
                return $layoutCandidate;
            }
        }

        return $srinFile;
    }
}
