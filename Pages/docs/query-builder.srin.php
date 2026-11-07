@Connect('layouts/docs')

<p class="doc-lead">
    The Srin Engine <strong>QueryBuilder</strong> provides a fluent, chainable interface for constructing SQL queries. It is packed with unique developer conveniences like <code>search()</code>, <code>filter()</code>, <code>distinctValues()</code>, and conditional <code>when()</code> clauses that eliminate boilerplate if-statements.
</p>

<h2>QueryBuilder Superpowers</h2>

<h3>1. Auto-Conditional Search: <code>search()</code></h3>
<p>
    Instead of manually checking if a search term is present and appending <code>LIKE %...%</code> queries, use <code>search()</code>. If the keyword is empty or null, the clause is automatically skipped:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Searches single column
$posts = Post::search('title', $this->get('q'))->get();

// Searches across multiple columns with OR conditions
$posts = Post::search(['title', 'content', 'author_name'], $this->get('q'))->get();</code></pre>
</div>

<h3>2. Auto-Conditional Filter: <code>filter()</code></h3>
<p>
    Filter a column only when a non-empty value is supplied:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Only applies WHERE category = 'technology' if category was passed in request
$posts = Post::filter('category', $this->get('category'))
    ->filter('status', $this->get('status'))
    ->get();</code></pre>
</div>

<h3>3. Instant Dropdown Data: <code>distinctValues()</code></h3>
<p>
    Building category or tag filter dropdowns traditionally requires fetching all records, mapping an array, filtering nulls, and running <code>array_unique()</code>. In Srin Engine, one call handles it all at the database layer:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Returns a clean array of non-null, unique strings: ['Design', 'Development', 'Marketing']
$categories = Post::where('is_published', true)->distinctValues('category');</code></pre>
</div>

<h3>4. Conditional Queries with <code>when()</code></h3>
<p>
    Conditionally apply clauses to a query without breaking the chain:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>$role = $this->get('role');

$users = db('users')
    ->when($role !== '', function($query) use ($role) {
        $query->where('role', $role);
    })
    ->get();</code></pre>
</div>

<h3>5. Column Value Extraction: <code>pluck()</code> &amp; <code>value()</code></h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Extract single column into flat array: ['alice@test.com', 'bob@test.com']
$emails = db('users')->pluck('email');

// Keyed by another column: [1 => 'Alice', 2 => 'Bob']
$names = db('users')->pluck('name', 'id');

// Retrieve a single column value from first matching row
$email = db('users')->where('id', 1)->value('email');</code></pre>
</div>

<h3>6. Batch Increments &amp; Decrements</h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Increment view counter by 1
db('posts')->where('id', 42)->increment('views');

// Decrement stock by 5
db('products')->where('id', 10)->decrement('stock', 5);</code></pre>
</div>

<h3>7. Memory-Safe Chunking: <code>chunk()</code></h3>
<p>
    Process thousands of database records without exhausting PHP memory limits:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>db('users')->chunk(200, function ($users) {
    foreach ($users as $user) {
        // Dispatch newsletter...
    }
});</code></pre>
</div>

<h2>Core Where Clauses</h2>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Method</th>
                <th>SQL Representation</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>where('votes', 100)</code></td>
                <td><code>WHERE votes = 100</code></td>
            </tr>
            <tr>
                <td><code>where('age', '>=', 18)</code></td>
                <td><code>WHERE age >= 18</code></td>
            </tr>
            <tr>
                <td><code>orWhere('role', 'editor')</code></td>
                <td><code>OR role = 'editor'</code></td>
            </tr>
            <tr>
                <td><code>whereLike('title', '%php%')</code></td>
                <td><code>WHERE title LIKE '%php%'</code></td>
            </tr>
            <tr>
                <td><code>whereIn('status', ['draft', 'published'])</code></td>
                <td><code>WHERE status IN ('draft', 'published')</code></td>
            </tr>
            <tr>
                <td><code>whereNull('deleted_at')</code></td>
                <td><code>WHERE deleted_at IS NULL</code></td>
            </tr>
            <tr>
                <td><code>whereNotNull('verified_at')</code></td>
                <td><code>WHERE verified_at IS NOT NULL</code></td>
            </tr>
            <tr>
                <td><code>whereRaw("DATE(created_at) = ?", [$date])</code></td>
                <td><code>WHERE DATE(created_at) = ?</code></td>
            </tr>
        </tbody>
    </table>
</div>

<h2>Aggregates</h2>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>$count   = db('orders')->count();
$total   = db('orders')->sum('price');
$average = db('reviews')->avg('rating');
$max     = db('products')->max('price');
$min     = db('products')->min('price');
$exists  = db('users')->where('email', $email)->exists();</code></pre>
</div>
