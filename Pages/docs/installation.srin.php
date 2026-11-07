@Connect('layouts/docs')

<p class="doc-lead">
    Getting started with Srin Engine is rapid and straightforward. Follow this guide to set up your environment, clone the project, configure your environment variables, and start the local development server.
</p>

<h2>Server Requirements</h2>
<p>
    Srin Engine is built to be lean and dependency-light, but requires modern PHP capabilities. Before installing, ensure your environment meets the following requirements:
</p>

<ul>
    <li><strong>PHP 8.1</strong> or higher (PHP 8.2+ recommended)</li>
    <li><strong>PDO PHP Extension</strong> (along with <code>pdo_sqlite</code>, <code>pdo_mysql</code>, or <code>pdo_pgsql</code>)</li>
    <li><strong>OpenSSL PHP Extension</strong> (for encryption, secure session tokens, and CSRF)</li>
    <li><strong>Mbstring PHP Extension</strong> (for multibyte string processing)</li>
    <li><strong>Composer</strong> (optional, for autoloading and dependency management)</li>
</ul>

<h2>Installing Srin Engine</h2>

<h3>Option 1: Using Composer (Recommended)</h3>
<p>Create a fresh Srin Engine application using Composer:</p>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>composer create-project deadxfire/srin my-srin-app</code></pre>
</div>

<h3>Option 2: Cloning from GitHub</h3>
<p>Alternatively, clone the repository directly from GitHub:</p>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>git clone https://github.com/deadxfire/srin.git my-srin-app
cd my-srin-app
composer install</code></pre>
</div>

<h2>Environment Configuration</h2>
<p>
    Srin uses a root <code>.env</code> file to manage sensitive and environment-specific configuration values. When you clone the project, copy the template file:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code># Linux / macOS
cp .env.example .env

# Windows PowerShell
copy .env.example .env</code></pre>
</div>

<p>Open <code>.env</code> in your code editor. Here is a typical configuration:</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">INI / .env</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>APP_NAME="Srin Engine"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=base64:srin-random-secret-key-32-chars

# Database Configuration (sqlite, mysql, or pgsql)
DB_CONNECTION=sqlite
DB_DATABASE=Storage/database.sqlite

# Or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=srin_db
# DB_USERNAME=root
# DB_PASSWORD=secret</code></pre>
</div>

<div class="callout callout-warning">
    <div class="callout-title">Production Notice</div>
    <p>In production environments, always set <code>APP_ENV=production</code> and <code>APP_DEBUG=false</code>. This prevents internal stack traces from being displayed to end users and routes fatal errors to custom <code>Pages/errors/500.srin.php</code> views.</p>
</div>

<h2>Directory Permissions</h2>
<p>
    Ensure that the <code>Storage/</code> directory is writable by your web server or CLI user:
</p>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>chmod -R 775 Storage</code></pre>
</div>

<h2>Starting the Development Server</h2>
<p>
    Srin Engine comes bundled with a dedicated CLI utility (<code>srin</code>). You can launch the built-in development server with a single command:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>php srin serve</code></pre>
</div>

<p>By default, the server starts at <code>http://127.0.0.1:8000</code>. You can customize the host and port:</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>php srin serve --port=9000 --host=0.0.0.0</code></pre>
</div>

<h2>Directory Structure Overview</h2>
<p>
    The Srin directory layout is clean, organized, and intentional:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Tree</span>
    </div>
    <pre><code>srin-app/
├── Config/           # Application, database, mailer & auth configuration
├── Controller/       # HTTP Controllers handling incoming requests
├── Core/             # Srin Engine core kernel, router, query builder, ORM
├── Database/
│   ├── Migrations/   # Database schema migrations
│   └── Seeders/      # Database test data seeders
├── Model/            # ActiveRecord Model classes
├── Pages/            # View templates (*.srin.php) & layouts
│   ├── errors/       # Custom 404, 419, 500 error pages
│   └── layouts/      # Master layout templates (@Connect)
├── Public/           # Web server document root (index.php, CSS, JS, images)
│   ├── assets/       # Static public assets
│   └── index.php     # Front controller & entry point
├── Routes/
│   ├── api.php       # Stateless API routes
│   └── web.php       # Web routes with session & CSRF protection
├── Storage/          # Local SQLite DB, logs, file cache, and uploads
├── .env              # Environment-specific credentials
├── composer.json     # Project dependencies and PSR-4 autoloading
└── srin              # Srin CLI console executable</code></pre>
</div>
