@Connect('layouts/docs')

<p class="doc-lead">
    Srin Engine includes a native, blazingly fast template compiler supporting <code>.srin.php</code> files. It provides layout inheritance (<code>@Connect</code>), named slot injection (<code>@contentConnect</code>), partial inclusions, auto-escaping, CSRF injection, and direct asset linking.
</p>

<h2>Layout Inheritance with <code>@Connect</code></h2>
<p>
    Templates inherit master layouts using the <code>@Connect('layout_name')</code> directive at the very top of the file:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template (Pages/blog/index.srin.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>@@Connect('layouts/app')
@@title('Latest Articles')

&lt;section class="hero"&gt;
    &lt;h1&gt;Welcome to the Blog&lt;/h1&gt;
    &lt;p&gt;Explore our latest insights and tutorials.&lt;/p&gt;
&lt;/section&gt;

&lt;div class="articles"&gt;
    &lt;?php foreach ($posts as $post): ?&gt;
        &lt;article&gt;
            &lt;h2&gt;@{{ $post->title }}&lt;/h2&gt;
            &lt;p&gt;@{{ $post->summary }}&lt;/p&gt;
        &lt;/article&gt;
    &lt;?php endforeach; ?&gt;
&lt;/div&gt;</code></pre>
</div>

<h2>Creating Master Layouts</h2>
<p>
    Master layouts live in <code>Pages/layouts/</code>. Use <code>@content</code> where the page body should be injected:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template (Pages/layouts/app.srin.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;&lt;?= htmlspecialchars($title ?? 'Srin Application', ENT_QUOTES, 'UTF-8') ?&gt;&lt;/title&gt;
    &lt;link rel="stylesheet" href="@{{asset('css/app.css')}}"&gt;
    @@content('head')
&lt;/head&gt;
&lt;body&gt;
    @@include('partials/navbar')

    &lt;main class="container"&gt;
        @@content
    &lt;/main&gt;

    @@include('partials/footer')

    &lt;script src="@{{asset('js/app.js')}}"&gt;&lt;/script&gt;
    @@content('scripts')
&lt;/body&gt;
&lt;/html&gt;</code></pre>
</div>

<h2>Named Content Sections (Slots)</h2>
<p>
    When a child template needs to push scripts or stylesheets into a specific slot in the parent layout, use <code>@contentConnect('slot_name')</code>:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>@@Connect('layouts/app')

&lt;h1&gt;Interactive Dashboard&lt;/h1&gt;
&lt;canvas id="analyticsChart"&gt;&lt;/canvas&gt;

@@contentConnect('scripts')
&lt;script src="https://cdn.jsdelivr.net/npm/chart.js"&gt;&lt;/script&gt;
&lt;script&gt;
    const ctx = document.getElementById('analyticsChart');
    new Chart(ctx, { /* config */ });
&lt;/script&gt;
@@endContentConnect</code></pre>
</div>

<h2>Directives &amp; Syntax</h2>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Directive / Tag</th>
                <th>Output / Purpose</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>@{{ $var }}</code></td>
                <td>Safe HTML-escaped output (prevents XSS attacks).</td>
            </tr>
            <tr>
                <td><code>@{!! $rawHtml !!}</code></td>
                <td>Unescaped, raw HTML output (use with trusted content).</td>
            </tr>
            <tr>
                <td><code>@@csrf</code></td>
                <td>Renders hidden <code>_csrf_token</code> input field for secure forms.</td>
            </tr>
            <tr>
                <td><code>@@honeypot</code></td>
                <td>Renders hidden anti-spam honeypot trap to block automated bots.</td>
            </tr>
            <tr>
                <td><code>@@include('partial/name')</code></td>
                <td>Embeds a partial template from <code>Pages/</code> or <code>Pages/partials/</code>.</td>
            </tr>
            <tr>
                <td><code>@@paginate($paginator)</code></td>
                <td>Renders complete accessible HTML pagination navigation buttons.</td>
            </tr>
            <tr>
                <td><code>@{{asset('images/logo.png')}}</code></td>
                <td>Generates absolute URL to public assets with automatic subfolder detection.</td>
            </tr>
            <tr>
                <td><code>@{{route('blog.show', ['slug' => 'demo'])}}</code></td>
                <td>Resolves full URL for a registered named route.</td>
            </tr>
            <tr>
                <td><code>@{{url('/contact')}}</code></td>
                <td>Resolves absolute URL from relative path.</td>
            </tr>
        </tbody>
    </table>
</div>

<h2>Reusing Components with <code>@include</code></h2>
<p>
    Keep your views clean and modular by breaking cards, navigation bars, and modals into partials inside <code>Pages/partials/</code>:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>&lt;!-- Include a partial --&gt;
@@include('partials/newsletter-card')</code></pre>
</div>
