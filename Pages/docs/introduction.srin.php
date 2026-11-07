@Connect('layouts/docs')

<p class="doc-lead">
    <strong>Srin Engine</strong> is a high-performance, standalone PHP web framework built for developers who crave elegance, speed, and zero boilerplate. Engineered from the ground up by Arindam Makar, it provides an expressive syntax that feels natural, intuitive, and modern.
</p>

<h2>The Srin Philosophy</h2>
<p>
    Modern web development often drowns in excessive configuration, bloated vendor folders, and redundant lines of boilerplate code. Simple tasks like reading an input query, filtering a database query, or returning distinct categories frequently demand 20 to 40 lines of repetitive ceremony.
</p>
<p>
    <strong>Srin Engine challenges this status quo.</strong> Every subsystem in Srin is engineered around one core goal:
    <em>maximum developer velocity with minimum code clutter</em>.
</p>

<div class="callout callout-tip">
    <div class="callout-title">The Srin Principle</div>
    <p>If a common developer task takes 10 lines of code in other frameworks, Srin aims to make it expressive, clean, and achievable in 2 to 3 lines—without sacrificing type safety or database efficiency.</p>
</div>

<h2>The Standalone Evolution: Before &amp; After</h2>
<p>
    Consider a standard real-world controller action: reading a search string and category filter from the query parameters, querying published posts with eager-loaded comments, paginating, preserving query strings, and fetching distinct categories for the filter dropdown.
</p>

<h3>Traditional Framework Boilerplate (35+ Lines)</h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Verbose Style)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>public function index(): string
{
    $search = trim((string) $this->input('q', ''));
    $category = trim((string) $this->input('category', ''));

    $query = Post::where('is_published', true)->with('comments');

    if ($category !== '') {
        $query->where('category', $category);
    }

    if ($search !== '') {
        $query->where('title', 'LIKE', "%{$search}%");
    }

    $posts = $query->latest()->paginate(4);

    if ($search !== '' || $category !== '') {
        $posts->appends([
            'q'        => $search,
            'category' => $category,
        ]);
    }

    // Get distinct categories
    $allPosts = Post::where('is_published', true)->get();
    $categories = array_values(array_unique(array_filter(array_map(fn($p) => $p->category, $allPosts))));

    return $this->render('blog/index', [
        'title'          => 'Explore Srin Engine Blog',
        'posts'          => $posts,
        'categories'     => $categories,
        'activeCategory' => $category,
        'searchQuery'    => $search,
        'totalPosts'     => count($allPosts),
    ]);
}</code></pre>
</div>

<h3>The Srin Standalone Way (12 Clean Lines)</h3>
<p>
    With Srin Engine's standalone superpowers—including <code>$this->get()</code>, <code>$query->filter()</code>, <code>$query->search()</code>, auto-retaining paginators, and native <code>distinctValues()</code>—the exact same action becomes pure poetry:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Srin Engine Standalone Style)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>public function index(): string
{
    $posts = Post::where('is_published', true)
        ->with('comments')
        ->filter('category', $this->get('category'))
        ->search('title', $this->get('q'))
        ->latest()
        ->paginate(4);

    return $this->view('blog/index', [
        'title'          => 'Explore Srin Engine Blog',
        'posts'          => $posts,
        'categories'     => Post::where('is_published', true)->distinctValues('category'),
        'activeCategory' => $this->get('category'),
        'searchQuery'    => $this->get('q'),
        'totalPosts'     => Post::where('is_published', true)->count(),
    ]);
}</code></pre>
</div>

<h2>Core Framework Architecture</h2>
<p>
    Srin is structured cleanly into dedicated modules that operate independently or in harmony:
</p>

<div class="feature-grid">
    <div class="feature-card">
        <div class="feature-icon">&#9889;</div>
        <div class="feature-title">Ultra-Fast Routing</div>
        <div class="feature-desc">Dynamic route matching with parameterized wildcards (<code>{id}</code>, <code>{slug}</code>), named routes, and route-aware navigation helpers.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#128190;</div>
        <div class="feature-title">ActiveRecord &amp; Fluent Query</div>
        <div class="feature-desc">ActiveRecord models with fillable mass-assignment protection, relationships (<code>hasMany</code>, <code>belongsTo</code>), and fluent query chaining.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#127912;</div>
        <div class="feature-title">.srin.php Template Engine</div>
        <div class="feature-desc">Zero-overhead template compiler supporting <code>@Connect</code> layouts, <code>@contentConnect</code> sections, <code>{{asset('')}}</code>, and CSRF directives.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#128737;</div>
        <div class="feature-title">Defense-in-Depth Security</div>
        <div class="feature-desc">Built-in token CSRF verification, anti-bot honeypot traps, IP-based rate limiting, and safe HTML sanitization.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#128736;</div>
        <div class="feature-title">Srin CLI Artisan</div>
        <div class="feature-desc">A unified console companion to scaffold controllers, models, migrations, run migrations, and launch local development servers.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#128640;</div>
        <div class="feature-title">Lightweight Footprint</div>
        <div class="feature-desc">Boots in sub-milliseconds without heavy runtime dependency graphs. Perfect for production microservices and full-stack web portals.</div>
    </div>
</div>

<h2>Framework Specifications</h2>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Feature</th>
                <th>Srin Engine Specification</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>PHP Compatibility</strong></td>
                <td>PHP 8.1, 8.2, 8.3, 8.4+</td>
                <td>Leverages modern union types, typed properties, and nullsafe operators.</td>
            </tr>
            <tr>
                <td><strong>Database Drivers</strong></td>
                <td>SQLite, MySQL / MariaDB, PostgreSQL</td>
                <td>Unified PDO abstraction with automatic identifier quoting.</td>
            </tr>
            <tr>
                <td><strong>View Templates</strong></td>
                <td><code>*.srin.php</code></td>
                <td>Compiled directly to optimized native PHP opcache.</td>
            </tr>
            <tr>
                <td><strong>Architecture</strong></td>
                <td>Modular MVC (Model-View-Controller)</td>
                <td>Clean separation of concerns with full standalone flexibility.</td>
            </tr>
            <tr>
                <td><strong>Author &amp; Maintainer</strong></td>
                <td>Arindam Makar (deadxfire)</td>
                <td>Open source MIT License.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="callout callout-info">
    <div class="callout-title">Ready to Dive In?</div>
    <p>Continue to the next chapter: <a href="{{route('docs.show', ['topic' => 'installation'])}}"><strong>Installation &amp; Quickstart</strong></a> to set up your first Srin Engine application in under 2 minutes!</p>
</div>
