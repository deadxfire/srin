# SRIN Engine - Core PHP Static Website System

**SRIN** is a lightweight, high-performance, and ultra-secure **Core PHP static website engine** architected and developed by **Arindam Makar** ([@deadxfire](https://github.com/deadxfire)). Designed for blazing-fast static websites paired with dynamic essentials: mail transfer via SMTP or logs, native `.env` environment configuration, Composer packages in a custom `Others/` vendor directory, strict `.srin.php` templates with `[layout]` master templates, named routing, direct asset linking, automated error handling, and the dedicated `php srin` CLI tool.

Starts with a clean slate: no dummy controllers or unwanted bloat. The default page is **`Pages/welcome.srin.php`**, serving the interactive documentation and system verification.

- **Developer:** Arindam Makar
- **GitHub:** [https://github.com/deadxfire](https://github.com/deadxfire)
- **Composer Package:** `deadxfire/srin`

---

## 🚀 Instant Download & Installation via Composer

You can automatically download and scaffold the entire Srin system onto any new computer using Composer:

```bash
composer create-project deadxfire/srin my-project
```

### Alternative: Install from GitHub / VCS Repository
```bash
composer create-project deadxfire/srin my-project --repository='{"type":"vcs","url":"https://github.com/deadxfire/srin.git"}'
```

### Alternative: Git Clone
```bash
git clone https://github.com/deadxfire/srin.git my-project
cd my-project
composer install
```

After installation completes, start the local development server:
```bash
php srin serve
```
Visit **`http://localhost:8000`** in your browser.

---

## 🌐 How to Publish to Composer (Packagist) for Seamless Installation

To make `composer create-project deadxfire/srin <project-name>` work worldwide on any computer, follow these simple steps:

### Step 1: Push your Code to GitHub
1. Create a repository on GitHub named `srin` under your account: `https://github.com/deadxfire/srin`.
2. Initialize and push your code:
   ```bash
   git init
   git add .
   git commit -m "Initial release of Srin Engine"
   git remote add origin https://github.com/deadxfire/srin.git
   git branch -M main
   git push -u origin main
   ```
3. (Optional but recommended) Create a release tag:
   ```bash
   git tag -a v1.0.0 -m "Release v1.0.0"
   git push origin v1.0.0
   ```

### Step 2: Submit to Packagist.org
1. Visit [Packagist.org](https://packagist.org/) and log in with your GitHub account.
2. Click **Submit** in the top navigation.
3. Paste your repository URL: `https://github.com/deadxfire/srin.git` and click **Check**.
4. Packagist will read `composer.json` and detect the package name `deadxfire/srin`. Click **Submit**.

### Step 3: Enable Auto-Update via GitHub Webhook
- In your Packagist package page, go to settings and copy your API Token.
- On GitHub, go to your repository **Settings &rarr; Webhooks &rarr; Add Webhook**, select Packagist integration, or use GitHub Services. Whenever you push new commits or tags, Packagist updates automatically.

Once published, anyone can install Srin Engine on any computer with:
```bash
composer create-project deadxfire/srin my-website
```

---

## 🌿 Environment Variables (`.env`) & How They Work

**Yes, your `.env` works right out of the box!**

Srin Engine includes a native, zero-dependency `.env` loader built into `Core\App::loadEnv()`. When your application starts, it reads `.env` and automatically populates `$_ENV`, `$_SERVER`, and PHP's `getenv()`.

### 1. Setup Your `.env` File
Copy the example file to `.env`:
```bash
cp .env.example .env
# Windows Command Prompt:
copy .env.example .env
```

Example `.env`:
```env
# App Configuration
APP_NAME="My Custom Website"
APP_ENV=development
APP_DEBUG=true
APP_BASE_URL="/"
APP_TIMEZONE="Asia/Kolkata"

# Mail Configuration
MAIL_DRIVER=log
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mysite.com
MAIL_FROM_NAME="My Custom Website"
MAIL_ADMIN_ADDRESS=admin@mysite.com
```

### 2. The `env()` Helper Function
Use the global `env()` helper anywhere in your configuration or application:
```php
$appName = env('APP_NAME', 'Srin Engine');
$isDebug = env('APP_DEBUG', false);
```
- Automatically converts `"true"`/`"false"` strings into actual PHP booleans `true`/`false`.
- Automatically converts `"null"` into PHP `null`.

---

## ⚙️ Main Application Configuration: `Config/app.php`

`Config/app.php` is the **primary configuration file** for the entire Srin Engine:

```php
return [
    'name'      => env('APP_NAME', 'Srin Engine'),
    'developer' => 'Arindam Makar',
    'github'    => 'https://github.com/deadxfire',
    'env'       => env('APP_ENV', 'development'),
    'debug'     => (bool) env('APP_DEBUG', true),
    'base_url'  => env('APP_BASE_URL', '/'),
    'timezone'  => env('APP_TIMEZONE', 'Asia/Kolkata'),
    'locale'    => env('APP_LOCALE', 'en'),
    'charset'   => 'UTF-8',
];
```

### Accessing Configuration Values
Top-level keys automatically resolve from `Config/app.php`:
```php
config('name');         // Returns env('APP_NAME') or 'Srin Engine'
config('developer');    // 'Arindam Makar'
config('debug');        // Returns boolean
config('app.name');     // Dot-notation also supported
config('mail.driver');  // Loads from Config/mail.php
```

---

## 📁 Project Structure

```text
phpsystem/
├── assets/                 # All public assets (CSS, JS, images, downloads)
│   ├── css/                # Put your custom stylesheets here
│   ├── js/                 # Put your custom scripts here
│   ├── images/             # Public image directories and graphics
│   └── downloads/          # Downloadable files strictly served from here
│
├── Config/                 # Application configuration
│   ├── app.php             # Main configuration file (name, env, debug, developer, etc.)
│   ├── database.php        # Database connection config (sqlite, mysql, pgsql)
│   └── mail.php            # Mail driver (smtp, log, mail) and SMTP credentials
│
├── Controller/             # Custom controllers (starts clean)
│   └── .gitkeep            # Clean slate ready for your controllers
│
├── Model/                  # ActiveRecord database models
│   └── .gitkeep            # Clean slate ready for your models
│
├── database/               # Database files & migrations
│   └── migrations/         # Database migration blueprints
│       └── .gitkeep
│
├── Core/                   # Srin engine core logic
│   ├── App.php             # Application bootstrap, .env loader & unified error handler
│   ├── BaseController.php  # Base controller with render(), json(), redirect(), validate()
│   ├── Blueprint.php       # Schema column builder & multi-dialect SQL generator
│   ├── Database.php        # Singleton PDO connection manager
│   ├── Helper.php          # Global helpers: env(), view(), route(), config(), download(), asset()
│   ├── Mailer.php          # Mail transfer service (SMTP / native mail / log driver)
│   ├── Migration.php       # Base abstract migration class (up/down)
│   ├── Migrator.php        # Database migration runner, rollback & status tracker
│   ├── Model.php           # ActiveRecord base model (CRUD, timestamps, mass-assignment guard)
│   ├── QueryBuilder.php    # SQL-injection-safe fluent query builder
│   ├── Request.php         # HTTP request wrapper (inputs, sanitize, method, URI)
│   ├── Response.php        # HTTP response, redirects, and secure asset downloads
│   ├── Router.php          # URL routing engine (GET, POST, named routes, closures)
│   ├── Schema.php          # Schema builder facade (create, drop, dropIfExists)
│   └── View.php            # Strict .srin.php template renderer with [layout] & [block]
│
├── Pages/                  # Frontend templates & layouts (.srin.php only)
│   ├── errors/             # Custom error pages (e.g. 404.srin.php, 500.srin.php)
│   │   └── 404.srin.php    # Automatic 404 error page
│   └── welcome.srin.php    # Default welcome & documentation page
│
├── Routes/                 # Application route links
│   └── link.php            # Clean route link definitions with named links
│
├── Others/                 # Composer vendor packages & autoloader
│   ├── composer/           # Autoload maps generated by Composer
│   ├── phpmailer/          # PHPMailer package installed via Composer
│   ├── logs/               # Mail logs (mail.log) when using the 'log' driver
│   └── autoload.php        # Composer autoloader
│
├── srin                    # Srin CLI executable (cross-platform)
├── srin.php                # Srin CLI PHP entry (php srin.php)
├── index.php               # Single public entry point
├── .htaccess               # Apache clean URL rewrites & security cloaking
├── .env.example            # Environment variables blueprint
├── composer.json           # Composer configuration ("type": "project", vendor-dir: "Others")
└── composer.lock           # Locked Composer dependency tree
```

---

## 🔒 Enterprise-Grade Security & Anti-Hacking Defenses

Srin Engine enforces strict access boundaries and defense-in-depth protections to defeat common and advanced web attack vectors:

### 1. XSS (Cross-Site Scripting) Defense on Output Expressions
All dynamic expressions evaluated inside `[[ $expression ]]` are automatically sanitized using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. Any injected HTML or `<script>` tags are neutralized before reaching the browser. Raw unescaped output is strictly quarantined to `[{ $trustedHtml }]`.

### 2. Bulletproof CSRF Protection for Forms
Every form submission is protected using cryptographically random session tokens via `[csrf]` (or `<?= csrf_field() ?>`). Verified using timing-attack-safe `hash_equals()`; expired tokens automatically render a custom HTTP 419 error page with safe token regeneration.

### 3. Invisible Anti-Bot Honeypot (`[honeypot]`)
Block automated spam bots on contact and inquiry forms without slow, intrusive CAPTCHAs. Real users never see the invisible field; bots fill it and get rejected immediately.

### 4. Rate Limiting & DoS / Brute-Force Throttling
Defend endpoints against flood attacks with `$this->rateLimit('contact-form', 5, 60)`. When limits are exceeded, requests cleanly abort with HTTP 429 Too Many Requests.

### 5. 404 Cloaking for Core & Internal Files
Direct HTTP access to internal code files (e.g. `http://localhost:8000/core/App.php`, `/Config/mail.php`, `/Pages/welcome.srin.php`, or `.env`) **always returns HTTP 404 Not Found** with your custom 404 error page, instead of a revealing 403 Forbidden. Attackers cannot discover whether files exist or probe your internal directory structure.

### 6. Download File Isolation (Strictly from `assets/`)
Downloadable files are strictly restricted to the `assets/` directory (e.g. `assets/downloads/`):
```php
// In a Controller:
return $this->download('catalog.pdf'); 
// Or via helper:
return download('catalog.pdf', 'Company_Catalog.pdf');
```
- Attempting to download from `Pages/`, `Core/`, `Config/`, or traversing directories (`../../etc/passwd`) is immediately blocked and **throws a 404 error**.

### 7. Strict Template Extension (`.srin.php`) & Secure Compilation Cache
- **Pages/ Directory Enforcement**: Only `.srin.php` template files strictly residing within `Pages/` are executed. Directory traversal attempts (`../`) and standard `.php` templates are strictly rejected.
- **Compiled Disk Cache**: Compiled templates are written to a secure private cache directory (`Others/cache/views/`, permissions `0700`, with `.htaccess` `Require all denied` and `index.html`) and executed via `include` instead of `eval()`.
- **Trusted Template Source Contract**: Template source code must strictly be developer-authored and trusted. Dynamic user/request data must always be passed into views via the `$data` parameters array (e.g., `view('page', ['user' => $input])`) and **never** concatenated or interpolated into template source.

---

## 🎨 Running PHP in Templates: Native Srin Tags & Output Syntax

Inside any `.srin.php` template, run PHP code and output data using native Srin tags and auto-escaped output:

```html
<!-- Display Variables & Auto-Escaped Expressions (XSS-Safe) -->
<h1>[[ $title ]]</h1>
<p>Today is [[ date('F j, Y') ]]</p>
<p>Copyright &copy; [[ date('Y') ]] [[ config('name') ]]</p>

<!-- Form with Complete Anti-Hacking Protection -->
<form action="[[ route('contact.send') ]]" method="POST">
    [csrf]       <!-- CSRF security token field -->
    [honeypot]   <!-- Invisible anti-bot spam trap -->

    <input type="text" name="name" value="[[ old('name') ]]" placeholder="Your Name" required>
    <input type="email" name="email" value="[[ old('email') ]]" placeholder="Your Email" required>
    <textarea name="message" placeholder="Your Message">[[ old('message') ]]</textarea>
    <button type="submit">Send Message</button>
</form>

<!-- Raw / Unescaped HTML (only when explicitly intended) -->
<div>[{ $rawHtml }]</div>

<!-- Secure Template Comments (completely omitted from browser source) -->
[-- This comment will never show up in browser inspect element --]

<!-- Frontend JavaScript Escaping (Vue.js / Alpine.js / React) -->
<span>@[[ vueData ]]</span> <!-- Compiles literally to [[ vueData ]] -->
```

### Control Flow: Native Srin Tags & Descriptive Closers:
```html
[if $user]
    <h2>Welcome back, [[ $user->name ]]!</h2>
[else]
    <a href="[[ route('auth.login') ]]">Sign In</a>
[ifend]

[each $posts as $post]
    <article>
        <h3>[[ $post->title ]]</h3>
        <p>[[ $post->excerpt ]]</p>
    </article>
[empty]
    <p>No articles published yet.</p>
[eachend]
```

---

## 🛠️ Srin CLI Generator Commands (`php srin`)

Run `php srin` (or `php srin.php`) to generate controllers, checkpoints, models, migrations, pages, layouts, and manage logs:

```bash
# 1. Start development server
php srin serve
php srin serve --port=8080

# 2. Application Logging (Terminal Log Viewer)
php srin logs                        # View latest application log entries
srin logs -f                     # Live stream / tail logs in real-time (Ctrl+C to stop)
srin logs --lines=50             # View last 50 log lines
srin logs --mail                 # View mail delivery log
srin clear logs                  # Clear all log files in Others/logs/

# 3. Request Checkpoints (Linear Middleware Gates)
srin add checkpoint VerifySubscription # Scaffolds Checkpoint/VerifySubscriptionCheckpoint.php

# 4. Database Models & Migrations
srin add model Post -m          # Create Model/Post.php AND a migration
srin add model Inquiry          # Create Model/Inquiry.php only
srin add migration add_views    # Create a new migration file
srin migrate                    # Run all pending migrations
srin migrate rollback           # Rollback the last migration batch
srin migrate status             # Show migration status table
srin migrate fresh              # Drop all tables and re-run migrations

# 5. Create a standard controller
srin add controller Home

# 6. Create a controller with mail transfer
srin add controller Contact --mail

# 7. Create a new frontend page (.srin.php)
srin add page portfolio

# 8. Create a master layout
srin add layout admin

# 9. Create a custom error page (e.g., 403, 404, 500)
srin add error 404
srin add error 500

# 10. List all registered route links
srin links
```

---

## 🗄️ Database Models & Migrations (ActiveRecord)

Srin Engine features a lightweight yet expressive **ActiveRecord ORM** and schema migration system inspired by modern frameworks. It works out-of-the-box with **SQLite** (zero setup required), and seamlessly connects to **MySQL / MariaDB** and **PostgreSQL**.

### 1. Database Configuration (`Config/database.php`)

Set your database driver and credentials in your `.env` file:

```env
# Default: Zero-configuration SQLite
DB_CONNECTION=sqlite
DB_DATABASE="database/database.sqlite"

# Or MySQL / MariaDB:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=srin_db
# DB_USERNAME=root
# DB_PASSWORD=secret
# DB_CHARSET=utf8mb4

# Or PostgreSQL:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=srin_db
# DB_USERNAME=postgres
# DB_PASSWORD=secret
```

### 2. Creating Models & Migrations

Use `srin add model <Name> -m` to generate both the model and its corresponding table migration in one step:

```bash
srin add model Article -m
```

This generates:
- `Model/Article.php` (namespaced under `Model\Article`, extending `Core\Model`)
- `database/migrations/YYYY_MM_DD_HHMMSS_create_articles_table.php`

### 3. Writing Migrations (`Core\Schema` & `Core\Blueprint`)

Each migration file defines an `up()` method to create or alter tables, and a `down()` method to rollback:

```php
<?php

use Core\Migration;
use Core\Schema;
use Core\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();                                  // Auto-increment primary key
            $table->string('title');                       // VARCHAR(255)
            $table->string('slug')->unique();              // Unique column
            $table->text('content')->nullable();           // Nullable TEXT
            $table->integer('views')->default(0);          // Integer with default value
            $table->boolean('is_published')->default(true);// Boolean
            $table->timestamps();                          // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
```

#### Blueprint Column Types Supported:
- `$table->id('id')`
- `$table->string('name', 255)`
- `$table->text('bio')`
- `$table->integer('votes')`
- `$table->bigInteger('amount')`
- `$table->boolean('active')`
- `$table->float('rating')`
- `$table->decimal('price', 10, 2)`
- `$table->date('published_on')`
- `$table->dateTime('event_at')`
- `$table->timestamp('verified_at')`
- `$table->timestamps()` (creates `created_at` and `updated_at`)
- Modifiers: `->nullable()`, `->default($val)`, `->unique()`, `->primary()`

### 4. Running Migrations

```bash
# Apply pending migrations
php srin migrate

# Check migration status
php srin migrate:status

# Rollback last migration batch
php srin migrate:rollback

# Wipe database and rebuild from scratch
php srin migrate:fresh
```

### 5. Writing and Using Models (`Core\Model`)

Models live in `Model/` and automatically map to plural snake_cased tables (`Article` &rarr; `articles`, `Inquiry` &rarr; `inquiries`). You can override `$table`, `$primaryKey`, and protect fields with `$fillable` or `$guarded`:

```php
<?php

namespace Model;

use Core\Model;

class Article extends Model
{
    protected static string $table = 'articles';

    protected array $fillable = [
        'title',
        'slug',
        'content',
        'views',
        'is_published',
    ];
}
```

#### Complete ActiveRecord CRUD Operations:

```php
use Model\Article;

// 1. Create a record (Mass-assignment safe via $fillable)
$article = Article::create([
    'title'        => 'Mastering Srin Engine',
    'slug'         => 'mastering-srin-engine',
    'content'      => 'A complete guide to building fast PHP sites...',
    'is_published' => true,
]);

// 2. Find by Primary Key
$article = Article::find(1);
$article = Article::findOrFail(1); // Throws 404 if not found!

// 3. Querying with Fluent Query Builder (SQL-Injection-Proof via PDO Prepared Statements)
$published = Article::where('is_published', true)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

$first = Article::where('slug', 'mastering-srin-engine')->first();

// 4. Update & Atomic Increment/Decrement
$article->title = 'Updated Title';
$article->save();

// Atomically increment or decrement numeric columns:
$article->increment('views');        // Atomically increments by 1
$article->decrement('stock', 2);     // Atomically decrements by 2

// Or mass update:
$article->update([
    'views' => 150,
]);

// 5. Delete
$article->delete();
// Or delete by ID:
Article::destroy(1);

// 6. Aggregates & Checks (Zero PHP Memory Overhead)
$total  = Article::where('is_published', true)->count();
$views  = Article::sum('views');
$avg    = Article::avg('views');
$min    = Article::min('views');
$max    = Article::max('views');
$exists = Article::where('slug', 'test')->exists();

// 7. Serialization
$array = $article->toArray();
$json  = $article->toJson();
```

### 6. Model Relationships & Eager Loading (`hasMany`, `belongsTo`, `hasOne`)

Srin Engine features an elegant relationship engine with full **lazy loading**, **eager loading** (to eliminate N+1 queries), and **relation persistence**:

```php
namespace Model;

use Core\Model;

class User extends Model
{
    // One-to-Many: A user has many posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // One-to-One: A user has one profile
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

class Post extends Model
{
    // Inverse: A post belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

#### Working with Relationships:

```php
use Model\User;
use Model\Post;

// 1. Create related records through the relationship (automatically sets user_id!)
$user = User::find(1);
$post = $user->posts()->create([
    'title'   => 'My New Post',
    'content' => 'Building fast PHP apps with Srin Engine',
]);

// 2. Dynamic Property Access (Lazy Loading with memoization)
$posts = $user->posts;        // Array of Post instances
echo $post->user->name;       // BelongsTo inverse relationship

// 3. Eager Loading (Eliminates the N+1 database query problem!)
$users = User::with('posts', 'profile')->get();

foreach ($users as $u) {
    echo $u->name . ' has ' . count($u->posts) . ' posts.';
    echo ' Bio: ' . ($u->profile->bio ?? 'No bio');
}

// 4. Serialization automatically nests loaded relations in arrays & JSON
$userArray = $users[0]->toArray();
$userJson  = $users[0]->toJson();
```

#### Using Models in Controllers (Srin Standalone Style):

Srin Engine is designed with a unique, ultra-clean standalone style that eliminates repetitive boilerplate.

```php
<?php

namespace Controller;

use Core\BaseController;
use Model\Article;

class BlogController extends BaseController
{
    /**
     * Clean, expressive index action using Srin Standalone Superpowers:
     */
    public function index(): string
    {
        $category = $this->get('category');
        $search   = $this->get('q');

        // Effortless chained search, filter, and eager loading
        $articles = Article::where('is_published', true)
            ->with('comments')
            ->filter(['category' => $category])
            ->search('title', $search)
            ->latest()
            ->paginate(10);

        return $this->view('blog/index', [
            'title'          => 'Our Blog',
            'articles'       => $articles,
            'categories'     => Article::where('is_published', true)->distinctValues('category'),
            'activeCategory' => $category,
            'searchQuery'    => $search,
            'totalArticles'  => Article::where('is_published', true)->count(),
        ]);
    }

    public function show(string $slug): string
    {
        $article = Article::where('slug', $slug)->first();

        if (!$article) {
            $this->abort(404, 'Article not found');
        }

        // Increment views counter
        $article->increment('views');

        return $this->view('blog/show', [
            'title'   => $article->title,
            'article' => $article,
        ]);
    }
}
```

#### 5. Standalone QueryBuilder & Model Superpowers:

| Method | Example | Description |
| :--- | :--- | :--- |
| `search($columns, $keyword)` | `Article::search(['title', 'content'], $q)->get()` | Performs multi-column SQL `LIKE` search. Automatically skips if keyword is empty/null! |
| `filter($assocArray)` | `Article::filter(['category' => $cat, 'author_id' => $id])->get()` | Applies `WHERE` or `WHERE IN`. Automatically ignores empty strings or null keys! |
| `when($value, $callback)` | `Article::when($featured, fn($q) => $q->where('featured', 1))` | Conditionally adds clauses when truthy without breaking the method chain. |
| `distinctValues($column)` | `Article::where('is_published', 1)->distinctValues('category')` | Direct SQL-level extraction of unique column values (zero PHP memory overhead). |
| `pluck($column, $key)` | `User::pluck('name', 'id')` | Extracts an array of column values, optionally indexed by `$key`. |
| `increment($col, $amt)` | `Article::where('id', 5)->increment('views', 1)` | Atomically increments numeric columns directly in database. |
| `decrement($col, $amt)` | `Product::where('id', 2)->decrement('stock', 1)` | Atomically decrements numeric columns directly in database. |
| `chunk($size, $callback)` | `Article::chunk(100, fn($rows) => ...)` | Streams large datasets in manageable memory chunks. |

#### 6. Database Options & Connections (`Config/database.php`):

Srin Engine supports **SQLite**, **MySQL / MariaDB**, and **PostgreSQL** natively via `.env`:

```env
# 1. SQLite (Default - Zero-configuration embedded database)
DB_CONNECTION=sqlite
DB_DATABASE="database/database.sqlite"

# 2. MySQL / MariaDB (Enterprise relational database with UTF8MB4)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=srin_app
# DB_USERNAME=root
# DB_PASSWORD=secret

# 3. PostgreSQL (High-concurrency with custom search path & SSL)
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=srin_app
# DB_USERNAME=postgres
# DB_PASSWORD=secret
```

#### 7. Direct Database Gateway: `db('table')` & Raw SQL:

You don't always need a Model. Access tables directly or run ACID transactions anywhere:

```php
// Query tables directly without defining a Model:
$users = db('users')->where('status', 'active')->latest()->get();

// Raw queries with parameter binding:
$products = db()->select("SELECT * FROM products WHERE price < ? AND stock > ?", [100, 0]);

// Single row and scalar values:
$user = db()->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
$totalViews = db()->scalar("SELECT SUM(views) FROM articles WHERE is_published = 1");

// ACID Transactions:
db()->transaction(function ($pdo) {
    db('accounts')->where('id', 1)->decrement('balance', 50);
    db('accounts')->where('id', 2)->increment('balance', 50);
});
```

#### 8. Fluent Database Pagination (`Model::paginate()`):

Paginate records with automatic calculation of total pages, count, query string preservation, and responsive HTML link controls:

```php
// In a Controller:
$articles = Article::where('is_published', true)->latest()->paginate(10);

// Note: Current GET search & filter parameters are automatically preserved!
return $this->view('blog/index', ['articles' => $articles]);
```

In your `.srin.php` template:
```html
[each $articles as $article]
    <article class="post">
        <h2>[[ $article->title ]]</h2>
        <p>[[ $article->summary ]]</p>
    </article>
[eachend]

<!-- Render responsive pagination controls (prev, numbered pills, next): -->
[paginate $articles]
```

---

## 🎨 Asset Linking with `[[ asset('/path') ]]`

There are **no default CSS or JS files** cluttering your project. You create your own stylesheets, scripts, and media folders inside `assets/`.

Link assets directly inside `.srin.php` templates:

```html
<!-- Stylesheet -->
<link rel="stylesheet" href="[[ asset('/css/style.css') ]]">

<!-- JavaScript -->
<script src="[[ asset('/js/main.js') ]]"></script>

<!-- Public Images (auto-resolves to assets/images/ without needing the folder location) -->
<img src="[[ asset('logo.png') ]]" alt="Logo">
<img src="[[ asset('/images/portfolio/item-1.jpg') ]]" alt="Project">

<!-- Custom Asset Folders -->
<link rel="preload" href="[[ asset('/fonts/inter.woff2') ]]" as="font">
<img src="[[ asset('/icons/check.svg') ]]" alt="Check">
```

> **Direct Links & Subfolder Safe:** `[[ asset('/path') ]]` generates complete absolute URLs (e.g. `http://localhost:8000/assets/...`), auto-maps image filenames directly to `assets/images/`, and automatically detects whether your site is on a root domain (`https://mysite.com`) or inside a server subfolder (`https://mysite.com/subfolder/`).

---

## 🎨 Strict Templates, Master Layouts & Partials

### 1. Master Layout with Named Slots (`Pages/layouts/main.srin.php`):
Define your layout shell and place named content slots using `[slot 'slot_name']`, with `[slot]` for the primary body:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[[ $title ?? config('name') ]]</title>
    <link rel="stylesheet" href="[[ asset('/css/style.css') ]]">
    [slot 'head'] <!-- Optional page-specific meta or CSS -->
</head>
<body>
    <!-- 1. Header Slot with fallback navbar partial -->
    [slot 'header', view('partials.navbar')]

    <!-- 2. Main Page Content Slot -->
    <main class="container">
        [flash 'success']
        [flash 'error']
        [slot]
    </main>

    <!-- 3. Footer Slot -->
    [slot 'footer']

    <!-- 4. Scripts Slot -->
    <script src="[[ asset('/js/main.js') ]]"></script>
    [slot 'scripts']
</body>
</html>
```

### 2. Child Webpage with `[connect]` (`Pages/portfolio.srin.php`):
Connect to the layout with `[connect 'layouts/main']`, feed custom sections directly into layout slots using `[block 'slot_name'] ... [blockend]`, and include partial components directly on the page:

```html
[connect 'layouts/main']
[title 'My Portfolio']

<!-- Connect Custom Header Section -->
[block 'header']
<header class="portfolio-header">
    [include 'partials.navbar'] <!-- Dot-notation partial include -->
    <h1>Featured Creative Portfolio</h1>
</header>
[blockend]

<!-- Primary Content (Automatically flows into [slot]) -->
<section class="portfolio-grid">
    <h2>Client Showcase</h2>
    <p>Rendered securely through Srin Engine.</p>

    <!-- Direct Partial Component with Custom Data -->
    [include 'components.card', [
        'title' => 'E-Commerce Platform',
        'badge' => 'Production',
        'image' => asset('/images/portfolio/proj-1.jpg')
    ]]
</section>

<!-- Connect Custom Footer Section -->
[block 'footer']
<footer class="portfolio-footer">
    <p>&copy; [[ date('Y') ]] Custom Portfolio Footer.</p>
</footer>
[blockend]
```

### 3. Including Reusable Partials: `[include 'path.file']`
Include reusable template chunks directly onto any webpage using clean dot-notation or slash paths. Inherits all current scope variables and accepts custom parameters:

```html
<!-- Webpage Partials (Resolves to Pages/partials/navbar.srin.php) -->
[include 'partials.navbar']

<!-- Includes with Custom Data -->
[include 'components.card', ['title' => 'Fast API', 'desc' => 'Lightweight PHP']]
```

### 4. Complete Srin Tags & Directives Reference (Cheat Sheet)

| Category | Srin Tag / Directive | Example Syntax | Purpose |
| :--- | :--- | :--- | :--- |
| **Layouts** | `[layout 'name']` | `[layout 'layouts/main']` | Connect page to a master layout in `Pages/layouts/` |
| | `[title 'Page Title']` | `[title 'My Profile']` | Set the HTML page title variable `$title` |
| | `[slot 'name', 'fallback']` | `[slot 'header']` | Declare named injection slot in master layout |
| | `[slot]` | `[slot]` | Main page body placeholder in master layout |
| | `[block 'name'] ... [blockend]` | `[block 'scripts'] ... [blockend]` (or `[/block]`) | Inject custom section or assets into layout slot |
| **Partials** | `[include 'path.file']` | `[include 'partials/navbar']` | Include reusable partial template (dot or slash path) |
| | `[include 'path', $data]` | `[include 'components/card', ['title' => 'Pro']]` | Include partial with custom scoped data |
| **Control Flow** | `[if condition] ... [ifend]` | `[if $user] ... [else] ... [ifend]` (or `[/if]`) | Conditional branching with dedicated `[ifend]` |
| | `[elseif condition]` | `[elseif $guest]` | Else-if condition branch |
| | `[else]` | `[else]` | Fallback branch |
| | `[unless condition] ... [endless]` | `[unless $isSubscribed] ... [endless]` (or `[unlessend]`) | Inverted condition (`if (!condition)`) |
| | `[isset $var] ... [issetend]` | `[isset $account->bio] ... [issetend]` (or `[/isset]`) | Variable existence check |
| | `[empty $var] ... [ifend]` | `[empty $posts] ... [ifend]` | Variable emptiness check |
| | `[each $items as $item] ... [eachend]` | `[each $posts as $p] ... [eachend]` (or `[/each]`) | Iterate over arrays, models, or collections |
| | `[empty]` | `[each $p as $i] ... [empty] ... [eachend]` | Fallback when collection is empty (no forelse needed!) |
| | `[for ...] ... [forend]` | `[for $i = 1; $i <= 5; $i++] ... [forend]` (or `[/for]`) | Standard numeric loop |
| | `[while condition] ... [whileend]` | `[while $hasMore] ... [whileend]` (or `[/while]`) | Standard while loop |
| | `[end]` | `[if $x] ... [end]`, `[each $p] ... [end]` | Universal fallback closer supported on any block type |
| **UI Controls** | `[paginate $paginator]` | `[paginate $articles]` | Render responsive HTML pagination with pill links |
| | `[flash 'key']` | `[flash 'success']` | Render session flash alert banner if set |
| **Security** | `[csrf]` | `<form>[csrf]</form>` | Inject hidden CSRF protection token input |
| | `[honeypot]` | `<form>[honeypot]</form>` | Inject invisible spam-bot trap field |
| | `[[ $expression ]]` | `<h1>[[ $article->title ]]</h1>` | Srin Double-Bracket: auto-escaped XSS-safe printing (zero Vue collision!) |
| | `[{ $trustedHtml }]` | `<div>[{ $post->content }]</div>` | Srin HTML Compile: raw unescaped HTML printing for rich text |
| | `[-- comment --]` | `[-- Secret dev note --]` | Server-side comment (omitted from browser source) |
| **Helpers** | `[[assets('/path')]]` | `<link href="[[assets('/css/app.css')]]">` | Absolute path to `assets/` (subfolder safe) |
| | `[[route('name', $params)]]` | `<a href="[[route('blog.show', ['slug' => 'hi'])]]">` | Generate URL for named route |
| | `[[url('/path')]]` | `<a href="[[url('/contact')]]">Contact</a>` | Application URL with subfolder auto-detection |
| | `[[config('key', 'default')]]` | `<title>[[ config('name') ]]</title>` | Read application configuration |
| | `[[old('field')]]` | `<input value="[[ old('email') ]]">` | Repopulate input after validation error |
| | `is_home()` | `[unless is_home()] ... [end]` | Check if current page is the homepage |
| | `is_route('name')` | `[if is_route('contact')] ... [end]` | Check current route name (supports wildcards) |
| | `request_uri()` | `[if request_uri() !== '/'] ... [end]` | Get clean relative request path |

---

## 🛣️ Route Links in `Routes/link.php`

Assign names to your routes using `->name('name')`:

```php
use Core\Link;

// Welcome Page
getlink('/', function() {
    return view('welcome');
})->name('home');

// Controller Route Links with Names
getlink('/portfolio', 'PortfolioController@index')->name('portfolio');
getlink('/contact', 'ContactController@index')->name('contact');
postlink('/contact', 'ContactController@sendMail')->name('contact.send');

// Route with dynamic parameters
getlink('/blog/{slug}', 'BlogController@show')->name('blog.show');

// Fluent OOP Link facade:
Link::get('/pricing', 'PricingController@index')->name('pricing');
```

### Linking with Route Names in Templates:
```html
<a href="[[ route('home') ]]">Home</a>
<a href="[[ route('portfolio') ]]">Portfolio</a>
<a href="[[ route('contact') ]]">Contact Us</a>

<!-- With Parameters -->
<a href="[[ route('blog.show', ['slug' => 'my-first-post']) ]]">Read Article</a>
```

### In Controllers:
```php
// Redirect by route name
$this->redirectToRoute('contact');

// Generate URL by route name
$url = $this->route('portfolio');
```

---

## 🚨 Automatic Custom Error Pages

Simply create a `.srin.php` file named with the HTTP status code inside `Pages/errors/`:
- `Pages/errors/404.srin.php` &rarr; Automatically rendered on any 404 Not Found error.
- `Pages/errors/500.srin.php` &rarr; Automatically rendered on uncaught server errors (when `config('debug') === false`).
- `Pages/errors/403.srin.php` &rarr; Automatically rendered on 403 Forbidden.

Or generate one instantly:
```bash
srin add error 404
```

In your code, abort anytime:
```php
abort(404, 'Product not found');
```

---

## ✉️ Mail Transfer System

Configured in `Config/mail.php` with `.env` support:
- **`'log'` (Default for Development):** Logs all outgoing emails safely to `Others/logs/mail.log` without needing an SMTP connection.
- **`'smtp'`:** Connects to SMTP servers (e.g. Gmail, Mailgun, SendGrid, Amazon SES) using PHPMailer with SSL/TLS.
- **`'mail'`:** Dispatches via PHP's native `mail()` function.

### Generating a Controller with Mail Pre-Wired:
```bash
srin add controller Contact --mail
```

---

## 📜 Application Logging & Terminal Inspection

Srin Engine features a lightweight, zero-dependency logging engine (`Core\Log`) with automatic uncaught exception capture and a dedicated terminal log viewer.

### 1. Where Log Files Are Saved
All application and service logs are stored inside the `Others/logs/` directory:

| Log File | Exact Path | Description |
| :--- | :--- | :--- |
| **Application Log** | `Others/logs/srin.log` | Uncaught server exceptions, system errors, database warnings, and custom messages logged via `Core\Log`. |
| **Mail Dispatch Log** | `Others/logs/mail.log` | Outgoing emails captured in local development when `MAIL_DRIVER=log` is set in `.env`. |

### 2. How to Check & Tail Logs via Terminal

#### Option A: Using the Srin CLI (`php srin logs`)
Srin includes a dedicated terminal log viewer with ANSI color highlights (`ERROR` in red, `WARNING` in yellow, `INFO` in green, and `DEBUG` in cyan):

```bash
# 1. View the latest application logs (default: last 25 lines)
php srin logs

# 2. Live streaming / tailing in real-time (auto-updates as logs arrive; press Ctrl+C to exit)
php srin logs -f
# Or:
php srin logs --tail

# 3. View a custom number of lines
php srin logs --lines=50

# 4. Inspect outgoing mail delivery logs
php srin logs --mail

# 5. Clear / truncate all log files in Others/logs/
php srin clear:logs
# Or:
php srin logs --clear
```

#### Option B: Using Native System Terminals
You can also inspect or live-stream logs directly using your operating system's native terminal commands:

**Windows PowerShell:**
```powershell
# Live stream / tail the application log with PowerShell:
Get-Content Others/logs/srin.log -Wait -Tail 30

# View the last 50 lines:
Get-Content Others/logs/srin.log -Tail 50
```

**Linux / macOS / Git Bash:**
```bash
# Live tail the application log:
tail -f Others/logs/srin.log

# View the last 100 lines:
tail -n 100 Others/logs/srin.log
```

### 3. Writing Logs in PHP Code (`Core\Log`)
Log messages from any controller, model, or checkpoint using `Core\Log` or global helpers:

```php
use Core\Log;

// Using the static Log class:
Log::info('New account registered', ['account_id' => $account->id]);
Log::warning('Rate limit nearing threshold', ['ip' => request_ip()]);
Log::error('Payment processing failed', ['gateway' => 'stripe', 'code' => 504]);
Log::debug('SQL Query execution trace', ['time' => '1.2ms']); // Only recorded when APP_DEBUG=true

// Or using global helper shortcuts:
log_info('Order placed', ['order_id' => 1042]);
log_error('External API failed to respond');
log_warning('Unusual activity detected');
log_debug('Benchmark profile');
```

---

## 🛡️ Request Checkpoints (Linear Middleware Gates)

Instead of convoluted, deeply nested closure pipelines (onion middleware), Srin Engine introduces **Checkpoints**—a clean, linear request verification gate architecture.

### 1. The Checkpoint Lifecycle
Each checkpoint implements `enter(Request $request)` to validate incoming requests, and optionally `leave(Request $request)` to inspect the outgoing response:

```php
<?php

namespace Checkpoint;

use Core\Checkpoint;
use Core\Request;

class VerifySubscriptionCheckpoint extends Checkpoint
{
    public function enter(Request $request): void
    {
        if (!auth_check() || !auth_account()->is_subscribed) {
            flash('error', 'An active subscription is required to view this area.');
            redirect(route('billing.plans'));
        }
    }
}
```

### 2. Built-In Checkpoints
Srin Engine ships with three core checkpoints pre-registered in `Config/auth.php`:

| Checkpoint Alias | Class | Behavior |
| :--- | :--- | :--- |
| `'auth'` | `Checkpoint\AuthCheckpoint` | Requires visitor to be authenticated. Redirects guests to the login page. |
| `'guest'` | `Checkpoint\GuestCheckpoint` | Requires visitor to be a guest. Redirects logged-in accounts to the dashboard. |
| `'admin'` | `Checkpoint\AdminCheckpoint` | Requires an authenticated account with administrator rights. Aborts with HTTP 403 Forbidden if unauthorized. |

### 3. Registering Checkpoints on Routes
Attach checkpoints to individual routes or bundle them into groups:

```php
use Core\Link;

// Single checkpoint
getlink('/dashboard', 'DashboardController@index', 'dashboard')
    ->checkpoint('auth');

// Multiple checkpoints in sequence
getlink('/admin/analytics', 'AdminController@analytics', 'admin.analytics')
    ->checkpoint(['auth', 'admin']);

// Route Group with Checkpoint and Prefix
grouplink(['prefix' => '/portal', 'checkpoint' => 'auth'], function () {
    getlink('/', 'PortalController@index', 'portal.home');
    getlink('/billing', 'PortalController@billing', 'portal.billing');
});
```

### 4. Scaffolding Checkpoints via CLI
```bash
srin add checkpoint VerifySubscription
```

---

## 🔐 Universal Authentication & Identity (`Core\Auth` & `Model\Account`)

Srin Engine includes an enterprise-grade, zero-dependency authentication manager (`Core\Auth`) with native password hashing, session fixation defense, and dynamic account models.

### 1. Configuration (`Config/auth.php`)
```php
return [
    'model' => Model\Account::class,
    'session_key' => 'srin_auth_account_id',
    'login_route' => 'auth.login',
    'checkpoints' => [
        'auth'  => Checkpoint\AuthCheckpoint::class,
        'guest' => Checkpoint\GuestCheckpoint::class,
        'admin' => Checkpoint\AdminCheckpoint::class,
    ],
];
```

### 2. Authenticating in Controllers
```php
namespace Controller;

use Core\BaseController;
use Core\Auth;
use Model\Account;

class AuthController extends BaseController
{
    public function login()
    {
        $data = $this->guard([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Attempt authentication (verifies password using password_verify)
        if (Auth::attempt($data['email'], $data['password'])) {
            // Automatically regenerates session ID to prevent session fixation attacks
            return $this->success('Welcome back, ' . Auth::user()->name . '!')
                ->go('dashboard');
        }

        return $this->fail('Invalid email or password.')->back();
    }

    public function logout()
    {
        Auth::logout();
        return $this->success('You have been signed out.')->go('home');
    }
}
```

### 3. Global Auth & Cryptographic Helpers
Use these helpers anywhere in controllers, checkpoints, or templates:
- `auth()` &rarr; Returns the authenticated `Model\Account` instance, or `null`.
- `auth_check()` &rarr; Returns boolean `true` if logged in, `false` otherwise.
- `auth_id()` &rarr; Returns primary key ID of the logged-in account.
- `auth_account()` &rarr; Returns the account model instance.
- `hash_password($password)` &rarr; Hashes a plain-text password using the powerful, memory-hard **Argon2id** algorithm (RFC 9106) with auto-peppering.
- `verify_password($password, $hash)` &rarr; Constant-time timing-attack safe verification of plain-text passwords against hashes with backward compatibility.
- `encrypt($value)` &rarr; Military-grade **AES-256-GCM** authenticated encryption with hardware acceleration (AEAD).
- `decrypt($payload)` &rarr; Decrypts and validates authentication tags, rejecting tampered ciphertext safely.

### 4. Advanced Password Hashing & Encryption Engine
Srin provides modern, lightweight, hardware-hardened cryptographic security out-of-the-box:
- **Argon2id (`PASSWORD_ARGON2ID`)**: Default password hashing algorithm; highly resistant to GPU/ASIC hardware brute-forcing.
- **Application Pepper**: Optional secret pepper (`AUTH_PEPPER`) via HMAC-SHA256 to ensure database dumps cannot be cracked offline.
- **Zero-Downtime Auto-Rehash**: `Auth::attempt()` automatically detects legacy Bcrypt or older parameter hashes and upgrades them seamlessly upon login.
- **Key Generation CLI**: Run `php srin key:generate` to generate a secure 256-bit `APP_KEY` in your `.env`.

---

## 📁 Native HTTP File & Image Uploads

Srin Engine provides native, safe file and image upload management right through `Core\Request` and `Core\BaseController` without any external dependencies.

### 1. Upload Form Markup
```html
<form action="[[ route('account.avatar') ]]" method="POST" enctype="multipart/form-data">
    [csrf]
    [honeypot]
    <input type="file" name="avatar" accept="image/*" required>
    <button type="submit">Upload Avatar</button>
</form>
```

### 2. Processing Uploads in Controllers
```php
namespace Controller;

use Core\BaseController;

class AccountController extends BaseController
{
    public function uploadAvatar()
    {
        $this->validateCsrf();

        // 1. Verify valid upload exists (checks UPLOAD_ERR_OK and non-empty tmp_name)
        if (!$this->hasFile('avatar')) {
            return $this->fail('Please select a file to upload.')->back();
        }

        // 2. Inspect file metadata
        $fileInfo = $this->file('avatar'); // ['name' => ..., 'type' => ..., 'size' => ...]

        // 3. Store file safely: enforces extension whitelist, generates cryptographic filename
        $savedPath = $this->storeFile('avatar', 'assets/uploads/avatars', ['jpg', 'jpeg', 'png', 'webp']);

        if (!$savedPath) {
            return $this->fail('Invalid file format. Only JPG, PNG, and WebP images are allowed.')->back();
        }

        // Update account record
        $account = auth_account();
        $account->avatar = $savedPath;
        $account->save();

        return $this->success('Profile avatar updated!')->back();
    }
}
```

### 3. Three-Tier Security Guarantee
- **Error Code Verification:** `hasFile()` ensures `$_FILES['key']['error'] === UPLOAD_ERR_OK` and verifies `tmp_name` is non-empty, preventing empty-upload bypasses.
- **Cryptographic Filename Hashing:** `storeFile()` generates `bin2hex(random_bytes(16))` filenames, completely defeating directory traversal (e.g. `../../etc/passwd`) and double-extension attacks (e.g. `malicious.php.jpg`).
- **Strict Extension Whitelisting:** Passing permitted extensions (e.g. `['jpg', 'png', 'webp']`) strictly verifies the extension before moving the temporary file to permanent storage.

---

## 📦 Composer Support (`Others/` Vendor Directory)

All third-party packages installed via Composer are organized into `Others/` rather than the traditional `vendor/` directory, keeping your project organized:

```bash
composer require vlucas/phpdotenv
```

Autoloading is handled automatically in `Others/autoload.php` and loaded in `index.php`.

---

## 🌐 Production Web Server Configuration (Nginx & Apache)

When deploying Srin Engine in production, never expose project internals (`.env`, `database/`, `Others/logs/`, `Config/`, `composer.json`, `composer.lock`) to the public web.

### 🔒 Document Root Recommendation
For highest security, production web servers should point their document root to a dedicated public directory (or enforce strict URL whitelisting), ensuring only `index.php` and static files in `assets/` are reachable by clients.

### 🚀 Nginx Configuration Example
On Nginx, configure your server block to deny hidden files, configuration files, database files, and internal directories, routing only static assets and `index.php`:

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/srin;

    index index.php;
    charset utf-8;

    # 1. Deny access to hidden/dot files (.env, .git, etc.)
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # 2. Deny access to secrets, database files, logs, and metadata
    location ~* \.(env|sqlite[0-9]?|db|log|lock|sql|bak|ini|dist|example|ya?ml)$ {
        deny all;
    }

    # 3. Deny direct access to framework internals and composer manifests
    location ~* ^/(composer\..*|srin$|database/|Others/logs/|Config/|Core/|Model/|Controller/|Pages/|Others/) {
        deny all;
    }

    # 4. Serve static assets directly from /assets/ (block scripts and dotfiles)
    location /assets/ {
        # Never execute script types inside assets/
        location ~* \.(php[0-9s]?|phtml|phar|cgi|pl|py|sh|inc|exe)$ {
            deny all;
        }

        # Block dotfiles inside assets/
        location ~* /\..* {
            deny all;
        }

        try_files $uri =404;
        access_log off;
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }

    # 5. Route all other requests through index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 6. Execute PHP via FastCGI ONLY for index.php
    location = /index.php {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny direct execution of all other PHP files
    location ~ \.php$ {
        deny all;
    }
}
```

### 🛡️ Apache Configuration
For Apache, `.htaccess` in the project root is pre-configured with `FilesMatch` and `mod_rewrite` rules to automatically deny dotfiles, database files (`.sqlite`), log files (`.log`), `composer.*`, and any server-side scripts under `assets/`, while routing all page requests to `index.php`.

---

## 🔄 One-Command System Upgrade (`php srin upgrade`)

Keep your Srin Engine up-to-date with a single command. Srin automatically checks the official GitHub repository for updates and safely upgrades the core framework code with overwrite:

```bash
# Check and upgrade default system code to the latest release:
php srin upgrade

# Force reinstall & overwrite default framework code with latest repository code:
php srin upgrade --force

# Check if an update is available without modifying any files:
php srin upgrade --check
```

### 🔒 Strict User Data Preservation Guarantee
`php srin upgrade` safely updates default framework files while **strictly preserving** your project work:

| Component | Upgrade Action | Purpose |
| :--- | :--- | :--- |
| `Core/` | **Overwritten** | Engine classes, ORM, router, security layers |
| `Config/` | **Overwritten** | Default config blueprints (new settings) |
| `Others/` | **Overwritten** | Vendor autoloader & Composer libraries |
| `srin`, `srin.php` | **Overwritten** | CLI binary & helper commands |
| `index.php`, `.htaccess` | **Overwritten** | Application gateway & Apache rewrites |
| `Pages/welcome.srin.php` | **Overwritten** | Documentation showcase & release notes |
| **`.env`** | **PRESERVED** | Your secrets, database keys & credentials |
| **`Model/`** | **PRESERVED** | Your custom database models |
| **`Controller/`** | **PRESERVED** | Your custom application controllers |
| **`database/`** | **PRESERVED** | Your migrations & SQLite database files |
| **`assets/`** | **PRESERVED** | Your stylesheets, scripts, images & fonts |
| **`Pages/`** (custom) | **PRESERVED** | Your custom templates, layouts & partials |
| **`.git/`** | **PRESERVED** | Your Git version history & branches |

---

## 👤 Author & Credits

- **Creator & Lead Architect:** **Arindam Makar**
- **GitHub:** [https://github.com/deadxfire](https://github.com/deadxfire)
- **Project:** **SRIN Engine** (`deadxfire/srin`)
- **License:** MIT License
