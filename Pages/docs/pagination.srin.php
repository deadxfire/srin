@Connect('layouts/docs')

<p class="doc-lead">
    Srin Engine includes an intuitive, accessible database paginator. Paginating query results takes a single call to <code>->paginate()</code>. Query parameters (such as search keywords and filters) are preserved automatically across page navigation links.
</p>

<h2>Paginating Query Results</h2>
<p>
    Call <code>paginate($perPage)</code> directly on any ActiveRecord Model or QueryBuilder instance:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Controller/BlogController.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>public function index(): string
{
    $posts = Post::where('is_published', true)
        ->search('title', $this->get('q'))
        ->filter('category', $this->get('category'))
        ->latest()
        ->paginate(10); // 10 posts per page

    return $this->view('blog/index', [
        'posts' => $posts,
    ]);
}</code></pre>
</div>

<div class="callout callout-tip">
    <div class="callout-title">Automatic Query String Preservation</div>
    <p>Unlike other frameworks where you must manually call <code>->appends(['q' => $q, 'category' => $category])</code>, Srin Engine automatically detects active <code>$_GET</code> parameters and preserves them across pagination buttons!</p>
</div>

<h2>Rendering Pagination in Views</h2>
<p>
    Render pagination controls in your <code>.srin.php</code> view using the native <code>@@paginate()</code> directive or the <code>$posts->render()</code> method:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template (Pages/blog/index.srin.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>&lt;div class="posts-list"&gt;
    &lt;?php foreach ($posts as $post): ?&gt;
        &lt;div class="post-card"&gt;
            &lt;h3&gt;@{{ $post->title }}&lt;/h3&gt;
            &lt;p&gt;@{{ $post->summary }}&lt;/p&gt;
        &lt;/div&gt;
    &lt;?php endforeach; ?&gt;
&lt;/div&gt;

&lt;!-- Render modern pagination navigation links --&gt;
@@paginate($posts)</code></pre>
</div>

<h2>Iterating &amp; Array Access</h2>
<p>
    The <code>Paginator</code> implements PHP's <code>IteratorAggregate</code>, <code>Countable</code>, and <code>ArrayAccess</code> interfaces. You can loop over it directly, count items, or access specific indices:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Direct loop
foreach ($posts as $post) {
    echo $post->title;
}

// Count items on CURRENT page
echo count($posts);

// Access total items across ALL pages
echo $posts->total();</code></pre>
</div>

<h2>Paginator Inspection API</h2>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Method</th>
                <th>Return</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>$posts->total()</code></td>
                <td><code>int</code></td>
                <td>Total count of matching records across all pages.</td>
            </tr>
            <tr>
                <td><code>$posts->perPage()</code></td>
                <td><code>int</code></td>
                <td>Number of records displayed per page.</td>
            </tr>
            <tr>
                <td><code>$posts->currentPage()</code></td>
                <td><code>int</code></td>
                <td>The current active page number (1-indexed).</td>
            </tr>
            <tr>
                <td><code>$posts->lastPage()</code></td>
                <td><code>int</code></td>
                <td>The last available page number.</td>
            </tr>
            <tr>
                <td><code>$posts->hasPages()</code></td>
                <td><code>bool</code></td>
                <td>Returns true if the total exceeds <code>perPage</code>.</td>
            </tr>
            <tr>
                <td><code>$posts->onFirstPage()</code></td>
                <td><code>bool</code></td>
                <td>Returns true if user is on page 1.</td>
            </tr>
            <tr>
                <td><code>$posts->hasMorePages()</code></td>
                <td><code>bool</code></td>
                <td>Returns true if there are additional pages ahead.</td>
            </tr>
            <tr>
                <td><code>$posts->firstItem()</code></td>
                <td><code>?int</code></td>
                <td>The 1-based index of the first record on current page.</td>
            </tr>
            <tr>
                <td><code>$posts->lastItem()</code></td>
                <td><code>?int</code></td>
                <td>The 1-based index of the last record on current page.</td>
            </tr>
            <tr>
                <td><code>$posts->appends(array $query)</code></td>
                <td><code>self</code></td>
                <td>Explicitly append additional custom query parameters.</td>
            </tr>
        </tbody>
    </table>
</div>

<h2>JSON &amp; API Serialization</h2>
<p>
    When returning paginated data from an API endpoint, pass the paginator directly to <code>$this->json()</code>:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>public function apiIndex(): void
{
    $posts = Post::latest()->paginate(20);
    $this->json($posts);
}</code></pre>
</div>

<p>Srin formats the JSON payload with complete pagination metadata:</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">JSON</span>
    </div>
    <pre><code>{
    "data": [ { "id": 1, "title": "First Post" }, ... ],
    "total": 45,
    "per_page": 20,
    "current_page": 1,
    "last_page": 3,
    "has_more": true
}</code></pre>
</div>
