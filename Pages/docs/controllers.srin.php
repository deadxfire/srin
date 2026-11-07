@Connect('layouts/docs')

<p class="doc-lead">
    Controllers in Srin Engine are clean, readable, and packed with built-in superpowers. By extending <code>Core\BaseController</code>, your classes gain instant access to input parsing, automatic type casting, flash sessions, validation, and secure downloads—without requiring excessive dependency injection or boilerplate.
</p>

<h2>Creating a Controller</h2>
<p>
    Scaffold a new controller using the Srin CLI:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>php srin make:controller Article</code></pre>
</div>

<p>This creates <code>Controller/ArticleController.php</code>:</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>namespace Controller;

use Core\BaseController;
use Model\Article;

class ArticleController extends BaseController
{
    public function index(): string
    {
        $articles = Article::where('is_published', true)
            ->search('title', $this->get('q'))
            ->filter('category', $this->get('category'))
            ->latest()
            ->paginate(10);

        return $this->view('articles/index', [
            'articles' => $articles,
        ]);
    }
}</code></pre>
</div>

<h2>Complete BaseController Methods Reference</h2>
<p>
    Every controller extending <code>BaseController</code> inherits the following 25+ helper methods:
</p>

<h3>1. View &amp; Response Methods</h3>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Method Signature</th>
                <th>Return</th>
                <th>Description &amp; Example</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>$this->view(string $page, array $data = [])</code></td>
                <td><code>string</code></td>
                <td>Renders a <code>Pages/{$page}.srin.php</code> template with data variables.<br><code>return $this->view('blog/show', ['post' => $post]);</code></td>
            </tr>
            <tr>
                <td><code>$this->render(string $page, array $data = [])</code></td>
                <td><code>string</code></td>
                <td>Underlying alias for <code>view()</code>.</td>
            </tr>
            <tr>
                <td><code>$this->json(mixed $data, int $status = 200)</code></td>
                <td><code>void</code></td>
                <td>Sends a <code>Content-Type: application/json</code> response and terminates execution.<br><code>$this->json(['success' => true, 'id' => 42]);</code></td>
            </tr>
            <tr>
                <td><code>$this->redirect(string $url, int $status = 302)</code></td>
                <td><code>void</code></td>
                <td>Performs a CRLF-injection protected HTTP header redirection.</td>
            </tr>
            <tr>
                <td><code>$this->back(int $status = 302, string $fallback = '/')</code></td>
                <td><code>void</code></td>
                <td>Redirects the user back to the referring page (<code>HTTP_REFERER</code>).<br><code>$this->flashSuccess('Saved!')->back();</code></td>
            </tr>
            <tr>
                <td><code>$this->redirectToRoute(string $name, array $params = [], int $status = 302)</code></td>
                <td><code>void</code></td>
                <td>Redirects to a named route.<br><code>$this->redirectToRoute('blog.index');</code></td>
            </tr>
            <tr>
                <td><code>$this->route(string $name, array $params = [])</code></td>
                <td><code>string</code></td>
                <td>Generates a full URL for a registered named route.</td>
            </tr>
            <tr>
                <td><code>$this->abort(int $code = 404, string $message = '')</code></td>
                <td><code>void</code></td>
                <td>Stops request and renders custom error view in <code>Pages/errors/{$code}.srin.php</code>.<br><code>$this->abort(403, 'Unauthorized access.');</code></td>
            </tr>
            <tr>
                <td><code>$this->download(string $assetPath, ?string $downloadName = null)</code></td>
                <td><code>void</code></td>
                <td>Streams a secure file download from <code>assets/</code>. Path traversal outside assets is blocked.</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>2. Request Input &amp; Query Parsing</h3>
<p>
    All input helpers in Srin automatically apply <strong>trimming and sanitization</strong> to prevent unexpected whitespace and malicious payload injections.
</p>

<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Method</th>
                <th>Type</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>$this->get(string $key = '', mixed $default = '')</code></td>
                <td><code>mixed</code></td>
                <td>Reads a sanitized value from <code>$_GET</code>. Omit key to get all GET parameters.</td>
            </tr>
            <tr>
                <td><code>$this->post(string $key = '', mixed $default = '')</code></td>
                <td><code>mixed</code></td>
                <td>Reads a sanitized value from <code>$_POST</code>. Omit key to get all POST parameters.</td>
            </tr>
            <tr>
                <td><code>$this->search(string $key = 'q', string $default = '')</code></td>
                <td><code>string</code></td>
                <td>Expressive shortcut to retrieve a search keyword.<br><code>$q = $this->search();</code></td>
            </tr>
            <tr>
                <td><code>$this->input(string $key, mixed $default = null)</code></td>
                <td><code>mixed</code></td>
                <td>Reads from either GET or POST.</td>
            </tr>
            <tr>
                <td><code>$this->int(string $key, int $default = 0)</code></td>
                <td><code>int</code></td>
                <td>Type-casts input strictly to an integer.<br><code>$page = $this->int('page', 1);</code></td>
            </tr>
            <tr>
                <td><code>$this->bool(string $key, bool $default = false)</code></td>
                <td><code>bool</code></td>
                <td>Parses boolean strings (<code>"1"</code>, <code>"true"</code>, <code>"yes"</code>, <code>"on"</code>).</td>
            </tr>
            <tr>
                <td><code>$this->stringInput(string $key, string $default = '')</code></td>
                <td><code>string</code></td>
                <td>Guarantees string output; arrays or objects trigger fallback.</td>
            </tr>
            <tr>
                <td><code>$this->has(string $key)</code> / <code>$this->filled($key)</code></td>
                <td><code>bool</code></td>
                <td>Returns true if key exists and is non-empty.</td>
            </tr>
            <tr>
                <td><code>$this->allInputs()</code></td>
                <td><code>array</code></td>
                <td>Returns merged, sanitized GET and POST inputs.</td>
            </tr>
            <tr>
                <td><code>$this->onlyInputs(array $keys)</code></td>
                <td><code>array</code></td>
                <td>Mass-assignment whitelist; retrieves only specified keys.</td>
            </tr>
            <tr>
                <td><code>$this->isPost()</code> / <code>$this->isGet()</code></td>
                <td><code>bool</code></td>
                <td>Inspects HTTP request method.</td>
            </tr>
            <tr>
                <td><code>$this->isAjax()</code></td>
                <td><code>bool</code></td>
                <td>Detects <code>X-Requested-With: XMLHttpRequest</code> or Fetch calls.</td>
            </tr>
        </tbody>
    </table>
</div>

<h3>3. Session Flash Messages</h3>
<p>
    Flash messages persist in the session for exactly one subsequent request—ideal for redirect feedback:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Method chaining with back() or redirect()
$this->flashSuccess('Article updated successfully!')
     ->redirectToRoute('articles.index');

// Available flash shortcuts:
$this->flashSuccess('Saved!');
$this->flashError('Something went wrong.');
$this->flashInfo('Please confirm your email.');
$this->flashWarning('Your session expires in 5 minutes.');</code></pre>
</div>

<p>Display flash messages in your <code>.srin.php</code> view templates using the <code>has_flash()</code> and <code>flash()</code> helpers:</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>&lt;?php if (has_flash('success')): ?&gt;
    &lt;div class="alert alert-success"&gt;
        &lt;?= flash('success') ?&gt;
    &lt;/div&gt;
&lt;?php endif; ?&gt;</code></pre>
</div>

<h3>4. Form Validation Engine</h3>
<p>
    Validate incoming data using expressive pipe-delimited rule strings:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>public function store(): void
{
    $this->validateCsrf();

    $data = $this->onlyInputs(['title', 'email', 'category', 'password', 'password_confirmation']);

    $errors = $this->validate($data, [
        'title'    => 'required|min:5|max:150',
        'email'    => 'required|email',
        'category' => 'required|in:technology,design,lifestyle',
        'password' => 'required|min:8|confirmed',
    ]);

    if (!empty($errors)) {
        $this->flashError('Please fix the errors below.')
             ->back();
        return;
    }

    Article::create($data);
    $this->flashSuccess('Article published!')->redirectToRoute('articles.index');
}</code></pre>
</div>

<div class="callout callout-tip">
    <div class="callout-title">Supported Validation Rules</div>
    <p><code>required</code>, <code>email</code>, <code>numeric</code>, <code>url</code>, <code>min:N</code>, <code>max:N</code>, <code>alpha</code>, <code>alphanumeric</code>, <code>in:foo,bar</code>, and <code>confirmed</code> (compares <code>field</code> with <code>field_confirmation</code>).</p>
</div>
