@Connect('layouts/docs')

<p class="doc-lead">
    In Srin Engine, static asset management is seamless. The <code>{{asset('')}}</code> helper generates direct, fully qualified web URLs (e.g. <code>https://example.com/assets/images/photo.png</code>) rather than local filesystem paths, with built-in subfolder detection, reverse-proxy SSL normalization, and automatic image folder mapping.
</p>

<h2>The Direct Asset Link Superpower</h2>
<p>
    When working in views, you shouldn't have to worry about whether your application is hosted in a root domain, a nested subfolder (e.g., <code>/app/v2/</code>), or behind an SSL terminating reverse proxy.
</p>
<p>
    Srin Engine's <code>{{asset('')}}</code> helper automatically resolves the complete, direct web link:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>&lt;!-- Pass an image filename directly --&gt;
&lt;img src="{{asset('logo.png')}}" alt="Srin Engine Logo"&gt;

&lt;!-- Output rendered by the browser: --&gt;
&lt;!-- &lt;img src="http://localhost:8000/assets/images/logo.png" alt="Srin Engine Logo"&gt; --&gt;</code></pre>
</div>

<div class="callout callout-tip">
    <div class="callout-title">Automatic Image Folder Resolution</div>
    <p>Notice how you don't even need to type <code>images/logo.png</code>! If you pass an image extension (such as <code>.png</code>, <code>.jpg</code>, <code>.svg</code>, <code>.webp</code>, <code>.avif</code>), Srin automatically maps the link into the <code>assets/images/</code> folder.</p>
</div>

<h2>Asset URL Resolution Matrix</h2>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Resolved URL Output</th>
                <th>Behavior Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>{{asset('avatar.jpg')}}</code></td>
                <td><code>http://localhost:8000/assets/images/avatar.jpg</code></td>
                <td>Auto-maps image extension to <code>assets/images/</code>.</td>
            </tr>
            <tr>
                <td><code>{{asset('images/banner.webp')}}</code></td>
                <td><code>http://localhost:8000/assets/images/banner.webp</code></td>
                <td>Preserves explicit category directory.</td>
            </tr>
            <tr>
                <td><code>{{asset('css/app.css')}}</code></td>
                <td><code>http://localhost:8000/assets/css/app.css</code></td>
                <td>Maps stylesheet to CSS directory.</td>
            </tr>
            <tr>
                <td><code>{{asset('js/app.js?v=2.1')}}</code></td>
                <td><code>http://localhost:8000/assets/js/app.js?v=2.1</code></td>
                <td>Preserves cache-busting query strings.</td>
            </tr>
            <tr>
                <td><code>{{asset('https://cdn.example.com/font.woff2')}}</code></td>
                <td><code>https://cdn.example.com/font.woff2</code></td>
                <td>External CDN URLs are returned untouched.</td>
            </tr>
            <tr>
                <td><code>{{asset('')}}</code></td>
                <td><code>http://localhost:8000/assets/</code></td>
                <td>Returns the base public asset directory URL.</td>
            </tr>
        </tbody>
    </table>
</div>

<h2>Recommended Asset Directory Structure</h2>
<p>
    Organize your static files inside the root <code>assets/</code> or <code>Public/assets/</code> folder:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Directory Structure</span>
    </div>
    <pre><code>assets/
├── css/
│   ├── app.css
│   └── components.css
├── js/
│   ├── app.js
│   └── tracker.js
├── images/
│   ├── favicon.svg
│   ├── logo.png
│   └── banner.webp
├── fonts/
│   └── inter.woff2
└── downloads/
    └── user_guide.pdf</code></pre>
</div>

<h2>Secure Asset Downloads via Controllers</h2>
<p>
    To serve protected or downloadable files to users without exposing your filesystem or internal template directories, use the controller's <code>$this->download()</code> method:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Controller/DocumentController.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>public function downloadInvoice(string $id): void
{
    // Verify user authorization...
    $filename = "invoices/inv_{$id}.pdf";

    // Streams secure download with custom filename
    $this->download($filename, "My_Invoice_{$id}.pdf");
}</code></pre>
</div>

<div class="callout callout-warning">
    <div class="callout-title">Directory Traversal Defense</div>
    <p><code>$this->download()</code> strictly validates paths against the <code>assets/</code> folder. Any attempt to use dot-dot sequences (<code>../Pages/index.srin.php</code> or <code>../.env</code>) is immediately blocked with an HTTP 403 Forbidden exception.</p>
</div>
