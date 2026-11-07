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
                <a href="{{route('docs')}}" style="color: #0284c7; font-weight: 700;">Documentation &rarr;</a>
                <a href="#quickstart">Quick Start</a>
                <a href="#assets-guide">Assets</a>
                <a href="#srin-cli">CLI</a>
                <a href="#templates">Shortcodes</a>
                <a href="#database-models">Models &amp; Pagination</a>
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
                    <a href="{{route('docs')}}" class="btn btn-primary" style="background: linear-gradient(135deg, #0284c7, #4f46e5); color: #fff; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);">Multipage Documentation &rarr;</a>
                    <a href="#quickstart" class="btn btn-outline">Quick Start Guide</a>
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
                            <li><a href="#database-models">5. Models, Migrations &amp; Pagination</a></li>
                            <li><a href="#controllers">6. Controllers</a></li>
                            <li><a href="#templates">7. Shortcodes &amp; Templates</a></li>
                            <li><a href="#routing">8. Defining Routes</a></li>
                            <li><a href="#mail-transfer">9. Mail Transfer</a></li>
                            <li><a href="#composer">10. Composer &amp; Download</a></li>
                            <li><a href="#error-pages">11. Custom Error Pages</a></li>
                            <li><a href="#security">12. Security &amp; Downloads</a></li>
                            <li><a href="#helpers">13. Global Helpers</a></li>
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
                        <p>Simply drop any image or folder inside <code>assets/images/</code> and link it directly — <code>{{assets('logo.png')}}</code> automatically maps image filenames to the images directory without needing the folder location path, generating the complete direct URL:</p>
                        <pre><code>&lt;img src="{{assets('logo.png')}}" alt="Site Logo"&gt;
&lt;img src="{{assets('/images/portfolio/project-1.jpg')}}" alt="Project"&gt;</code></pre>

                        <h3>Adding Custom Asset Folders</h3>
                        <p>You can add any folder into <code>assets/</code> (e.g. <code>assets/fonts/</code>, <code>assets/icons/</code>, <code>assets/videos/</code>). They link instantly:</p>
                        <pre><code>&lt;link rel="preload" href="{{assets('/fonts/inter.woff2')}}" as="font"&gt;
&lt;img src="{{assets('/icons/star.svg')}}" alt="Star"&gt;</code></pre>

                        <div class="highlight-box">
                            <strong>Note on Subfolders &amp; Production Servers:</strong>
                            <code>{{assets('/...')}}</code> automatically generates the full absolute URL (e.g. <code>https://mysite.com/assets/...</code>) and detects whether your site is on a root domain (<code>https://mysite.com</code>) or inside a server subfolder (<code>https://mysite.com/subfolder/</code>).
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
                                <strong>Model/</strong>
                                <p>ActiveRecord database models live here. Extend <code>Core\Model</code> to access automatic plural tables, fillable mass-assignment protection, and fluent querying.</p>
                            </div>
                            <div class="structure-item">
                                <strong>database/migrations/</strong>
                                <p>All schema migration blueprints live here. Define schema with <code>Core\Blueprint</code> and execute via <code>php srin migrate</code>.</p>
                            </div>
                            <div class="structure-item">
                                <strong>Core/</strong>
                                <p>Srin engine core: <code>App</code>, <code>Model</code>, <code>Database</code>, <code>QueryBuilder</code>, <code>Schema</code>, <code>Blueprint</code>, <code>Migrator</code>, <code>Router</code>, <code>Request</code>, <code>Response</code>, <code>View</code>, <code>Mailer</code>, and <code>Helper</code>.</p>
                            </div>
                            <div class="structure-item">
                                <strong>Config/</strong>
                                <p>Configuration options (<code>app.php</code> as main config, <code>database.php</code> for PDO SQLite/MySQL/PostgreSQL, <code>mail.php</code> for SMTP and mail drivers).</p>
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
                        <p>Use the built-in Srin CLI tool (<code>srin</code> or <code>php srin</code>) to generate models, migrations, controllers, pages, layouts, and error pages:</p>

                        <h3>Generate Model with Migration</h3>
                        <pre><code>php srin make:model Post -m</code></pre>
                        <p>Generates <code>Model/Post.php</code> and a matching migration file in <code>database/migrations/</code>.</p>

                        <h3>Database Migrations</h3>
                        <pre><code>php srin migrate           # Run pending migrations
php srin migrate:status    # View status of migrations
php srin migrate:rollback  # Rollback last migration batch
php srin migrate:fresh     # Drop all tables and re-run all migrations</code></pre>

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

                        <h3>One-Command System Upgrade (<code>php srin upgrade</code>)</h3>
                        <p>Automatically checks for official updates from GitHub (<code>deadxfire/srin</code>) and upgrades the default core framework files with overwrite, while strictly preserving your project code:</p>
                        <pre><code># Check and upgrade default system code to the latest release:
php srin upgrade

# Force overwrite default system files with latest repository code:
php srin upgrade --force

# Check if an update is available without modifying any files:
php srin upgrade --check</code></pre>

                        <div class="highlight-box">
                            🔒 <strong>Strict User Data Preservation Guarantee:</strong>
                            <code>php srin upgrade</code> safely overwrites default framework components (<code>Core/</code>, <code>Config/</code>, <code>Others/</code>, <code>srin</code>, <code>srin.php</code>, <code>index.php</code>, <code>Pages/welcome.srin.php</code>, <code>README.md</code>), while <strong>never</strong> touching your custom project data:
                            <ul style="margin: 8px 0 0 20px;">
                                <li><code>.env</code> (Secrets, database credentials &amp; API keys)</li>
                                <li><code>Model/</code> (All custom database models)</li>
                                <li><code>Controller/</code> (All custom application controllers)</li>
                                <li><code>database/</code> (Migrations &amp; SQLite database files)</li>
                                <li><code>assets/</code> (Custom CSS, JavaScript, images &amp; fonts)</li>
                                <li><code>Pages/</code> (Custom templates, layouts &amp; partials)</li>
                            </ul>
                        </div>
                    </section>

                    <!-- 5. Database Models & Migrations -->
                    <section id="database-models" class="doc-card">
                        <h2>5. Database Models &amp; Migrations (ActiveRecord)</h2>
                        <p>Srin Engine features an expressive ActiveRecord ORM and a multi-dialect schema migration engine. Zero-setup <strong>SQLite</strong> is pre-configured by default, with complete support for <strong>MySQL</strong> and <strong>PostgreSQL</strong> via <code>Config/database.php</code>.</p>

                        <h3>1. Generate a Model &amp; Migration:</h3>
                        <pre><code># Generate both the model and migration blueprint together:
php srin make:model Article -m

# Or create a standalone migration:
php srin make:migration create_articles_table</code></pre>

                        <h3>2. Detailed Migration Code Sample (<code>database/migrations/YYYY_MM_DD_HHMMSS_create_articles_table.php</code>):</h3>
                        <p>Migrations extend <code>Core\Migration</code> and use <code>Core\Schema</code> with <code>Core\Blueprint</code>. Here is a production-grade schema demonstrating all column types, defaults, and modifiers:</p>
                        
                        <pre><code>&lt;?php

use Core\Migration;
use Core\Schema;
use Core\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint \$table) {
            // Auto-incrementing primary key ID
            \$table-&gt;id();

            // String and unique column
            \$table-&gt;string('title', 255);
            \$table-&gt;string('slug')-&gt;unique();

            // Text columns (nullable and required)
            \$table-&gt;text('summary')-&gt;nullable();
            \$table-&gt;text('content');

            // Foreign keys and numbers with default values
            \$table-&gt;integer('category_id')-&gt;nullable();
            \$table-&gt;integer('views')-&gt;default(0);
            \$table-&gt;decimal('price', 10, 2)-&gt;default(0.00);

            // Boolean flags
            \$table-&gt;boolean('is_published')-&gt;default(true);
            \$table-&gt;boolean('is_featured')-&gt;default(false);

            // Status string
            \$table-&gt;string('status', 50)-&gt;default('draft');

            // Dates &amp; Timestamps
            \$table-&gt;date('published_on')-&gt;nullable();
            \$table-&gt;dateTime('scheduled_at')-&gt;nullable();

            // Automatic created_at &amp; updated_at timestamps
            \$table-&gt;timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};</code></pre>

                        <h3>3. Schema Blueprint Column Types Reference:</h3>
                        <div class="helpers-table-wrapper">
                            <table class="helpers-table">
                                <thead>
                                    <tr>
                                        <th>Blueprint Method</th>
                                        <th>SQL Definition</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>\$table-&gt;id('id')</code></td>
                                        <td><code>INTEGER PRIMARY KEY AUTO_INCREMENT</code></td>
                                        <td>Primary key ID column.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;string('title', 255)</code></td>
                                        <td><code>VARCHAR(255)</code></td>
                                        <td>Standard string column.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;text('content')</code></td>
                                        <td><code>TEXT</code></td>
                                        <td>Multiline text block.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;integer('views')</code></td>
                                        <td><code>INTEGER</code></td>
                                        <td>Standard integer column.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;bigInteger('amount')</code></td>
                                        <td><code>BIGINT</code></td>
                                        <td>Large numeric identifier.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;boolean('is_active')</code></td>
                                        <td><code>TINYINT(1) / BOOLEAN</code></td>
                                        <td>True/false boolean flag.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;decimal('price', 10, 2)</code></td>
                                        <td><code>DECIMAL(10, 2)</code></td>
                                        <td>Fixed-point numbers for currency.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;float('rating')</code></td>
                                        <td><code>FLOAT</code></td>
                                        <td>Floating point numeric values.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;date('birth_date')</code></td>
                                        <td><code>DATE</code></td>
                                        <td>Date without time (YYYY-MM-DD).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;dateTime('event_at')</code></td>
                                        <td><code>DATETIME</code></td>
                                        <td>Date with time.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$table-&gt;timestamps()</code></td>
                                        <td><code>created_at, updated_at</code></td>
                                        <td>Automatic timestamp management.</td>
                                    </tr>
                                    <tr>
                                        <td><code>-&gt;nullable()</code>, <code>-&gt;default(\$val)</code>, <code>-&gt;unique()</code></td>
                                        <td>Column Modifiers</td>
                                        <td>Can be chained on any column type.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>4. Migration Management CLI Commands:</h3>
                        <pre><code># Run all pending migrations:
php srin migrate

# Check status of all migrations:
php srin migrate:status

# Rollback the last migration batch:
php srin migrate:rollback

# Wipe the database and re-run all migrations:
php srin migrate:fresh</code></pre>

                        <h3>5. Corresponding Model Definition (<code>Model/Article.php</code>):</h3>
                        <pre><code>&lt;?php

namespace Model;

use Core\Model;

class Article extends Model
{
    // Custom table name (defaults to 'articles' automatically)
    protected static string \$table = 'articles';

    // Mass-assignment whitelist protection
    protected array \$fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'category_id',
        'price',
        'is_published',
        'is_featured',
        'status',
        'published_on',
    ];

    // Attributes guarded from mass-assignment
    protected array \$guarded = ['id', 'views'];
}</code></pre>

                        <h3>6. Model Relationships &amp; Eager Loading (<code>hasMany</code>, <code>belongsTo</code>, <code>hasOne</code>):</h3>
                        <p>Models support expressive relationships with built-in lazy loading, batch eager loading (defeating the N+1 database problem), and automatic nested serialization:</p>

                        <pre><code>// Defining Relationships in Models:
class User extends Model
{
    public function posts()
    {
        return \$this-&gt;hasMany(Post::class); // Has-Many
    }

    public function profile()
    {
        return \$this-&gt;hasOne(Profile::class); // Has-One
    }
}

class Post extends Model
{
    public function user()
    {
        return \$this-&gt;belongsTo(User::class); // Belongs-To Inverse
    }
}

// 1. Create related records through relations (automatically sets foreign key):
\$user = User::find(1);
\$post = \$user-&gt;posts()-&gt;create(['title' =&gt; 'First Post', 'content' =&gt; 'Hello!']);

// 2. Dynamic property access (Lazy-loaded and cached in memory):
foreach (\$user-&gt;posts as \$p) {
    echo \$p-&gt;title;
}
echo \$post-&gt;user-&gt;name;

// 3. Batch Eager Loading (Solves the N+1 query problem):
\$users = User::with('posts', 'profile')-&gt;get();

// 4. Automatic nested JSON/Array serialization:
\$data = \$users[0]-&gt;toArray(); // Includes 'posts' and 'profile' arrays</code></pre>

                        <h3>7. Fluent Database Pagination: <code>Model::paginate(10)</code></h3>
                        <p>Easily paginate large datasets with a single method. Returns an instance of <code>Core\Paginator</code> containing records, total counts, page metadata, query string preservation, and responsive HTML links:</p>

                        <pre><code>// In a Controller:
public function index(): string
{
    // Paginate 10 published articles per page (auto-preserves GET search/filter parameters):
    \$articles = Article::where('is_published', true)
        -&gt;latest()
        -&gt;paginate(10);

    return \$this-&gt;view('articles/index', [
        'articles' =&gt; \$articles
    ]);
}

// In your .srin.php template:
@@foreach(\$articles as \$article)
    &lt;article class="article-card"&gt;
        &lt;h2&gt;@{{ \$article-&gt;title }}&lt;/h2&gt;
        &lt;p&gt;@{{ \$article-&gt;summary }}&lt;/p&gt;
    &lt;/article&gt;
@@endforeach

&lt;!-- Render responsive pagination controls (prev, numbered pills, next): --&gt;
@@paginate(\$articles)
&lt;!-- Or render raw links: --&gt;
@{!! \$articles-&gt;links() !!}</code></pre>

                        <h3>8. Database Options &amp; Multi-Driver Connections (<code>Config/database.php</code>):</h3>
                        <p>Srin Engine supports <strong>SQLite</strong>, <strong>MySQL / MariaDB</strong>, and <strong>PostgreSQL</strong> natively. Configure your default connection in <code>.env</code>:</p>

                        <div class="helpers-table-wrapper">
                            <table class="helpers-table">
                                <thead>
                                    <tr>
                                        <th>Driver</th>
                                        <th>Key .env Settings</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>SQLite</strong> (Default)</td>
                                        <td><code>DB_CONNECTION=sqlite</code><br><code>DB_DATABASE="database/database.sqlite"</code></td>
                                        <td>Zero-configuration, serverless embedded database. Auto-creates SQLite file and directory if missing. Supports in-memory database (<code>:memory:</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><strong>MySQL / MariaDB</strong></td>
                                        <td><code>DB_CONNECTION=mysql</code><br><code>DB_HOST=127.0.0.1</code><br><code>DB_PORT=3306</code><br><code>DB_DATABASE=srin_app</code><br><code>DB_USERNAME=root</code><br><code>DB_PASSWORD=secret</code></td>
                                        <td>Enterprise relational database with UTF8MB4 charset, strict mode, auto-reconnect, and PDO prepared statement protection.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>PostgreSQL</strong></td>
                                        <td><code>DB_CONNECTION=pgsql</code><br><code>DB_HOST=127.0.0.1</code><br><code>DB_PORT=5432</code><br><code>DB_DATABASE=srin_app</code><br><code>DB_USERNAME=postgres</code></td>
                                        <td>High-concurrency PostgreSQL support with custom search paths (<code>public</code> schema) and SSL negotiation modes.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>9. Direct Database Gateway: <code>db('table')</code> &amp; Raw SQL Operations</h3>
                        <p>You don't always need a model. Use the standalone <code>db('table')</code> helper or <code>Database</code> class for instant queries, transactions, and scalar queries anywhere in your application:</p>

                        <pre><code>// 1. Fluent table query without a Model:
\$activeUsers = db('users')-&gt;where('status', 'active')-&gt;latest()-&gt;get();

// 2. Insert records:
\$newId = db('logs')-&gt;insert(['action' =&gt; 'login', 'ip' =&gt; Request::ip()]);

// 3. Raw SELECT query with parameter binding:
\$rows = db()-&gt;select("SELECT * FROM products WHERE price &lt; ? AND stock &gt; ?", [100, 0]);

// 4. Single row (selectOne) or single value (scalar):
\$user = db()-&gt;selectOne("SELECT * FROM users WHERE email = ?", [\$email]);
\$totalViews = db()-&gt;scalar("SELECT SUM(views) FROM articles WHERE is_published = 1");

// 5. ACID Database Transactions:
db()-&gt;transaction(function (\$pdo) {
    db('accounts')-&gt;where('id', 1)-&gt;decrement('balance', 50);
    db('accounts')-&gt;where('id', 2)-&gt;increment('balance', 50);
});</code></pre>

                        <h3>10. Standalone QueryBuilder Superpowers:</h3>
                        <p>Srin Engine provides unique, developer-friendly methods that eliminate repetitive <code>if</code> checks and boilerplate:</p>

                        <div class="helpers-table-wrapper">
                            <table class="helpers-table">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Example</th>
                                        <th>What It Does</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>-&gt;search(\$cols, \$keyword)</code></td>
                                        <td><code>Post::search(['title', 'content'], \$q)-&gt;get()</code></td>
                                        <td>Searches across multiple columns with SQL LIKE. Automatically skips if keyword is empty/null!</td>
                                    </tr>
                                    <tr>
                                        <td><code>-&gt;filter(\$assocArray)</code></td>
                                        <td><code>Post::filter(['category' =&gt; \$cat, 'author' =&gt; \$id])-&gt;get()</code></td>
                                        <td>Applies WHERE/WHERE IN filters. Automatically ignores empty string or null values!</td>
                                    </tr>
                                    <tr>
                                        <td><code>-&gt;when(\$value, \$callback)</code></td>
                                        <td><code>Post::when(\$featured, fn(\$q) =&gt; \$q-&gt;where('featured', 1))</code></td>
                                        <td>Conditionally applies clauses when value is truthy without breaking method chains.</td>
                                    </tr>
                                    <tr>
                                        <td><code>-&gt;distinctValues(\$col)</code></td>
                                        <td><code>Post::where('published', 1)-&gt;distinctValues('category')</code></td>
                                        <td>Extracts unique column values directly from SQL. Fast, clean, zero PHP memory waste!</td>
                                    </tr>
                                    <tr>
                                        <td><code>-&gt;pluck(\$col, \$key)</code></td>
                                        <td><code>User::pluck('name', 'id')</code></td>
                                        <td>Extracts an array of column values, optionally keyed by another column.</td>
                                    </tr>
                                    <tr>
                                        <td><code>-&gt;increment(\$col, \$amt)</code></td>
                                        <td><code>Post::where('id', 5)-&gt;increment('views', 1)</code></td>
                                        <td>Atomically increments numeric column values in SQL.</td>
                                    </tr>
                                    <tr>
                                        <td><code>-&gt;decrement(\$col, \$amt)</code></td>
                                        <td><code>Product::where('id', 2)-&gt;decrement('stock', 1)</code></td>
                                        <td>Atomically decrements numeric column values in SQL.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- 6. Controllers -->
                    <section id="controllers" class="doc-card">
                        <h2>6. Writing Controllers (Standalone Srin Style)</h2>
                        <p>Controllers reside in <code>Controller/</code> and extend <code>Core\BaseController</code>. Unlike traditional frameworks with heavy boilerplate and verbose syntax, Srin Engine provides an ultra-clean, expressive, standalone developer experience designed to write 3x less code while maintaining bulletproof security.</p>

                        <h3>The Srin Standalone Style vs Traditional Verbose Style:</h3>
                        <p>Compare the old verbose way with Srin's clean, intuitive standalone style:</p>

                        <pre><code>// Traditional Verbose Style (30+ lines of repetitive code):
public function index(): string
{
    \$search = trim((string) \$this-&gt;input('q', ''));
    \$category = trim((string) \$this-&gt;input('category', ''));

    \$query = Post::where('is_published', true)-&gt;with('comments');
    if (\$category !== '') {
        \$query-&gt;where('category', \$category);
    }
    if (\$search !== '') {
        \$query-&gt;where('title', 'LIKE', "%{\$search}%");
    }
    \$posts = \$query-&gt;latest()-&gt;paginate(4);

    \$allPosts = Post::where('is_published', true)-&gt;get();
    \$categories = array_values(array_unique(array_filter(array_map(fn(\$p) =&gt; \$p-&gt;category, \$allPosts))));

    return \$this-&gt;render('blog/index', [
        'posts' =&gt; \$posts,
        'categories' =&gt; \$categories,
    ]);
}

// ✨ Srin Engine Standalone Style (Clean, elegant, 12 lines):
public function index(): string
{
    \$category = \$this-&gt;get('category');
    \$search   = \$this-&gt;get('q');

    \$posts = Post::where('is_published', true)
        -&gt;with('comments')
        -&gt;filter(['category' =&gt; \$category])
        -&gt;search('title', \$search)
        -&gt;latest()
        -&gt;paginate(4);

    return \$this-&gt;view('blog/index', [
        'title'          =&gt; 'Explore Srin Engine Blog',
        'posts'          =&gt; \$posts,
        'categories'     =&gt; Post::where('is_published', true)-&gt;distinctValues('category'),
        'activeCategory' =&gt; \$category,
        'searchQuery'    =&gt; \$search,
        'totalPosts'     =&gt; Post::where('is_published', true)-&gt;count(),
    ]);
}</code></pre>

                        <h3>Detailed Production Controller Code Sample (<code>Controller/ArticleController.php</code>):</h3>
                        <pre><code>&lt;?php

namespace Controller;

use Core\BaseController;
use Model\Article;

class ArticleController extends BaseController
{
    /**
     * Display a listing of articles.
     */
    public function index(): string
    {
        // Query models using the fluent, SQL-injection-safe query builder
        \$articles = Article::where('is_published', true)
            -&gt;where('status', 'published')
            -&gt;latest()
            -&gt;get();

        // Render Pages/articles/index.srin.php
        return \$this-&gt;render('articles/index', [
            'title'    =&gt; 'Latest Articles',
            'articles' =&gt; \$articles,
        ]);
    }

    /**
     * Display a single article by slug.
     */
    public function show(string \$slug): string
    {
        \$article = Article::where('slug', \$slug)-&gt;first();

        // Automatically abort with custom 404 error page if missing
        if (!\$article) {
            \$this-&gt;abort(404, 'Article not found');
        }

        // Increment views counter
        \$article-&gt;views += 1;
        \$article-&gt;save();

        return \$this-&gt;render('articles/show', [
            'title'   =&gt; \$article-&gt;title,
            'article' =&gt; \$article,
        ]);
    }

    /**
     * Show form to create a new article.
     */
    public function create(): string
    {
        return \$this-&gt;render('articles/create', [
            'title' =&gt; 'Create New Article',
        ]);
    }

    /**
     * Store a newly created article with full security defenses.
     */
    public function store()
    {
        // 1. Throttle requests to block DoS and brute-force flooding
        \$this-&gt;rateLimit('article-create', 5, 60);

        // 2. Defeat CSRF attacks (aborts with HTTP 419 on invalid token)
        \$this-&gt;validateCsrf();

        // 3. Defeat automated spam bots via invisible honeypot trap
        \$this-&gt;validateHoneypot();

        // 4. Whitelist allowed inputs to prevent mass-assignment attacks
        \$inputs = \$this-&gt;onlyInputs([
            'title', 'slug', 'summary', 'content', 'price', 'is_published'
        ]);

        // 5. Server-side data validation
        \$errors = \$this-&gt;validate(\$inputs, [
            'title'   =&gt; 'required|min:5|max:200',
            'slug'    =&gt; 'required|min:3',
            'content' =&gt; 'required|min:20',
        ]);

        // Redirect back with validation error messages if validation fails
        if (!empty(\$errors)) {
            return \$this-&gt;flash('errors', \$errors)-&gt;back();
        }

        // 6. Persist record using ActiveRecord Model
        \$article = Article::create(\$inputs);

        // 7. Redirect to named route with a success flash message
        return \$this-&gt;flash('success', 'Article created successfully!')
            -&gt;redirectToRoute('articles.show', ['slug' =&gt; \$article-&gt;slug]);
    }

    /**
     * Update an existing article.
     */
    public function update(int \$id)
    {
        \$this-&gt;validateCsrf();

        // Find record or automatically render 404 error page
        \$article = Article::findOrFail(\$id);

        \$data = \$this-&gt;onlyInputs(['title', 'summary', 'content', 'status']);
        \$article-&gt;update(\$data);

        return \$this-&gt;flash('success', 'Article updated!')
            -&gt;redirectToRoute('articles.show', ['slug' =&gt; \$article-&gt;slug]);
    }

    /**
     * Delete an article from database.
     */
    public function destroy(int \$id)
    {
        \$this-&gt;validateCsrf();

        \$article = Article::findOrFail(\$id);
        \$article-&gt;delete();

        return \$this-&gt;flash('success', 'Article removed.')
            -&gt;redirectToRoute('articles.index');
    }

    /**
     * Return JSON response for API requests.
     */
    public function api(): void
    {
        \$articles = Article::where('is_published', true)-&gt;latest()-&gt;get();

        // JSON response with HTTP 200 OK
        \$this-&gt;json([
            'status' =&gt; 'success',
            'count'  =&gt; count(\$articles),
            'data'   =&gt; \$articles,
        ], 200);
    }

    /**
     * Serve a downloadable file securely strictly from assets/.
     */
    public function export(): void
    {
        // Non-asset directory downloads are blocked with 404 for security
        \$this-&gt;download('downloads/articles-export.csv', 'Articles.csv');
    }
}</code></pre>

                        <h3>Controller Helper Methods Reference:</h3>
                        <div class="helpers-table-wrapper">
                            <table class="helpers-table">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Return Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>\$this-&gt;view(\$view, \$data)</code></td>
                                        <td><code>string</code></td>
                                        <td>Expressive template renderer for <code>Pages/{view}.srin.php</code>.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;render(\$view, \$data)</code></td>
                                        <td><code>string</code></td>
                                        <td>Renders a <code>Pages/{view}.srin.php</code> template with layout.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;get(\$key, \$default)</code></td>
                                        <td><code>string|array</code></td>
                                        <td>Quick GET query parameter fetcher. Auto-trims and sanitizes strings.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;post(\$key, \$default)</code></td>
                                        <td><code>string|array</code></td>
                                        <td>Quick POST body parameter fetcher. Auto-trims and sanitizes strings.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;input(\$key, \$default)</code></td>
                                        <td><code>mixed</code></td>
                                        <td>Retrieves a sanitized input value from GET or POST (auto-trimmed).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;has(\$key)</code> / <code>\$this-&gt;filled(\$key)</code></td>
                                        <td><code>bool</code></td>
                                        <td>Checks if an input parameter exists and is not null or empty string.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;search(\$key, \$default)</code></td>
                                        <td><code>string</code></td>
                                        <td>Retrieves clean search query string (defaults to <code>'q'</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;int(\$key, \$default)</code></td>
                                        <td><code>int</code></td>
                                        <td>Retrieves an input value strictly cast to integer.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;bool(\$key, \$default)</code></td>
                                        <td><code>bool</code></td>
                                        <td>Retrieves an input value strictly cast to boolean.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;onlyInputs(\$keys)</code></td>
                                        <td><code>array</code></td>
                                        <td>Whitelists specific request fields (defeats mass-assignment).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;allInputs()</code></td>
                                        <td><code>array</code></td>
                                        <td>Retrieves all GET and POST inputs sanitized.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;isPost()</code> / <code>\$this-&gt;isGet()</code></td>
                                        <td><code>bool</code></td>
                                        <td>Checks if incoming HTTP request method is POST or GET.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;isAjax()</code></td>
                                        <td><code>bool</code></td>
                                        <td>Checks if request was made via AJAX / Fetch (X-Requested-With).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;validate(\$inputs, \$rules)</code></td>
                                        <td><code>array</code></td>
                                        <td>Validates fields against rules (<code>required</code>, <code>email</code>, <code>min</code>, <code>max</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;validateCsrf()</code></td>
                                        <td><code>void</code></td>
                                        <td>Verifies CSRF token or terminates request with HTTP 419.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;validateHoneypot()</code></td>
                                        <td><code>void</code></td>
                                        <td>Checks invisible bot field and aborts if filled by spam bots.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;rateLimit(\$key, \$max, \$decay)</code></td>
                                        <td><code>void</code></td>
                                        <td>Throttles requests and aborts with HTTP 429 if exceeded.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;flashSuccess(\$message)</code></td>
                                        <td><code>\$this</code></td>
                                        <td>Sets a session success flash message (chainable).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;flashError(\$message)</code></td>
                                        <td><code>\$this</code></td>
                                        <td>Sets a session error flash message (chainable).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;flashInfo(\$message)</code></td>
                                        <td><code>\$this</code></td>
                                        <td>Sets a session info flash message (chainable).</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;flash(\$key, \$message)</code></td>
                                        <td><code>\$this</code></td>
                                        <td>Sets a custom session flash message available on next request.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;redirectToRoute(\$name, \$params)</code></td>
                                        <td><code>void</code></td>
                                        <td>Redirects the browser to a registered route name.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;redirect(\$url)</code></td>
                                        <td><code>void</code></td>
                                        <td>Redirects the browser to an absolute or relative URL.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;back(\$status, \$fallback)</code></td>
                                        <td><code>void</code></td>
                                        <td>Redirects back to previous page or referer with fallback.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;json(\$data, \$status)</code></td>
                                        <td><code>void</code></td>
                                        <td>Sends JSON response with appropriate headers and exits.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;download(\$file, \$name)</code></td>
                                        <td><code>void</code></td>
                                        <td>Streams a file download strictly from <code>assets/</code>.</td>
                                    </tr>
                                    <tr>
                                        <td><code>\$this-&gt;abort(\$code, \$message)</code></td>
                                        <td><code>void</code></td>
                                        <td>Terminates request and displays custom error template from <code>Pages/errors/</code>.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- 7. Pages & Layouts -->
                    <section id="templates" class="doc-card">
                        <h2>7. <code>.srin.php</code> Templates, <code>&#123;&#123; &#125;&#125;</code> Syntax &amp; <code>@Connect</code></h2>
                        <div class="highlight-box">
                            <strong>Strict Template Format:</strong> All views, layouts, and partials in Srin Engine <strong>must use the <code>.srin.php</code> extension</strong> (e.g. <code>Pages/portfolio.srin.php</code>). Standard <code>.php</code> files (like <code>Pages/portfolio.php</code>) are intentionally rejected and will not work.
                        </div>

                        <h3>Running PHP Code via <code>&#123;&#123; &#125;&#125;</code> (Auto-Escaped &amp; XSS-Safe):</h3>
                        <p>Whenever you need to run PHP code or display data inside any <code>.srin.php</code> template, use <code>&#123;&#123; &#125;&#125;</code>. Srin Engine automatically passes all outputs through <code>htmlspecialchars()</code> with <code>ENT_QUOTES</code> for complete defense against XSS:</p>
                        <pre><code>&lt;!-- Variables &amp; Expressions --&gt;
&lt;h1&gt;Welcome, @{{ \$name }}!&lt;/h1&gt;
&lt;p&gt;Copyright &copy; @{{ date('Y') }} @{{ config('name') }}&lt;/p&gt;
&lt;span&gt;Total Items: @{{ count(\$projects) }}&lt;/span&gt;

&lt;!-- Safe Asset &amp; Route Linking --&gt;
&lt;link rel="stylesheet" href="@{{assets('/css/style.css')}}"&gt;
&lt;a href="@{{route('home')}}"&gt;Home&lt;/a&gt;

&lt;!-- Raw HTML (when unescaped output is explicitly intended) --&gt;
&lt;div&gt;@{!! \$trustedHtml !!}&lt;/div&gt;

&lt;!-- Secure Comments (stripped completely from client output) --&gt;
@{{-- This comment is never visible in page source --}}

&lt;!-- Frontend JS Framework Escaping (Vue / Alpine / React) --&gt;
&lt;span&gt;@@{{ clientVar }}&lt;/span&gt; &lt;!-- Renders literally as @{{ clientVar }} --&gt;</code></pre>

                        <h3>Secure Form Directives:</h3>
                        <pre><code>&lt;form action="@{{route('contact.send')}}" method="POST"&gt;
    @@csrf       &lt;!-- Injects CSRF token protection --&gt;
    @@honeypot   &lt;!-- Injects invisible bot trap to block spam bots --&gt;

    &lt;input type="text" name="name" value="@{{ old('name') }}" required&gt;
    &lt;input type="email" name="email" value="@{{ old('email') }}" required&gt;
    &lt;button type="submit"&gt;Send Message&lt;/button&gt;
&lt;/form&gt;</code></pre>

                        <h3>Control Flow Directives:</h3>
                        <pre><code>@@if(count(\$projects) &gt; 0)
    @@foreach(\$projects as \$project)
        &lt;div class="card"&gt;@{{ \$project['title'] }}&lt;/div&gt;
    @@endforeach
@@else
    &lt;p&gt;No projects found.&lt;/p&gt;
@@endif</code></pre>

                        <h3>Master Layout with Named Slots: <code>@@content('name')</code></h3>
                        <p>In any layout (e.g. <code>Pages/layouts/main.srin.php</code>), define named injection slots using <code>@@content('slot_name')</code> alongside the primary <code>@@content</code>:</p>
                        <pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;title&gt;@{{ \$title ?? config('name') }}&lt;/title&gt;
    &lt;link rel="icon" type="image/svg+xml" href="@{{assets('/favicon.svg')}}"&gt;
    &lt;link rel="stylesheet" href="@{{assets('/css/style.css')}}"&gt;
    @@content('styles') &lt;!-- Optional page styles --&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;!-- Header Slot --&gt;
    @@content('header')

    &lt;main class="container"&gt;
        @@flash('success')
        @@flash('error')
        
        &lt;!-- Default Main Page Content --&gt;
        @@content
    &lt;/main&gt;

    &lt;!-- Sidebar with optional fallback HTML --&gt;
    @@content('sidebar', '&lt;aside&gt;Default Sidebar Content&lt;/aside&gt;')

    &lt;!-- Footer Slot --&gt;
    @@content('footer')

    &lt;script src="@{{assets('/js/main.js')}}"&gt;&lt;/script&gt;
    @@content('scripts') &lt;!-- Optional page scripts --&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>

                        <h3>Connecting Content on Pages: <code>@@contentConnect('name')</code> &amp; <code>@@Connect</code></h3>
                        <p>On your page (e.g. <code>Pages/home.srin.php</code>), connect to the layout using <code>@@Connect('layouts/main')</code>, and slot custom sections into your layout using <code>@@contentConnect('name') ... @@endContentConnect</code> (or <code>@@contentCconnect</code>):</p>
                        
                        <pre><code>@@Connect('layouts/main')
@@title('Home Page')

&lt;!-- 1. Connect Custom Header --&gt;
@@contentConnect('header')
&lt;header class="hero-header"&gt;
    @@include('partials.navbar') &lt;!-- Includes Pages/partials/navbar.srin.php --&gt;
    &lt;h1&gt;Welcome to @{{ config('name') }}&lt;/h1&gt;
&lt;/header&gt;
@@endContentConnect

&lt;!-- 2. Default Main Page Content (Automatically slots into @content) --&gt;
&lt;section class="services"&gt;
    &lt;h2&gt;Our Services&lt;/h2&gt;
    &lt;p&gt;This primary content flows directly into the main @content area.&lt;/p&gt;

    &lt;!-- Reusable partial card with custom data --&gt;
    @@include('components.card', ['title' =&gt; 'Fast Development', 'desc' =&gt; 'Core PHP engine with zero bloat'])
&lt;/section&gt;

&lt;!-- 3. Connect Custom Footer --&gt;
@@contentConnect('footer')
&lt;footer class="custom-footer"&gt;
    &lt;p&gt;&amp;copy; @{{ date('Y') }} Custom Footer for Home Page.&lt;/p&gt;
&lt;/footer&gt;
@@endContentConnect</code></pre>

                        <h3>Including Partials: <code>@@include('path.file')</code></h3>
                        <p>Add reusable partial views directly into any webpage using clean dot-notation or slash paths (e.g. <code>@@include('partials.navbar')</code> or <code>@@include('components.card')</code>). All webpage variables are automatically inherited, and you can pass custom parameters:</p>

                        <pre><code>&lt;!-- Direct partial include with inherited page variables --&gt;
@@include('partials.navbar')      &lt;!-- Resolves to Pages/partials/navbar.srin.php --&gt;
@@include('includes.sidebar')     &lt;!-- Resolves to Pages/includes/sidebar.srin.php --&gt;

&lt;!-- Passing custom variables into the partial --&gt;
@@include('components.product-card', [
    'title' =&gt; 'Srin Engine Pro',
    'price' =&gt; 99.00,
    'badge' =&gt; 'Popular'
])</code></pre>

                        <h3>Master Shortcode &amp; Template Directive Reference:</h3>
                        <p>Below is the complete cheat sheet of all template shortcodes and directives supported by Srin Engine:</p>

                        <div class="helpers-table-wrapper">
                            <table class="helpers-table">
                                <thead>
                                    <tr>
                                        <th style="width: 28%;">Shortcode / Directive</th>
                                        <th style="width: 38%;">Syntax / Example</th>
                                        <th>Purpose &amp; Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background: rgba(2, 132, 199, 0.05); font-weight: 700;">
                                        <td colspan="3">1. Layouts, Slots &amp; Titles</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@Connect('layouts/name')</code></td>
                                        <td><code>@@Connect('layouts/main')</code></td>
                                        <td>Connects a child webpage to a master layout template in <code>Pages/layouts/</code>.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@title('Page Title')</code></td>
                                        <td><code>@@title('Dashboard - Analytics')</code></td>
                                        <td>Sets the page title variable <code>\$title</code> for the layout.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@content('name', 'fallback')</code></td>
                                        <td><code>@@content('header', view('partials.nav'))</code></td>
                                        <td>Defines a named slot in master layout with optional fallback content.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@content</code></td>
                                        <td><code>@@content</code></td>
                                        <td>Injects primary child page content into the layout.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@contentConnect('name')</code></td>
                                        <td><code>@@contentConnect('header') ... @@endContentConnect</code></td>
                                        <td>Defines custom content block on webpage to inject into matching layout slot.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@connectContent('name')</code></td>
                                        <td><code>@@connectContent('main') ... @@endconnectContent</code></td>
                                        <td>Alias for <code>@@contentConnect</code> (word-order flexible).</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@contentCconnect('name')</code></td>
                                        <td><code>@@contentCconnect('footer') ... @@endContentCconnect</code></td>
                                        <td>Alias for <code>@@contentConnect</code> (supports double-C spelling).</td>
                                    </tr>

                                    <tr style="background: rgba(2, 132, 199, 0.05); font-weight: 700;">
                                        <td colspan="3">2. Partials &amp; Components</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@include('path.file')</code></td>
                                        <td><code>@@include('partials.navbar')</code></td>
                                        <td>Directly includes a reusable partial view. Supports dot-notation and slash paths.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@include('name', \$data)</code></td>
                                        <td><code>@@include('components.card', ['title' =&gt; 'Pro'])</code></td>
                                        <td>Includes a partial while passing custom scoped variables.</td>
                                    </tr>

                                    <tr style="background: rgba(2, 132, 199, 0.05); font-weight: 700;">
                                        <td colspan="3">3. Database &amp; UI Controls</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@paginate(\$paginator)</code></td>
                                        <td><code>@@paginate(\$articles)</code></td>
                                        <td>Renders modern, responsive HTML pagination controls with numbered pills &amp; prev/next links.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@flash('key')</code></td>
                                        <td><code>@@flash('success')</code>, <code>@@flash('error')</code></td>
                                        <td>Automatically renders session flash message alerts if present.</td>
                                    </tr>

                                    <tr style="background: rgba(2, 132, 199, 0.05); font-weight: 700;">
                                        <td colspan="3">4. Security &amp; Anti-Hacking Directives</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@csrf</code></td>
                                        <td><code>@@csrf</code></td>
                                        <td>Renders hidden cryptographic CSRF protection input field inside forms.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@honeypot</code></td>
                                        <td><code>@@honeypot</code></td>
                                        <td>Renders invisible anti-bot honeypot input field to trap automated spam bots.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ \$expression }}</code></td>
                                        <td><code>@{{ \$article-&gt;title }}</code></td>
                                        <td>Evaluates PHP and automatically escapes output with <code>htmlspecialchars</code> (XSS-safe).</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{!! \$trustedHtml !!}</code></td>
                                        <td><code>@{!! \$article-&gt;body !!}</code></td>
                                        <td>Renders raw, unescaped HTML content (for trusted rich text editors).</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{-- comment --}}</code></td>
                                        <td><code>@{{-- Internal developer notes --}}</code></td>
                                        <td>Server-side comment, completely stripped and omitted from HTML source code.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@@{{ clientVar }}</code></td>
                                        <td><code>@@@{{ vueCount }}</code></td>
                                        <td>Escapes braces for frontend JavaScript frameworks (Vue.js, Alpine.js, React).</td>
                                    </tr>

                                    <tr style="background: rgba(2, 132, 199, 0.05); font-weight: 700;">
                                        <td colspan="3">5. Template Control Flow Directives</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@if / @@elseif / @@else</code></td>
                                        <td><code>@@if(\$count &gt; 0) ... @@else ... @@endif</code></td>
                                        <td>Standard conditional branching.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@unless(condition)</code></td>
                                        <td><code>@@unless(\$loggedIn) ... @@endunless</code></td>
                                        <td>Inverted condition (executes when condition is false).</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@isset(\$variable)</code></td>
                                        <td><code>@@isset(\$user-&gt;bio) ... @@endisset</code></td>
                                        <td>Checks if a variable is set and not null.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@empty(\$variable)</code></td>
                                        <td><code>@@empty(\$posts) ... @@endempty</code></td>
                                        <td>Checks if a collection or variable is empty.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@foreach(\$items as \$item)</code></td>
                                        <td><code>@@foreach(\$posts as \$post) ... @@endforeach</code></td>
                                        <td>Iterates over arrays, collections, or paginators.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@for(...)</code></td>
                                        <td><code>@@for(\$i = 1; \$i &lt;= 5; \$i++) ... @@endfor</code></td>
                                        <td>Standard numeric for loop.</td>
                                    </tr>
                                    <tr>
                                        <td><code>@@while(condition)</code></td>
                                        <td><code>@@while(\$hasMore) ... @@endwhile</code></td>
                                        <td>Standard while loop.</td>
                                    </tr>

                                    <tr style="background: rgba(2, 132, 199, 0.05); font-weight: 700;">
                                        <td colspan="3">6. Template Helper Functions (Inside <code>@{{ }}</code>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>assets('/path')</code></td>
                                        <td><code>@{{assets('/css/style.css')}}</code></td>
                                        <td>Generates absolute URL directly to assets folder (subfolder safe).</td>
                                    </tr>
                                    <tr>
                                        <td><code>route('name', \$params)</code></td>
                                        <td><code>@{{route('posts.show', ['slug' =&gt; 'hi'])}}</code></td>
                                        <td>Generates URL for a named route from <code>Routes/web.php</code>.</td>
                                    </tr>
                                    <tr>
                                        <td><code>url('/path')</code></td>
                                        <td><code>@{{url('/contact')}}</code></td>
                                        <td>Generates application URL with subfolder auto-detection.</td>
                                    </tr>
                                    <tr>
                                        <td><code>config('key', 'default')</code></td>
                                        <td><code>@{{config('app.name')}}</code></td>
                                        <td>Retrieves application configuration values.</td>
                                    </tr>
                                    <tr>
                                        <td><code>old('field', 'default')</code></td>
                                        <td><code>@{{old('email')}}</code></td>
                                        <td>Retrieves previous form input after a validation error.</td>
                                    </tr>
                                    <tr>
                                        <td><code>is_home()</code></td>
                                        <td><code>@@unless(is_home()) ... @@endunless</code></td>
                                        <td>Returns <code>true</code> on homepage (<code>/</code> or named route <code>'home'</code>), <code>false</code> otherwise.</td>
                                    </tr>
                                    <tr>
                                        <td><code>is_route('name')</code></td>
                                        <td><code>@@if(is_route('contact')) ... @@endif</code></td>
                                        <td>Checks if current route name or URI matches. Supports wildcards (<code>'blog.*'</code>).</td>
                                    </tr>
                                    <tr>
                                        <td><code>request_uri()</code></td>
                                        <td><code>@@if(request_uri() !== '/') ... @@endif</code></td>
                                        <td>Returns the current relative request path (without query parameters).</td>
                                    </tr>
                                    <tr>
                                        <td><code>csrf_field()</code></td>
                                        <td><code>@{{csrf_field()}}</code></td>
                                        <td>HTML hidden input containing CSRF security token.</td>
                                    </tr>
                                    <tr>
                                        <td><code>honeypot_field()</code></td>
                                        <td><code>@{{honeypot_field()}}</code></td>
                                        <td>HTML hidden input for anti-bot spam prevention.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- 8. Defining Routes -->
                    <section id="routing" class="doc-card">
                        <h2>8. Defining Routes &amp; Named Links (<code>Routes/web.php</code>)</h2>
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

                    <!-- 9. Mail Transfer -->
                    <section id="mail-transfer" class="doc-card">
                        <h2>9. Mail Transfer Feature</h2>
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

                    <!-- 10. Composer Support & Automated Setup -->
                    <section id="composer" class="doc-card">
                        <h2>10. Composer Support &amp; Automated Download</h2>
                        <p>Srin is designed to be completely installable via Composer. Anyone on a new computer can run a single command to automatically download the entire engine, directory layout, and dependencies:</p>

                        <pre><code># Download and setup Srin on any new computer:
composer create-project deadxfire/srin my-project</code></pre>

                        <p>Vendor packages are configured to install into <code>Others/</code> instead of <code>vendor/</code>, keeping your project root clean:</p>
                        <pre><code>composer require &lt;package-name&gt;</code></pre>
                        <p>All classes and packages installed in <code>Others/</code> are automatically autoloaded via <code>Others/autoload.php</code>.</p>
                    </section>

                    <!-- 11. Custom Error Pages -->
                    <section id="error-pages" class="doc-card">
                        <h2>11. Custom Error Pages (<code>Pages/errors/</code>)</h2>
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

                    <!-- 12. Enterprise Security & Downloads -->
                    <section id="security" class="doc-card">
                        <h2>12. Enterprise Security &amp; Anti-Hacking Defenses</h2>
                        <p>Srin Engine enforces a comprehensive, defense-in-depth security model to defeat common and advanced web attack vectors:</p>

                        <div class="highlight-box">
                            🔒 <strong>Multi-Layer Defense Matrix:</strong> Built-in protection against Cross-Site Scripting (XSS), CSRF, Bot Flooding, Rate Limiting / Brute-Force, Parameter Pollution, Path Traversal, and HTTP Response Splitting.
                        </div>

                        <h3>Comprehensive Hacking Defenses:</h3>
                        <ul class="feature-bullets">
                            <li>
                                <strong>1. XSS (Cross-Site Scripting) Defense:</strong>
                                All <code>&#123;&#123; $expr &#125;&#125;</code> statements are automatically compiled with <code>htmlspecialchars(..., ENT_QUOTES, 'UTF-8')</code>. Any injected <code>&lt;script&gt;</code> tags or HTML attributes are completely neutralized.
                            </li>
                            <li>
                                <strong>2. CSRF (Cross-Site Request Forgery) Defense:</strong>
                                Forms are secured using cryptographically secure tokens via <code>@@csrf</code> (or <code>&#123;&#123; csrf_field() &#125;&#125;</code>). Validated using timing-safe <code>hash_equals()</code>; failed tokens cleanly trigger HTTP <code>419 Page Expired</code>.
                            </li>
                            <li>
                                <strong>3. Automated Bot &amp; Spam Trap (Honeypot):</strong>
                                Add <code>@@honeypot</code> to forms. An invisible CSS-hidden input traps automated spam bots while real humans never see it. Verify with <code>\$this-&gt;validateHoneypot()</code>.
                            </li>
                            <li>
                                <strong>4. Rate Limiting &amp; Brute-Force / DoS Protection:</strong>
                                Throttle endpoint submissions using <code>\$this-&gt;rateLimit('contact-form', 5, 60)</code>. Exceeded attempts automatically abort with HTTP <code>429 Too Many Requests</code>.
                            </li>
                            <li>
                                <strong>5. Array Injection &amp; Mass Assignment Defense:</strong>
                                Use <code>Request::string('field')</code> to prevent type-juggling attacks, and <code>Request::only(['name', 'email'])</code> to strictly whitelist allowed inputs.
                            </li>
                            <li>
                                <strong>6. CRLF Injection &amp; Response Splitting Defense:</strong>
                                <code>Response::redirect()</code> and internal headers automatically strip <code>\r</code> and <code>\n</code> carriage return sequences to prevent header injection.
                            </li>
                            <li>
                                <strong>7. 404 Cloaking for Core &amp; Config Files:</strong>
                                Direct HTTP access to <code>/core/App.php</code>, <code>/Config/mail.php</code>, <code>/Pages/welcome.srin.php</code>, or <code>.env</code> always returns HTTP 404 (cloaking your file structure from attackers).
                            </li>
                            <li>
                                <strong>8. Download Isolation:</strong>
                                All downloadable files must reside strictly in <code>assets/</code> (e.g. <code>assets/downloads/</code>). Downloads from <code>Pages/</code> or core folders are blocked with 404.
                            </li>
                        </ul>

                        <h3>Secure Controller Implementation Example:</h3>
                        <pre><code>// In Controller/ContactController.php:
public function sendMail()
{
    // 1. Defeat brute force / DoS:
    \$this-&gt;rateLimit('contact-form', 5, 60);

    // 2. Defeat CSRF attacks:
    \$this-&gt;validateCsrf();

    // 3. Defeat automated spam bots:
    \$this-&gt;validateHoneypot();

    // 4. Defeat parameter pollution:
    \$inputs = \$this-&gt;onlyInputs(['name', 'email', 'message']);
    \$errors = \$this-&gt;validate(\$inputs, [
        'name'    =&gt; 'required|min:2|max:100',
        'email'   =&gt; 'required|email',
        'message' =&gt; 'required|min:10|max:5000',
    ]);
}</code></pre>
                    </section>

                    <!-- 13. Global Helpers -->
                    <section id="helpers" class="doc-card">
                        <h2>13. Useful Global Helpers</h2>
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
