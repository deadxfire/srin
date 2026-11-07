<?php

namespace Core;

class BaseController
{
    /**
     * Render a view from the Pages directory.
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
     * Redirect to another URL.
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
     * Get an input value from the request.
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return Request::input($key, $default);
    }

    /**
     * Get all inputs from GET and POST.
     */
    protected function allInputs(): array
    {
        return Request::all();
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
     * Simple validation helper for static site forms.
     *
     * Example rules: ['name' => 'required', 'email' => 'required|email', 'message' => 'required|min:10']
     * Returns array of error messages indexed by field name.
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $value = trim((string) ($data[$field] ?? ''));

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && $value === '') {
                    $errors[$field] = ucfirst($field) . ' is required.';
                    break;
                }

                if ($rule === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Please provide a valid email address.';
                    break;
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = ucfirst($field) . " must be at least {$min} characters.";
                        break;
                    }
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (strlen($value) > $max) {
                        $errors[$field] = ucfirst($field) . " cannot exceed {$max} characters.";
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

