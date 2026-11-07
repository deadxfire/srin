@Connect('layouts/docs')

<p class="doc-lead">
    Srin Engine provides a rich collection of global helper functions that simplify common operations like reading environment variables, generating URLs, issuing redirects, running queries, flash messaging, and HTML escaping.
</p>

<h2>Helper Reference by Category</h2>

<h3>1. Configuration &amp; Environment</h3>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Helper</th>
                <th>Return</th>
                <th>Description &amp; Example</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>env(string $key, mixed $default = null)</code></td>
                <td><code>mixed</code></td>
                <td>Reads an environment variable from <code>.env</code> with automatic boolean, null, and empty string casting.<br><code>$debug = env('APP_DEBUG', false);</code></td>
            </tr>
            <tr>
                <td><code>config(?string $key = null, mixed $default = null)</code></td>
                <td><code>mixed</code></td>
                <td>Reads a value from <code>Config/*.php</code> using dot notation. If key omitted, returns all <code>Config/app.php</code> settings.<br><code>$driver = config('database.default');</code></td>
            </tr>
        </tbody>
    </table>
</div>

<h3>2. URL, Routing &amp; Assets</h3>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Helper</th>
                <th>Return</th>
                <th>Description &amp; Example</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>asset(string $path = '')</code></td>
                <td><code>string</code></td>
                <td>Generates a direct, absolute web URL to an asset. Automatically resolves image file extensions directly into <code>assets/images/</code>.<br><code>&lt;img src="{{asset('logo.png')}}"&gt;</code></td>
            </tr>
            <tr>
                <td><code>route(string $name, array|string|int $params = [])</code></td>
                <td><code>string</code></td>
                <td>Resolves the full URL for a registered named route.<br><code>$url = route('docs.show', ['topic' => 'controllers']);</code></td>
            </tr>
            <tr>
                <td><code>url(string $path = '')</code></td>
                <td><code>string</code></td>
                <td>Generates an application URL aware of reverse proxies and subdirectories.<br><code>$url = url('/pricing');</code></td>
            </tr>
            <tr>
                <td><code>app_origin()</code></td>
                <td><code>string</code></td>
                <td>Retrieves the current application origin (scheme + host + port).<br><code>$origin = app_origin(); // https://example.com</code></td>
            </tr>
            <tr>
                <td><code>is_home()</code></td>
                <td><code>bool</code></td>
                <td>Returns <code>true</code> if the current request is the homepage (<code>/</code>).</td>
            </tr>
            <tr>
                <td><code>is_route(string|array $patterns)</code></td>
                <td><code>bool</code></td>
                <td>Tests if the current route matches a name or wildcard pattern (e.g. <code>docs.*</code>).</td>
            </tr>
            <tr>
                <td><code>is_route_active(string|array $patterns, string $class = 'active')</code></td>
                <td><code>string</code></td>
                <td>Returns CSS class name if active, empty string otherwise.<br><code>&lt;a class="<?= is_route_active('docs.*') ?>"&gt;Docs&lt;/a&gt;</code></td>
            </tr>
            <tr>
                <td><code>request_uri()</code> / <code>request_path()</code></td>
                <td><code>string</code></td>
                <td>Returns the current relative path without query strings.</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>3. Response, Views &amp; Redirection</h3>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Helper</th>
                <th>Return</th>
                <th>Description &amp; Example</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>view(string $page, array $data = [])</code></td>
                <td><code>string</code></td>
                <td>Renders a <code>Pages/{$page}.srin.php</code> template with bound data.</td>
            </tr>
            <tr>
                <td><code>redirect(string $url, int $status = 302)</code></td>
                <td><code>void</code></td>
                <td>Issues an HTTP redirect header with CRLF injection protection.</td>
            </tr>
            <tr>
                <td><code>back(int $status = 302, string $fallback = '/')</code></td>
                <td><code>void</code></td>
                <td>Redirects back to the previous page (<code>HTTP_REFERER</code>).</td>
            </tr>
            <tr>
                <td><code>json(mixed $data, int $status = 200)</code></td>
                <td><code>void</code></td>
                <td>Sends a JSON response header and payload, terminating execution.</td>
            </tr>
            <tr>
                <td><code>abort(int $code = 404, string $message = '')</code></td>
                <td><code>void</code></td>
                <td>Aborts execution and renders the matching error template in <code>Pages/errors/{$code}.srin.php</code>.</td>
            </tr>
            <tr>
                <td><code>download(string $assetPath, ?string $downloadName = null)</code></td>
                <td><code>void</code></td>
                <td>Streams a secure file download from the <code>assets/</code> folder.</td>
            </tr>
            <tr>
                <td><code>e(?string $value)</code></td>
                <td><code>string</code></td>
                <td>Safe HTML entity escaping (XSS prevention).</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>4. Database &amp; Queries</h3>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Helper</th>
                <th>Return</th>
                <th>Description &amp; Example</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>db(?string $table = null)</code></td>
                <td><code>QueryBuilder|Database</code></td>
                <td>If a table name is passed, returns a fluent <code>QueryBuilder</code> instance.<br><code>$posts = db('posts')->latest()->get();</code><br>If called without arguments, acts as a database query gateway:<br><code>$count = db()->scalar("SELECT COUNT(*) FROM users");</code></td>
            </tr>
        </tbody>
    </table>
</div>

<h3>5. Security &amp; Sessions</h3>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Helper</th>
                <th>Return</th>
                <th>Description &amp; Example</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>csrf_token()</code></td>
                <td><code>string</code></td>
                <td>Retrieves or generates the active CSRF session token.</td>
            </tr>
            <tr>
                <td><code>csrf_field()</code></td>
                <td><code>string</code></td>
                <td>Generates <code>&lt;input type="hidden" name="_csrf" value="..."&gt;</code> HTML.</td>
            </tr>
            <tr>
                <td><code>csrf_verify(?string $token = null)</code></td>
                <td><code>bool</code></td>
                <td>Verifies submitted CSRF token using constant-time comparison.</td>
            </tr>
            <tr>
                <td><code>honeypot_field(string $name = '_srin_hp_check')</code></td>
                <td><code>string</code></td>
                <td>Generates invisible anti-bot honeypot HTML markup.</td>
            </tr>
            <tr>
                <td><code>honeypot_verify(string $name = '_srin_hp_check')</code></td>
                <td><code>bool</code></td>
                <td>Validates that honeypot field was left blank by human visitor.</td>
            </tr>
            <tr>
                <td><code>rate_limit(string $key, int $max = 5, int $decaySeconds = 60)</code></td>
                <td><code>bool</code></td>
                <td>Checks if the current IP has exceeded the specified rate limit.</td>
            </tr>
            <tr>
                <td><code>flash(string $key, ?string $message = null)</code></td>
                <td><code>?string</code></td>
                <td>Sets or retrieves (and unsets) a one-time session flash notification.<br><code>flash('success', 'Post saved!');</code><br><code>$msg = flash('success');</code></td>
            </tr>
            <tr>
                <td><code>has_flash(string $key)</code></td>
                <td><code>bool</code></td>
                <td>Checks if a flash key is currently present in the session without deleting it.</td>
            </tr>
            <tr>
                <td><code>old(string $key, string $default = '')</code></td>
                <td><code>string</code></td>
                <td>Retrieves input value flashed from previous failed form submission.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="callout callout-info">
    <div class="callout-title">Extending Global Helpers</div>
    <p>You can define your own project-specific helper functions by creating a file in your project (e.g. <code>App/helpers.php</code>) and adding it to the <code>"files"</code> autoload array inside your <code>composer.json</code>.</p>
</div>
