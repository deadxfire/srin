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
     * Check if request is AJAX / Fetch.
     */
    protected function isAjax(): bool
    {
        return Request::isAjax();
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
                if ($rule === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[$field] = 'Please provide a valid URL.';
                    break;
                }

                // min:N
                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    $len = is_string($value) ? mb_strlen($value) : $value;
                    if ($len < $min) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
                        break;
                    }
                }

                // max:N
                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    $len = is_string($value) ? mb_strlen($value) : $value;
                    if ($len > $max) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " cannot exceed {$max} characters.";
                        break;
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

        // 2. Automated security verification on mutations
        if ($this->isPost()) {
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
