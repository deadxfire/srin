@Connect('layouts/docs')

<p class="doc-lead">
    Srin Engine features a lightning-fast, expressive HTTP router. Define web routes in <code>Routes/web.php</code> and stateless API routes in <code>Routes/api.php</code> with support for named routes, URI parameters, and route inspection.
</p>

<h2>Basic Route Registration</h2>
<p>
    Routes accept a URI pattern and a handler (either a Closure, a controller string <code>Controller@action</code>, or an array <code>[Controller::class, 'action']</code>):
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Routes/web.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>use Core\Router;
use Controller\HomeController;
use Controller\BlogController;

// Simple closure route
Router::get('/', function() {
    return 'Welcome to Srin Engine!';
});

// Controller action using Class string
Router::get('/about', 'HomeController@about');

// Controller action using Array syntax
Router::get('/blog', [BlogController::class, 'index']);</code></pre>
</div>

<h2>Available Router Methods</h2>
<p>
    The router supports all standard HTTP verbs:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>Router::get($uri, $action);
Router::post($uri, $action);
Router::put($uri, $action);
Router::patch($uri, $action);
Router::delete($uri, $action);
Router::options($uri, $action);
Router::any($uri, $action); // Matches any HTTP verb</code></pre>
</div>

<h2>Route Parameters</h2>
<p>
    Capture URI segments by wrapping parameter names in curly braces <code>{param}</code>. Srin automatically passes captured values to your controller method in the order they appear:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Single parameter
Router::get('/users/{id}', 'UserController@show');

// Multiple parameters
Router::get('/posts/{category}/{slug}', 'PostController@show');</code></pre>
</div>

<p>Inside your controller action, type-hint the incoming parameters:</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Controller/PostController.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>public function show(string $category, string $slug): string
{
    $post = Post::where('category', $category)
        ->where('slug', $slug)
        ->firstOrFail();

    return $this->view('posts/show', ['post' => $post]);
}</code></pre>
</div>

<h2>Named Routes &amp; URL Generation</h2>
<p>
    Naming your routes makes URL generation effortless and immune to path refactoring. Chain the <code>->name()</code> method:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>Router::get('/docs/{topic}', 'DocsController@show')->name('docs.show');
Router::get('/checkout', 'CartController@checkout')->name('cart.checkout');</code></pre>
</div>

<h3>Generating URLs with the <code>route()</code> Helper</h3>
<p>
    Use the global <code>route()</code> helper in PHP code or <code>@{{route('name')}}</code> in your <code>.srin.php</code> view templates:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code><!-- Without parameters -->
&lt;a href="@{{route('cart.checkout')}}"&gt;Proceed to Checkout&lt;/a&gt;

<!-- With route parameters -->
&lt;a href="@{{route('docs.show', ['topic' => 'routing'])}}"&gt;Read Routing Docs&lt;/a&gt;

<!-- In PHP Controller or Middleware -->
$url = route('docs.show', ['topic' => 'routing']);</code></pre>
</div>

<h2>Route-Aware Navigation Helpers</h2>
<p>
    Highlighting active navigation tabs in your navbar is built-in:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>&lt;nav&gt;
    &lt;a href="@{{route('home')}}" class="&lt;?= is_home() ? 'active' : '' ?&gt;"&gt;Home&lt;/a&gt;
    &lt;a href="@{{route('blog.index')}}" class="&lt;?= is_route('blog.index') ? 'active' : '' ?&gt;"&gt;Blog&lt;/a&gt;
    &lt;a href="@{{route('docs')}}" class="&lt;?= is_route_active('docs.*') ? 'active' : '' ?&gt;"&gt;Documentation&lt;/a&gt;
&lt;/nav&gt;</code></pre>
</div>

<div class="callout callout-info">
    <div class="callout-title">Tip: Inspecting Routes via CLI</div>
    <p>Run <code>php srin routes</code> at any time in your terminal to see all registered routes, HTTP methods, and their associated controller actions.</p>
</div>
