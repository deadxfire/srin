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
     * Get an input value from the request (sanitized).
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return Request::input($key, $default);
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
    protected function flash(string $key, string $message): void
    {
        flash($key, $message);
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
