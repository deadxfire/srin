<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Welcome to Srin Engine - Developed by Arindam Makar', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/svg+xml" href="{{assets('/favicon.svg')}}">
    <link rel="alternate icon" href="{{assets('/favicon.ico')}}">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --font-sans: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            --radius: 8px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-sans); background-color: var(--bg-main); color: var(--text-main); line-height: 1.6; min-height: 100vh; display: flex; flex-direction: column; }
        .container { width: 100%; max-width: 1100px; margin: 0 auto; padding: 0 20px; }
        .site-header { background: #fff; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 100; }
        .nav-container { display: flex; align-items: center; justify-content: space-between; height: 70px; }
        .brand-logo { font-size: 1.25rem; font-weight: 700; color: var(--text-main); text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .logo-accent { color: var(--primary); font-family: var(--font-mono); }
        .dev-tag { font-size: 0.8rem; font-weight: 500; color: var(--text-muted); background: #f1f5f9; padding: 2px 8px; border-radius: 9999px; }
        .nav-links { display: flex; gap: 20px; }
        .nav-links a { color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 0.95rem; }
        .nav-links a:hover { color: var(--primary); }
        .menu-toggle { display: none; background: none; border: none; cursor: pointer; flex-direction: column; gap: 5px; }
        .menu-toggle span { width: 24px; height: 2px; background: var(--text-main); }
        .page-content { flex: 1; padding: 40px 0 60px; }
        .docs-hero { text-align: center; padding: 20px 20px 40px; }
        .hero-badge-row { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
        .hero-badge { display: inline-block; background: #e0e7ff; color: #4338ca; padding: 4px 14px; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; }
        .dev-badge { background: #dbeafe; color: #1e40af; }
        .docs-hero h1 { font-size: 2.6rem; letter-spacing: -0.02em; margin-bottom: 16px; font-weight: 800; }
        .docs-hero .lead { font-size: 1.2rem; color: var(--text-muted); max-width: 750px; margin: 0 auto 25px; }
        .hero-actions { display: flex; justify-content: center; gap: 15px; }
        .btn { display: inline-block; font-weight: 600; padding: 12px 24px; border-radius: var(--radius); text-decoration: none; cursor: pointer; font-size: 1rem; border: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-outline { background: transparent; color: var(--text-main); border: 1px solid var(--border-color); }
        .btn-outline:hover { background: #f1f5f9; }
        .docs-wrapper { display: grid; grid-template-columns: 240px 1fr; gap: 40px; align-items: start; }
        .docs-sidebar { position: sticky; top: 90px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow); }
        .sidebar-nav h4 { margin-bottom: 12px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
        .sidebar-nav ul { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .sidebar-nav a { color: var(--text-main); text-decoration: none; font-size: 0.9rem; display: block; }
        .sidebar-nav a:hover { color: var(--primary); font-weight: 600; }
        .docs-content { display: flex; flex-direction: column; gap: 30px; }
        .doc-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 30px; box-shadow: var(--shadow); }
        .doc-card h2 { font-size: 1.5rem; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #f1f5f9; }
        .doc-card h3 { font-size: 1.15rem; margin: 20px 0 10px; color: var(--primary); }
        p { margin-bottom: 12px; }
        pre { background: #0f172a; color: #f8fafc; padding: 16px; border-radius: var(--radius); overflow-x: auto; font-family: var(--font-mono); font-size: 0.9rem; margin: 12px 0 16px; }
        code { font-family: var(--font-mono); background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
        pre code { background: transparent; padding: 0; }
        .highlight-box { background: #eff6ff; border-left: 4px solid var(--primary); padding: 16px; border-radius: 4px; margin: 16px 0; }
        .step-list { margin: 15px 0 15px 20px; display: flex; flex-direction: column; gap: 14px; }
        .feature-bullets { margin: 12px 0 12px 20px; display: flex; flex-direction: column; gap: 8px; }
        .structure-list { display: flex; flex-direction: column; gap: 12px; margin-top: 15px; }
        .structure-item { background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 14px 18px; }
        .structure-item strong { color: var(--primary); font-family: var(--font-mono); font-size: 1rem; }
        .developer-card { display: flex; align-items: center; gap: 16px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 18px 22px; margin: 15px 0 20px; }
        .dev-avatar { width: 48px; height: 48px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; flex-shrink: 0; }
        .dev-card-info h3 { font-size: 1.05rem; margin-bottom: 4px; color: var(--text-main); }
        .dev-card-info p { margin-bottom: 0; font-size: 0.9rem; color: var(--text-muted); }
        .helpers-table-wrapper { overflow-x: auto; margin-top: 15px; }
        .helpers-table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
        .helpers-table th, .helpers-table td { padding: 10px 14px; border: 1px solid var(--border-color); text-align: left; }
        .helpers-table th { background: #f8fafc; font-weight: 600; }
        .site-footer { background: #fff; border-top: 1px solid var(--border-color); padding: 30px 0; font-size: 0.9rem; color: var(--text-muted); }
        .footer-container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        @media (max-width: 900px) { .docs-wrapper { grid-template-columns: 1fr; } .docs-sidebar { position: static; } }
        @media (max-width: 768px) { .menu-toggle { display: flex; } .nav-links { display: none; position: absolute; top: 70px; left: 0; right: 0; background: #fff; flex-direction: column; padding: 20px; border-bottom: 1px solid var(--border-color); } .nav-links.active { display: flex; } .docs-hero h1 { font-size: 2rem; } }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container nav-container">
            <a href="/" class="brand-logo">
                <span class="logo-accent">&lt;/&gt;</span> Srin Engine <span class="dev-tag">by Arindam Makar</span>
            </a>

            <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="nav-links" id="navLinks">
                <a href="#quickstart">Quick Start</a>
                <a href="#assets-guide">Assets</a>
                <a href="#srin-cli">CLI</a>
                <a href="#templates">.srin.php</a>
                <a href="#error-pages">Errors</a>
                <a href="#mail-transfer">Mail</a>
                <a href="https://github.com/deadxfire" target="_blank" style="color: var(--primary); font-weight: 600;">GitHub</a>
            </nav>
        </div>
    </header>

    <main class="page-content">
        <div class="container">
            <div class="docs-hero">
                <div class="hero-badge-row">
                    <span class="hero-badge">Core PHP Engine</span>
                    <span class="hero-badge dev-badge">Developed by Arindam Makar</span>
                    <span class="hero-badge">Zero Bloat</span>
                </div>
                <h1>Srin Engine for Static Websites</h1>
                <p class="lead">
                    A clean, fast Core PHP engine for static sites developed by <strong>Arindam Makar</strong>. Features dynamic extras like mail transfer, Composer packages in <code>Others/</code>, strict <code>.srin.php</code> templates, <code>@Connect</code> layouts, named routes, <code>@{{assets('/path')}}</code> direct linking, and automatic error page rendering.
                </p>
                <div class="hero-actions">
                    <a href="#quickstart" class="btn btn-primary">Quick Start Guide</a>
                    <a href="#error-pages" class="btn btn-outline">Error Pages Guide</a>
                </div>
            </div>

            <div class="docs-wrapper">
                <!-- Sticky Quick Navigation Sidebar -->
                <aside class="docs-sidebar">
                    <div class="sidebar-nav">
                        <h4>Documentation</h4>
                        <ul>
                            <li><a href="#quickstart">1. Quick Start</a></li>
                            <li><a href="#assets-guide">2. Asset Linking</a></li>
                            <li><a href="#folder-structure">3. Folder Structure</a></li>
                            <li><a href="#srin-cli">4. CLI Commands (srin)</a></li>
                            <li><a href="#controllers">5. Controllers</a></li>
                            <li><a href="#templates">6. .srin.php &amp; @Connect</a></li>
                            <li><a href="#routing">7. Defining Routes</a></li>
                            <li><a href="#mail-transfer">8. Mail Transfer</a></li>
                            <li><a href="#composer">9. Composer &amp; Download</a></li>
                            <li><a href="#error-pages">10. Custom Error Pages</a></li>
                            <li><a href="#security">11. Security &amp; Downloads</a></li>
                            <li><a href="#helpers">12. Global Helpers</a></li>
                        </ul>
                    </div>
                </aside>

                <!-- Main Documentation Content -->
                <div class="docs-content">
                    <!-- 1. Quick Start -->
                    <section id="quickstart" class="doc-card">
                        <h2>1. Quick Start &amp; Automated Download</h2>
                        <p>You can automatically download and scaffold Srin on any new computer using a single Composer command:</p>

                        <pre><code># Automatic Download &amp; Project Setup on a New Computer:
composer create-project deadxfire/srin my-website</code></pre>

                        <p>No default CSS, JS, or dummy controllers clutter your project. You build your own frontend directly:</p>
                        
                        <ol class="step-list">
                            <li>
                                <strong>Start local development server:</strong>
                                <pre><code>php srin serve
# or
php -S localhost:8000 index.php</code></pre>
                            </li>
                            <li>
                                <strong>Create your first Controller:</strong>
                                <pre><code>php srin make:controller HomeController</code></pre>
                            </li>
                            <li>
                                <strong>Create your first Page (generates <code>Pages/home.srin.php</code>):</strong>
                                <pre><code>php srin make:page home</code></pre>
                            </li>
                            <li>
                                <strong>Add your custom CSS or JS in <code>assets/</code>:</strong>
                                <pre><code>assets/css/style.css
assets/js/main.js
assets/images/banner.jpg</code></pre>
                            </li>
                            <li>
                                <strong>Connect the Route in <code>Routes/web.php</code>:</strong>
                                <pre><code>Router::get('/', 'HomeController@index')->name('home');</code></pre>
                            </li>
                        </ol>
                    </section>

                    <!-- 2. Assets Guide -->
                    <section id="assets-guide" class="doc-card">
                        <h2>2. Connecting Assets Directly with <code>{{assets('/path')}}</code></h2>
                        <p>
                            There are <strong>no default CSS or JS files</strong>. Whenever you connect CSS, JS, or public images, use the clean <code>{{assets('/path')}}</code> directive inside any <code>.srin.php</code> template. It resolves directly into the <code>assets/</code> folder:
                        </p>

                        <h3>Connecting CSS</h3>
                        <pre><code>&lt;link rel="stylesheet" href="{{assets('/css/style.css')}}"&gt;</code></pre>

                        <h3>Connecting JavaScript</h3>
                        <pre><code>&lt;script src="{{assets('/js/main.js')}}"&gt;&lt;/script&gt;</code></pre>

                        <h3>Adding Public Images</h3>
                        <p>Simply drop any image or folder inside <code>assets/images/</code> and link it directly:</p>
                        <pre><code>&lt;img src="{{assets('/images/logo.png')}}" alt="Site Logo"&gt;
&lt;img src="{{assets('/images/portfolio/project-1.jpg')}}" alt="Project"&gt;</code></pre>

                        <h3>Adding Custom Asset Folders</h3>
                        <p>You can add any folder into <code>assets/</code> (e.g. <code>assets/fonts/</code>, <code>assets/icons/</code>, <code>assets/videos/</code>). They link instantly:</p>
                        <pre><code>&lt;link rel="preload" href="{{assets('/fonts/inter.woff2')}}" as="font"&gt;
&lt;img src="{{assets('/icons/star.svg')}}" alt="Star"&gt;</code></pre>

                        <div class="highlight-box">
                            <strong>Note on Subfolders &amp; Production Servers:</strong>
                            <code>{{assets('/...')}}</code> automatically calculates your server path whether your site is on a root domain (<code>https://mysite.com</code>) or inside a server subfolder (<code>https://mysite.com/subfolder/</code>).
                        </div>
                    </section>

                    <!-- 3. Folder Structure -->
                    <section id="folder-structure" class="doc-card">
                        <h2>3. Folder Structure</h2>
                        <div class="structure-list">
                            <div class="structure-item">
                                <strong>assets/</strong>
                                <p>Contains your public assets (<code>assets/css/</code>, <code>assets/js/</code>, <code>assets/images/</code>, <code>assets/downloads/</code>, etc.). No default bloat.</p>
                            </div>
                            <div class="structure-item">
                                <strong>Controller/</strong>
                                <p>All your custom controllers live here. Extend <code>BaseController</code> to access <code>render()</code>, <code>json()</code>, <code>redirect()</code>, and <code>validate()</code>.</p>
                            </div>
                            <div class="structure-item">
                                <strong>Core/</strong>
                                <p>Srin engine core: <code>App</code>, <code>Router</code>, <code>Request</code>, <code>Response</code>, <code>View</code>, <code>Mailer</code>, and <code>Helper</code>.</p>
                            </div>
                            <div class="structure-item">
                                <strong>Config/</strong>
                                <p>Configuration options (<code>app.php</code> as main config; <code>mail.php</code> for SMTP and mail drivers).</p>
                            </div>
                            <div class="structure-item">
                                <strong>Pages/</strong>
                                <p>Your frontend <code>.srin.php</code> templates and master layouts. Connect any page to a layout using <code>@Connect('layouts/main')</code>.</p>
                            </div>
                            <div class="structure-item">
                                <strong>Routes/</strong>
                                <p>All URL routes are defined in <code>Routes/web.php</code> using clean <code>Router::get()</code> and <code>Router::post()</code> syntax.</p>
                            </div>
                            <div class="structure-item">
                                <strong>Others/</strong>
                                <p>Designated vendor directory for all Composer packages and the autoloader (configured via <code>composer.json</code>).</p>
                            </div>
                        </div>
                    </section>

                    <!-- 4. CLI Commands -->
                    <section id="srin-cli" class="doc-card">
                        <h2>4. Static CLI Commands (<code>php srin</code>)</h2>
                        <p>Use the built-in Srin CLI tool (<code>srin</code> or <code>php srin</code>) to generate controllers, pages, layouts, and error pages:</p>

                        <h3>Create a Standard Controller</h3>
                        <pre><code>php srin make:controller HomeController</code></pre>
                        <p>Generates <code>Controller/HomeController.php</code> extending <code>BaseController</code>.</p>

                        <h3>Create a Controller with Mail Transfer</h3>
                        <pre><code>php srin make:controller ContactController --mail</code></pre>
                        <p>Generates a complete controller with form validation, CSRF verification, and mail dispatch using <code>Core\Mailer</code>.</p>

                        <h3>Create a New Frontend Page (<code>.srin.php</code>)</h3>
                        <pre><code>php srin make:page about</code></pre>
                        <p>Generates <code>Pages/about.srin.php</code> pre-connected to <code>@Connect('layouts/main')</code>.</p>

                        <h3>Create a New Layout</h3>
                        <pre><code>php srin make:layout admin</code></pre>
                        <p>Generates <code>Pages/layouts/admin.srin.php</code> pre-wired with <code>{{assets('/css/style.css')}}</code> and <code>@content</code>.</p>

                        <h3>Create Custom Error Page</h3>
                        <pre><code>php srin make:error 404</code></pre>

                        <h3>List Registered Routes</h3>
                        <pre><code>php srin routes</code></pre>

                        <h3>Start the Local Server</h3>
                        <pre><code>php srin serve --port=8000</code></pre>
                    </section>

                    <!-- 5. Controllers -->
                    <section id="controllers" class="doc-card">
                        <h2>5. Writing Controllers</h2>
                        <p>Controllers inherit useful helper methods from <code>BaseController</code>:</p>
                        <pre><code>&lt;?php

namespace Controller;

use Core\BaseController;

class PortfolioController extends BaseController
{
    public function index(): string
    {
        // Renders Pages/portfolio.srin.php
        return $this->render('portfolio', [
            'title' => 'My Portfolio',
            'projects' => ['Project 1', 'Project 2']
        ]);
    }

    public function api(): void
    {
        $this->json(['status' => 'success']);
    }
}</code></pre>
                    </section>

                    <!-- 6. Pages & Layouts -->
                    <section id="templates" class="doc-card">
                        <h2>6. <code>.srin.php</code> Templates &amp; <code>@Connect</code> Layouts</h2>
                        <div class="highlight-box">
                            <strong>Strict Template Format:</strong> All views, layouts, and partials in Srin Engine <strong>must use the <code>.srin.php</code> extension</strong> (e.g. <code>Pages/portfolio.srin.php</code>). Standard <code>.php</code> files (like <code>Pages/portfolio.php</code>) are intentionally rejected and will not work.
                        </div>

                        <h3>Page Template (e.g. <code>Pages/home.srin.php</code>):</h3>
                        <pre><code>@@Connect('layouts/main')
@@title('Welcome to Our Site')

&lt;section class="hero"&gt;
    &lt;h1&gt;Hello World&lt;/h1&gt;
    &lt;p&gt;This content automatically slots into the master layout.&lt;/p&gt;
&lt;/section&gt;</code></pre>

                        <h3>Master Layout (e.g. <code>Pages/layouts/main.srin.php</code>):</h3>
                        <pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;title&gt;&lt;?= $title ?? config('name') ?&gt;&lt;/title&gt;
    &lt;link rel="stylesheet" href="{{assets('/css/style.css')}}"&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;main class="container"&gt;
        @@flash('success')
        @@flash('error')
        @@content
    &lt;/main&gt;
    &lt;script src="{{assets('/js/main.js')}}"&gt;&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>

                        <h3>Directives Summary:</h3>
                        <ul class="feature-bullets">
                            <li><code>{{assets('/css/...')}}</code>: Direct asset URL linking to <code>assets/</code> folder.</li>
                            <li><code>@@Connect('layouts/main')</code>: Connects the page to a master layout.</li>
                            <li><code>@@title('Your Title')</code>: Sets the page title variable.</li>
                            <li><code>@@content</code>: Where the page HTML is injected inside the layout.</li>
                            <li><code>@@include('partials/name')</code>: Reusable component include from <code>Pages/partials/</code>.</li>
                            <li><code>@@csrf</code>: Generates hidden CSRF input field for forms.</li>
                            <li><code>@@flash('success')</code>: Displays session flash notifications.</li>
                        </ul>
                    </section>

                    <!-- 7. Defining Routes -->
                    <section id="routing" class="doc-card">
                        <h2>7. Defining Routes &amp; Named Links (<code>Routes/web.php</code>)</h2>
                        <p>Register all link paths in <code>Routes/web.php</code> and assign names using <code>->name('name')</code>:</p>
                        <pre><code>use Core\Router;

// Define named routes:
Router::get('/', 'HomeController@index')->name('home');
Router::get('/about', 'AboutController@index')->name('about');
Router::get('/services', 'ServicesController@index')->name('services');
Router::get('/contact', 'ContactController@index')->name('contact');
Router::post('/contact', 'ContactController@sendMail')->name('contact.send');

// Route with dynamic parameters:
Router::get('/portfolio/{slug}', 'PortfolioController@show')->name('portfolio.show');</code></pre>

                        <h3>Linking to Named Routes in Templates:</h3>
                        <p>In any <code>.srin.php</code> template, generate links easily using <code>@{{route('name')}}</code>:</p>
                        <pre><code>&lt;a href="@{{route('home')}}"&gt;Home&lt;/a&gt;
&lt;a href="@{{route('about')}}"&gt;About Us&lt;/a&gt;
&lt;a href="@{{route('contact')}}"&gt;Contact&lt;/a&gt;

&lt;!-- With Route Parameters: --&gt;
&lt;a href="@{{route('portfolio.show', ['slug' => 'branding-project'])}}"&gt;View Project&lt;/a&gt;</code></pre>

                        <h3>Using in Controllers or PHP:</h3>
                        <pre><code>// In Controllers:
\$this-&gt;redirectToRoute('contact');
\$url = \$this-&gt;route('about');

// In Plain PHP:
echo route('home');</code></pre>
                    </section>

                    <!-- 8. Mail Transfer -->
                    <section id="mail-transfer" class="doc-card">
                        <h2>8. Mail Transfer Feature</h2>
                        <p>The system provides a ready-to-use mailer in <code>Core\Mailer</code> with three drivers configurable in <code>Config/mail.php</code>:</p>

                        <ul class="feature-bullets">
                            <li><strong><code>log</code> (Default for Dev):</strong> Dispatched emails are safely logged to <code>Others/logs/mail.log</code> without requiring an external SMTP server.</li>
                            <li><strong><code>smtp</code>:</strong> Uses PHPMailer (located in <code>Others/</code>) with full TLS/SSL authentication.</li>
                            <li><strong><code>mail</code>:</strong> Uses PHP's native <code>mail()</code> function.</li>
                        </ul>

                        <h3>Dispatching an Email in PHP:</h3>
                        <pre><code>use Core\Mailer;

Mailer::send([
    'to'         =&gt; 'admin@example.com',
    'subject'    =&gt; 'New Contact Inquiry',
    'body'       =&gt; '&lt;h2&gt;Hello&lt;/h2&gt;&lt;p&gt;You received a message...&lt;/p&gt;',
    'is_html'    =&gt; true,
    'reply_to'   =&gt; 'customer@example.com',
    'reply_name' =&gt; 'Customer Name',
]);</code></pre>
                    </section>

                    <!-- 9. Composer Support & Automated Setup -->
                    <section id="composer" class="doc-card">
                        <h2>9. Composer Support &amp; Automated Download</h2>
                        <p>Srin is designed to be completely installable via Composer. Anyone on a new computer can run a single command to automatically download the entire engine, directory layout, and dependencies:</p>

                        <pre><code># Download and setup Srin on any new computer:
composer create-project arindammakar/srin my-project</code></pre>

                        <p>Vendor packages are configured to install into <code>Others/</code> instead of <code>vendor/</code>, keeping your project root clean:</p>
                        <pre><code>composer require &lt;package-name&gt;</code></pre>
                        <p>All classes and packages installed in <code>Others/</code> are automatically autoloaded via <code>Others/autoload.php</code>.</p>
                    </section>

                    <!-- 10. Custom Error Pages -->
                    <section id="error-pages" class="doc-card">
                        <h2>10. Custom Error Pages (<code>Pages/errors/</code>)</h2>
                        <p>Srin automatically detects and sets your error page design by matching the HTTP error number with any template in <code>Pages/errors/{code}.srin.php</code>.</p>

                        <div class="highlight-box">
                            💡 <strong>Zero Configuration:</strong> Simply create a file named with the error number (e.g. <code>Pages/errors/404.srin.php</code> or <code>Pages/errors/500.srin.php</code>). Srin automatically routes that error status code to your custom design!
                        </div>

                        <h3>Create Error Pages via CLI:</h3>
                        <pre><code># Create custom 404 Not Found page
php srin make:error 404

# Create custom 500 Server Error page
php srin make:error 500

# Create custom 403 Forbidden page
php srin make:error 403</code></pre>

                        <h3>How Automatic Error Routing Works:</h3>
                        <ul class="feature-bullets">
                            <li><strong>404 Not Found:</strong> Whenever an unmatched URL is requested, Srin automatically checks for <code>Pages/errors/404.srin.php</code> and displays your custom design.</li>
                            <li><strong>500 Server Errors:</strong> When an uncaught exception occurs in production (<code>debug => false</code> in <code>Config/app.php</code>), Srin automatically displays <code>Pages/errors/500.srin.php</code>.</li>
                            <li><strong>Explicit <code>abort(\$code)</code>:</strong> Trigger any custom error page anytime from controllers or routes:</li>
                        </ul>

                        <pre><code>// In Controllers:
if (!\$post) {
    \$this-&gt;abort(404, 'Post not found');
}

// In Plain PHP / Closures:
if (!isAuthorized()) {
    abort(403, 'You do not have access to this area');
}</code></pre>

                        <h3>Available Variables Inside Error Templates:</h3>
                        <div class="helpers-table-wrapper">
                            <table class="helpers-table">
                                <thead>
                                    <tr>
                                        <th>Variable</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>\$code</code></td>
                                        <td>The integer HTTP status code (e.g. <code>404</code>, <code>500</code>, <code>403</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$statusText</code></td>
                                        <td>Standard HTTP phrase (e.g. <code>'Page Not Found'</code>, <code>'Forbidden'</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$message</code></td>
                                        <td>Custom error explanation message passed into <code>abort()</code> or system default.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$title</code></td>
                                        <td>Pre-formatted page title (e.g. <code>'404 - Page Not Found'</code>).</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- 11. Enterprise Security & Downloads -->
                    <section id="security" class="doc-card">
                        <h2>11. Enterprise Security &amp; Downloadable Files</h2>
                        <p>Srin enforces strict directory isolation and defense-in-depth security inspired by Laravel to protect your core code and sensitive assets.</p>

                        <div class="highlight-box">
                            🔒 <strong>Strict Direct-Access Lock:</strong> All internal directories (<code>Controller/</code>, <code>Core/</code>, <code>Config/</code>, <code>Pages/</code>, <code>Routes/</code>, <code>Others/</code>) and sensitive files (<code>.env</code>, <code>composer.json</code>, <code>srin</code>) are completely blocked from web browsers, immediately routing to the 404 error page.
                        </div>

                        <h3>Downloadable Files Rules:</h3>
                        <ul class="feature-bullets">
                            <li><strong>Must Reside in <code>assets/</code>:</strong> All downloadable files (PDFs, ZIPs, documents, brochures, catalogs) must be stored inside the <code>assets/</code> directory (e.g. <code>assets/downloads/</code>).</li>
                            <li><strong><code>Pages/</code> Cannot Be Used for Downloads:</strong> Attempting to store or access downloadable files inside <code>Pages/</code> will <strong>not work</strong>. The <code>Pages/</code> directory is strictly reserved for server-rendered view templates and is completely blocked from direct HTTP file retrieval.</li>
                        </ul>

                        <h3>Linking to Downloads in Templates:</h3>
                        <pre><code>&lt;!-- Direct download link from assets/downloads/ --&gt;
&lt;a href="@{{assets('/downloads/brochure.pdf')}}" download&gt;Download Brochure (PDF)&lt;/a&gt;
&lt;a href="@{{assets('/downloads/portfolio.zip')}}" download&gt;Download Portfolio (ZIP)&lt;/a&gt;</code></pre>

                        <h3>Secure Programmatic Download via Controller / Route:</h3>
                        <pre><code>// In a Controller:
return \$this-&gt;download('downloads/brochure.pdf', 'Company-Brochure.pdf');

// In a Route Closure:
Router::get('/download/catalog', function() {
    download('downloads/catalog.pdf', 'Official-Catalog.pdf');
});</code></pre>

                        <h3>Laravel-Grade Security Features:</h3>
                        <ul class="feature-bullets">
                            <li><strong>Single Entry Point:</strong> All HTTP requests funnel strictly through <code>index.php</code>.</li>
                            <li><strong>Automatic Security Headers:</strong> Every response emits <code>X-Frame-Options: SAMEORIGIN</code>, <code>X-Content-Type-Options: nosniff</code>, <code>X-XSS-Protection</code>, and <code>Referrer-Policy</code>.</li>
                            <li><strong>Session Hardening:</strong> Sessions are pre-configured with <code>cookie_httponly</code>, <code>use_only_cookies</code>, and HTTPS detection.</li>
                            <li><strong>Path Traversal Defense:</strong> Resolvers use <code>realpath()</code> checks to prevent <code>../</code> directory escape attacks.</li>
                            <li><strong>CSRF Token System:</strong> Form submissions are protected using <code>@csrf</code> and <code>csrf_verify()</code>.</li>
                        </ul>
                    </section>

                    <!-- 12. Global Helpers -->
                    <section id="helpers" class="doc-card">
                        <h2>12. Useful Global Helpers</h2>
                        <div class="helpers-table-wrapper">
                            <table class="helpers-table">
                                <thead>
                                    <tr>
                                        <th>Helper Function</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>assets('/css/style.css')</code></td>
                                        <td>Direct link to assets folder (or use <code>/assets/...</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><code>download(\$path, \$name)</code></td>
                                        <td>Securely stream a download from <code>assets/</code> (non-asset folders blocked).</td>
                                    </tr>
                                    <tr>
                                        <td><code>view('page', \$data)</code></td>
                                        <td>Render a <code>.srin.php</code> template from <code>Pages/</code>.</td>
                                    </tr>
                                    <tr>
                                        <td><code>abort(\$code, \$message)</code></td>
                                        <td>Abort request and automatically render <code>Pages/errors/{code}.srin.php</code>.</td>
                                    </tr>
                                    <tr>
                                        <td><code>redirect('/path')</code></td>
                                        <td>Redirect the browser to another URL.</td>
                                    </tr>
                                    <tr>
                                        <td><code>json(\$data, \$status)</code></td>
                                        <td>Output a JSON response and exit.</td>
                                    </tr>
                                    <tr>
                                        <td><code>route('name', \$params)</code></td>
                                        <td>Generate URL for a named route (or use <code>@{{route('name')}}</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><code>url('/path')</code></td>
                                        <td>Generate an application link with base URL.</td>
                                    </tr>
                                    <tr>
                                        <td><code>csrf_field()</code></td>
                                        <td>Render the hidden CSRF security input token.</td>
                                    </tr>
                                    <tr>
                                        <td><code>flash('key', 'msg')</code></td>
                                        <td>Set or display session flash messages across requests.</td>
                                    </tr>
                                    <tr>
                                        <td><code>old('field')</code></td>
                                        <td>Retrieve previous input value after form validation error.</td>
                                    </tr>
                                    <tr>
                                        <td><code>config('name')</code></td>
                                        <td>Retrieve config values from <code>Config/app.php</code> or dot-notation.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container footer-container">
            <div class="footer-info">
                <p>&copy; <?= date('Y') ?> Srin Engine. Designed and Developed by <strong><a href="https://github.com/deadxfire" target="_blank" style="color: inherit; text-decoration: underline;">Arindam Makar</a></strong>.</p>
                <p class="footer-subtext">Fast, Clean, Lightweight &bull; Zero Bloat &bull; <a href="https://github.com/deadxfire" target="_blank" style="color: inherit;">https://github.com/deadxfire</a></p>
            </div>
            <div class="footer-links">
                <a href="#quickstart">Quick Start</a>
                <a href="#assets-guide">Assets Guide</a>
                <a href="#error-pages">Error Pages</a>
                <a href="https://github.com/deadxfire" target="_blank">GitHub</a>
                <a href="/api/status">API Status</a>
            </div>
        </div>
    </footer>

    <script>
        // Mobile navigation toggle
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');
        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', () => {
                navLinks.classList.toggle('active');
            });
        }
    </script>
</body>
</html>
