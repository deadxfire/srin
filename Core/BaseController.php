<?php

namespace Core;

class BaseController
{
    /**
     * Render a view from the Pages directory (.srin.php).
     */
    protected function render(string $page, array $data = []): string
    {
        return View::render($page, $data);
    }

    /**
     * Expressive alias for render().
     * Example: return $this->view('blog/index', ['posts' => $posts]);
     */
    protected function view(string $page, array $data = []): string
    {
        return $this->render($page, $data);
    }

    /**
     * Send a JSON response.
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }

    /**
     * Redirect to another URL (CRLF injection protected).
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        Response::redirect($url, $statusCode);
    }

    /**
     * Redirect back to previous URL (HTTP_REFERER) or a fallback route.
     */
    protected function back(int $statusCode = 302, string $fallback = '/'): void
    {
        Response::back($statusCode, $fallback);
    }

    /**
     * Generate URL for a named route.
     */
    protected function route(string $name, array|string|int $params = []): string
    {
        return route($name, $params);
    }

    /**
     * Redirect to a named route.
     */
    protected function redirectToRoute(string $name, array|string|int $params = [], int $statusCode = 302): void
    {
        $this->redirect(route($name, $params), $statusCode);
    }

    /**
     * Get an input value from the request (sanitized and trimmed if string).
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        $val = Request::input($key, $default);
        return is_string($val) ? trim($val) : $val;
    }

    /**
     * Get a sanitized, trimmed string parameter from GET query.
     * Example: $search = $this->get('q');
     */
    protected function get(string $key = '', mixed $default = ''): mixed
    {
        if ($key === '') {
            return Security::cleanArray($_GET);
        }
        $val = $_GET[$key] ?? $default;
        if (is_string($val)) {
            return trim(Security::cleanString($val));
        }
        return is_array($val) ? Security::cleanArray($val) : $val;
    }

    /**
     * Get a sanitized, trimmed parameter from POST body.
     * Example: $title = $this->post('title');
     */
    protected function post(string $key = '', mixed $default = ''): mixed
    {
        if ($key === '') {
            return Security::cleanArray($_POST);
        }
        $val = $_POST[$key] ?? $default;
        if (is_string($val)) {
            return trim(Security::cleanString($val));
        }
        return is_array($val) ? Security::cleanArray($val) : $val;
    }

    /**
     * Check if a specific input key is present and not empty.
     */
    protected function has(string $key): bool
    {
        $val = $this->input($key, null);
        if ($val === null) {
            return false;
        }
        if (is_string($val)) {
            return trim($val) !== '';
        }
        if (is_array($val)) {
            return !empty($val);
        }
        return true;
    }

    /**
     * Alias for has().
     */
    protected function filled(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Get input strictly as an integer.
     */
    protected function int(string $key, int $default = 0): int
    {
        return Request::int($key, $default);
    }

    /**
     * Get input strictly as a boolean.
     */
    protected function bool(string $key, bool $default = false): bool
    {
        return Request::bool($key, $default);
    }

    /**
     * Quick shortcut to retrieve a sanitized search keyword.
     */
    protected function search(string $key = 'q', string $default = ''): string
    {
        return (string) $this->get($key, $default);
    }

    /**
     * Get an uploaded file from the request.
     */
    protected function file(string $key): ?array
    {
        return Request::file($key);
    }

    /**
     * Get all uploaded files from the request.
     */
    protected function files(): array
    {
        return Request::files();
    }

    /**
     * Check if request has a valid uploaded file.
     */
    protected function hasFile(string $key): bool
    {
        return Request::hasFile($key);
    }

    /**
     * Move an uploaded file safely to target directory (defaults to assets/uploads).
     * Generates a collision-resistant filename and validates extensions.
     * Returns the relative path on success, or null on failure.
     */
    protected function storeFile(string $key, string $destinationDir = 'assets/uploads', array $allowedExtensions = [], ?int $maxBytes = null): ?string
    {
        if (!$this->hasFile($key)) {
            return null;
        }

        $file = $this->file($key);
        if (!is_array($file) || !isset($file['tmp_name']) || !is_string($file['tmp_name'])) {
            return null;
        }

        $originalName = (string) ($file['name'] ?? '');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Extensions that must never be stored, even if the caller allows them
        // (server-side code, handlers/config overrides, and script-bearing formats).
        $forbidden = [
            'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 'phtml', 'pht', 'phps', 'phar',
            'inc', 'cgi', 'pl', 'py', 'rb', 'sh', 'bash', 'asp', 'aspx', 'jsp', 'exe', 'dll', 'bat', 'cmd',
            'htaccess', 'htpasswd', 'ini', 'user', 'shtml', 'html', 'htm', 'xhtml', 'js', 'mjs',
        ];

        // Check EVERY dot-separated part of the client name (e.g. "shell.php.jpg").
        foreach (explode('.', strtolower(basename($originalName))) as $part) {
            if (in_array($part, $forbidden, true)) {
                return null;
            }
        }

        // Safe default allowlist when the caller does not supply one.
        $allowed = !empty($allowedExtensions)
            ? array_map('strtolower', $allowedExtensions)
            : ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'csv'];
        $allowed = array_values(array_diff($allowed, $forbidden));

        if ($ext === '' || !in_array($ext, $allowed, true)) {
            return null;
        }

        // Size limit.
        $limit = $maxBytes ?? (int) config('app.upload_max_bytes', 5 * 1024 * 1024);
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $limit) {
            return null;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        // Content must match the claimed type for images.
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)
            && @getimagesize($file['tmp_name']) === false) {
            return null;
        }

        // Destination: reject any traversal segment outright (no "clean up and continue").
        $baseDir = realpath(dirname(__DIR__));
        $normalized = trim(str_replace('\\', '/', $destinationDir), '/');
        foreach (explode('/', $normalized) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return null;
            }
        }
        $targetPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);

        if (!is_dir($targetPath) && !mkdir($targetPath, 0755, true) && !is_dir($targetPath)) {
            return null;
        }

        $realTarget = realpath($targetPath);
        if ($realTarget === false || !str_starts_with($realTarget . DIRECTORY_SEPARATOR, $baseDir . DIRECTORY_SEPARATOR)) {
            return null;
        }

        // Defence in depth: make sure the upload directory can never execute scripts.
        $guard = $realTarget . DIRECTORY_SEPARATOR . '.htaccess';
        if (!file_exists($guard)) {
            @file_put_contents($guard, "# Auto-generated: uploads must never execute\n"
                . "<IfModule mod_php.c>\n    php_flag engine off\n</IfModule>\n"
                . "<IfModule mod_php7.c>\n    php_flag engine off\n</IfModule>\n"
                . "<FilesMatch \"(?i)\\.(php[0-9s]?|phtml|phar|pht|cgi|pl|py|sh)$\">\n"
                . "    <IfModule mod_authz_core.c>\n        Require all denied\n    </IfModule>\n"
                . "    <IfModule !mod_authz_core.c>\n        Order allow,deny\n        Deny from all\n    </IfModule>\n"
                . "</FilesMatch>\n"
                . "Options -ExecCGI -Indexes\n"
                . "RemoveHandler .php .phtml .phar\n"
                . "Header set X-Content-Type-Options \"nosniff\"\n");
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = $realTarget . DIRECTORY_SEPARATOR . $safeName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            @chmod($destination, 0644);
            return $normalized . '/' . $safeName;
        }

        return null;
    }

    /**
     * Check if request is AJAX / Fetch.
     */
    protected function isAjax(): bool
    {
        return Request::isAjax();
    }

    /**
     * Get currently authenticated identity model instance.
     */
    protected function auth(): ?object
    {
        return Auth::user();
    }

    /**
     * Check if visitor is authenticated.
     */
    protected function authCheck(): bool
    {
        return Auth::check();
    }

    /**
     * Get authenticated user ID.
     */
    protected function userId(): ?int
    {
        return Auth::id();
    }

    /**
     * Require visitor to be authenticated, or redirect to login.
     */
    protected function requireAuth(?string $redirectRoute = null): object
    {
        $user = Auth::user();
        if (!$user) {
            $route = $redirectRoute ?? (function_exists('config') ? config('auth.login_route', 'auth.login') : 'auth.login');
            $this->flashError('Please sign in to access this page.');
            $this->redirectToRoute($route);
            exit;
        }
        return $user;
    }

    /**
     * Require visitor to be a guest, or redirect away.
     */
    protected function requireGuest(?string $redirectRoute = null): void
    {
        if (Auth::check()) {
            $route = $redirectRoute ?? (function_exists('config') ? config('auth.home_route', 'home') : 'home');
            $this->redirectToRoute($route);
            exit;
        }
    }

    /**
     * Get input strictly as string (prevents array injection).
     */
    protected function stringInput(string $key, string $default = ''): string
    {
        return Request::string($key, $default);
    }

    /**
     * Get all inputs from GET and POST (sanitized).
     */
    protected function allInputs(): array
    {
        return Request::all();
    }

    /**
     * Whitelist specific input fields (Mass Assignment protection).
     */
    protected function onlyInputs(array $keys): array
    {
        return Request::only($keys);
    }

    /**
     * Check if request method is POST.
     */
    protected function isPost(): bool
    {
        return Request::isPost();
    }

    /**
     * Check if request method is GET.
     */
    protected function isGet(): bool
    {
        return Request::isGet();
    }

    /**
     * Set a flash message.
     */
    protected function flash(string $key, string $message): self
    {
        flash($key, $message);
        return $this;
    }

    /**
     * Set a success flash message.
     */
    protected function flashSuccess(string $message): self
    {
        return $this->flash('success', $message);
    }

    /**
     * Set an error flash message.
     */
    protected function flashError(string $message): self
    {
        return $this->flash('error', $message);
    }

    /**
     * Set an info flash message.
     */
    protected function flashInfo(string $message): self
    {
        return $this->flash('info', $message);
    }

    /**
     * Set a warning flash message.
     */
    protected function flashWarning(string $message): self
    {
        return $this->flash('warning', $message);
    }

    /**
     * Enforce CSRF token verification.
     * Aborts with HTTP 419 Page Expired if verification fails.
     */
    protected function validateCsrf(): void
    {
        Security::enforceCsrf();
    }

    /**
     * Enforce Honeypot bot protection.
     * Aborts with HTTP 400 Bad Request if a bot submitted the field.
     */
    protected function validateHoneypot(string $field = '_srin_hp_check'): void
    {
        Security::enforceHoneypot($field);
    }

    /**
     * Enforce rate limiting on an action (e.g. form submission, login).
     * Aborts with HTTP 429 Too Many Requests if exceeded.
     */
    protected function rateLimit(string $key, int $maxAttempts = 5, int $decaySeconds = 60): void
    {
        Security::enforceRateLimit($key, $maxAttempts, $decaySeconds);
    }

    /**
     * Input validation helper with comprehensive rule set.
     *
     * Example rules:
     *   [
     *       'name'     => 'required|min:3|max:100',
     *       'email'    => 'required|email',
     *       'message'  => 'required|min:10',
     *       'age'      => 'numeric',
     *       'website'  => 'url',
     *   ]
     *
     * Returns array of error messages indexed by field name.
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $raw = $data[$field] ?? '';
            $value = is_string($raw) ? trim($raw) : $raw;

            foreach ($ruleList as $rule) {
                // required
                if ($rule === 'required') {
                    if ($value === '' || $value === null || (is_array($value) && empty($value))) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                        break;
                    }
                }

                // If not required and empty, skip remaining validations
                if ($value === '' || $value === null) {
                    continue;
                }

                // email
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Please provide a valid email address.';
                    break;
                }

                // numeric
                if ($rule === 'numeric' && !is_numeric($value)) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a number.';
                    break;
                }

                // url
                if ($rule === 'url') {
                    $scheme = parse_url((string) $value, PHP_URL_SCHEME);
                    if (!filter_var($value, FILTER_VALIDATE_URL) || !in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
                        $errors[$field] = 'Please provide a valid URL.';
                        break;
                    }
                }

                // min:N
                if (str_starts_with($rule, 'min:')) {
                    $min = (float) substr($rule, 4);
                    $isNumeric = in_array('numeric', $ruleList, true);
                    if ($isNumeric) {
                        if ((float) $value < $min) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min}.";
                            break;
                        }
                    } else {
                        $len = mb_strlen((string) $value);
                        if ($len < (int) $min) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at least " . (int)$min . " characters.";
                            break;
                        }
                    }
                }

                // max:N
                if (str_starts_with($rule, 'max:')) {
                    $max = (float) substr($rule, 4);
                    $isNumeric = in_array('numeric', $ruleList, true);
                    if ($isNumeric) {
                        if ((float) $value > $max) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " cannot exceed {$max}.";
                            break;
                        }
                    } else {
                        $len = mb_strlen((string) $value);
                        if ($len > (int) $max) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " cannot exceed " . (int)$max . " characters.";
                            break;
                        }
                    }
                }

                // alpha
                if ($rule === 'alpha' && !preg_match('/^[a-zA-Z]+$/', (string) $value)) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' may only contain letters.';
                    break;
                }

                // alphanumeric
                if ($rule === 'alphanumeric' && !preg_match('/^[a-zA-Z0-9]+$/', (string) $value)) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' may only contain letters and numbers.';
                    break;
                }

                // in:foo,bar,baz
                if (str_starts_with($rule, 'in:')) {
                    $allowed = explode(',', substr($rule, 3));
                    if (!in_array((string) $value, $allowed, true)) {
                        $errors[$field] = 'The selected ' . str_replace('_', ' ', $field) . ' is invalid.';
                        break;
                    }
                }

                // confirmed (e.g. password matches password_confirmation)
                if ($rule === 'confirmed') {
                    $confirmField = $field . '_confirmation';
                    $confirmVal = $data[$confirmField] ?? '';
                    if ($value !== $confirmVal) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' confirmation does not match.';
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Srin Action Guard - All-in-one defense, validation, and safe data extraction.
     * Automatically verifies CSRF and Honeypot on POST/PUT/DELETE, sanitizes inputs,
     * and redirects back with flashed errors on validation failure.
     *
     * Example:
     *   $data = $this->guard([
     *       'title'   => 'required|min:5|max:200',
     *       'slug'    => ['required', 'min:3'],
     *       'content' => 'required|min:20',
     *       'summary', 'price', 'is_published' // optional whitelisted fields
     *   ]);
     */
    protected function guard(array $schema, ?string $throttleKey = null, int $maxAttempts = 5, int $decaySeconds = 60): array
    {
        // 1. Optional rate limiting
        if ($throttleKey !== null) {
            $this->rateLimit($throttleKey, $maxAttempts, $decaySeconds);
        }

        // 2. Automated security verification on mutations (handles POST and spoofed PUT/PATCH/DELETE)
        $isMutation = $this->isPost()
            || in_array(Request::method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            || strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

        if ($isMutation) {
            $this->validateCsrf();
            $this->validateHoneypot();
        }

        // 3. Separate whitelist keys from validation rules
        $rules = [];
        $allowedKeys = [];

        foreach ($schema as $key => $rule) {
            if (is_int($key)) {
                // Positional string: 'summary', 'price'
                $allowedKeys[] = $rule;
            } else {
                // Associative: 'title' => 'required|min:5' or 'slug' => ['required', 'min:3']
                $allowedKeys[] = $key;
                $rules[$key] = is_array($rule) ? implode('|', $rule) : (string) $rule;
            }
        }

        // Auto-whitelist confirmation fields (e.g. password_confirmation for confirmed rule)
        foreach ($rules as $field => $ruleString) {
            if (str_contains($ruleString, 'confirmed')) {
                $allowedKeys[] = $field . '_confirmation';
            }
        }
        $allowedKeys = array_values(array_unique($allowedKeys));

        // 4. Extract only allowed inputs (mass-assignment protection)
        $data = $this->onlyInputs($allowedKeys);

        // 5. Run validation if rules exist
        if (!empty($rules)) {
            $errors = $this->validate($data, $rules);
            if (!empty($errors)) {
                if (function_exists('flash_inputs')) {
                    flash_inputs($data);
                }
                $firstError = reset($errors);
                $this->flash('errors', is_array($errors) ? implode(' ', $errors) : (string) $errors);
                $this->flash('error', (string) $firstError);
                $this->back();
                exit;
            }
        }

        return $data;
    }

    /**
     * Enforce CSRF token and anti-bot Honeypot verification simultaneously.
     */
    protected function secure(): void
    {
        $this->validateCsrf();
        $this->validateHoneypot();
    }

    /**
     * Expressive success flash shortcut.
     * Example: return $this->success('Saved!')->go('articles.index');
     */
    protected function success(string $message): self
    {
        return $this->flashSuccess($message);
    }

    /**
     * Expressive error flash shortcut.
     * Example: return $this->fail('Invalid submission')->back();
     */
    protected function fail(string $message): self
    {
        return $this->flashError($message);
    }

    /**
     * Expressive route redirect shortcut.
     * Example: return $this->success('Done')->go('articles.show', ['slug' => $slug]);
     */
    protected function go(string $name, array|string|int $params = [], int $statusCode = 302): void
    {
        $this->redirectToRoute($name, $params, $statusCode);
    }

    /**
     * Expressive JSON API responder.
     * Example: $this->respond(['status' => 'ok', 'data' => $items]);
     */
    protected function respond(mixed $data, int $statusCode = 200): void
    {
        $this->json($data, $statusCode);
    }

    /**
     * Abort request with an HTTP error code.
     * Automatically renders Pages/errors/{code}.srin.php if present.
     */
    protected function abort(int $code = 404, string $message = ''): void
    {
        Response::renderError($code, $message);
    }

    /**
     * Download a file securely from the assets/ directory.
     * Non-asset directories like Pages/ are strictly blocked.
     */
    protected function download(string $assetPath, ?string $downloadName = null): void
    {
        Response::download($assetPath, $downloadName);
    }
}
