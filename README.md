# SRIN Engine - Core PHP Static Website System

**SRIN** is a lightweight, high-performance, and ultra-secure **Core PHP static website engine** architected and developed by **Arindam Makar** ([@deadxfire](https://github.com/deadxfire)). Designed for blazing-fast static websites paired with dynamic essentials: mail transfer via SMTP or logs, native `.env` environment configuration, Composer packages in a custom `Others/` vendor directory, strict `.srin.php` templates with `@Connect` layouts, named routing, direct asset linking, automated error handling, and the dedicated `php srin` CLI tool.

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
│   └── mail.php            # Mail driver (smtp, log, mail) and SMTP credentials
│
├── Controller/             # Custom controllers (starts clean)
│   └── .gitkeep            # Clean slate ready for your controllers
│
├── Core/                   # Srin engine core logic
│   ├── App.php             # Application bootstrap, .env loader & unified error handler
│   ├── BaseController.php  # Base controller with render(), json(), redirect(), validate()
│   ├── Router.php          # URL routing engine (GET, POST, named routes, closures)
│   ├── Request.php         # HTTP request wrapper (inputs, sanitize, method, URI)
│   ├── Response.php        # HTTP response, redirects, and secure asset downloads
│   ├── View.php            # Strict .srin.php template renderer with @Connect
│   ├── Mailer.php          # Mail transfer service (SMTP / native mail / log driver)
│   └── Helper.php          # Global helpers: env(), view(), route(), config(), download(), asset()
│
├── Pages/                  # Frontend templates & layouts (.srin.php only)
│   ├── errors/             # Custom error pages (e.g. 404.srin.php, 500.srin.php)
│   │   └── 404.srin.php    # Automatic 404 error page
│   └── welcome.srin.php    # Default welcome & documentation page
│
├── Routes/                 # Application routes
│   └── web.php             # Clean route definitions with named routes
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

Like Laravel, Srin Engine enforces strict access boundaries and defense-in-depth protections to defeat common and advanced web attack vectors:

### 1. XSS (Cross-Site Scripting) Defense on `{{ }}`
All expressions evaluated inside `{{ $expression }}` are automatically sanitized using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. Any injected HTML or `<script>` tags are neutralized before reaching the browser. Raw unescaped output is strictly quarantined to `{!! $trustedHtml !!}`.

### 2. Bulletproof CSRF Protection for Forms
Every form submission is protected using cryptographically random session tokens via `@csrf` or `{{ csrf_field() }}`. Verified using timing-attack-safe `hash_equals()`; expired tokens automatically render a custom HTTP 419 error page with safe token regeneration.

### 3. Invisible Anti-Bot Honeypot (`@honeypot`)
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

### 7. Strict Template Extension (`.srin.php`)
Only `.srin.php` template files inside `Pages/` are executed. Standard `.php` templates (e.g. `Pages/portfolio.php`) are strictly rejected.

---

## 🎨 Running PHP in Templates: `{{ }}` Syntax & `@Connect`

Inside any `.srin.php` template, run PHP code and output data using `{{ }}`:

```html
<!-- Display Variables & Auto-Escaped Expressions (XSS-Safe) -->
<h1>{{ $title }}</h1>
<p>Today is {{ date('F j, Y') }}</p>
<p>Copyright &copy; {{ date('Y') }} {{ config('name') }}</p>

<!-- Form with Complete Anti-Hacking Protection -->
<form action="{{route('contact.send')}}" method="POST">
    @csrf       <!-- CSRF security token field -->
    @honeypot   <!-- Invisible anti-bot spam trap -->

    <input type="text" name="name" value="{{ old('name') }}" placeholder="Your Name" required>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Your Email" required>
    <textarea name="message" placeholder="Your Message">{{ old('message') }}</textarea>
    <button type="submit">Send Message</button>
</form>

<!-- Raw / Unescaped HTML (only when explicitly intended) -->
<div>{!! $rawHtml !!}</div>

<!-- Secure Template Comments (completely omitted from browser source) -->
{{-- This comment will never show up in browser inspect element --}}

<!-- Frontend JavaScript Escaping (Vue.js / Alpine.js / React) -->
<span>@{{ vueData }}</span> <!-- Compiles literally to {{ vueData }} -->
```

### Control Flow Directives:
```html
@if(count($items) > 0)
    <ul>
    @foreach($items as $item)
        <li>{{ $item['name'] }}</li>
    @endforeach
    </ul>
@else
    <p>No items found.</p>
@endif
```

---

## 🛠️ Srin CLI Generator Commands (`php srin`)

Run `php srin` (or `php srin.php`) to generate controllers, pages, layouts, and error templates:

```bash
# 1. Start development server
php srin serve
php srin serve --port=8080

# 2. Create a standard controller
php srin make:controller HomeController

# 3. Create a controller with mail transfer
php srin make:controller ContactController --mail

# 4. Create a new frontend page (.srin.php)
php srin make:page portfolio

# 5. Create a master layout
php srin make:layout admin

# 6. Create a custom error page (e.g., 403, 404, 500)
php srin make:error 404
php srin make:error 500

# 7. List all registered routes
php srin routes

# 8. Clear development mail logs
php srin clear:logs
```

---

## 🎨 Asset Linking with `{{assets('/path')}}`

There are **no default CSS or JS files** cluttering your project. You create your own stylesheets, scripts, and media folders inside `assets/`.

Link assets directly inside `.srin.php` templates:

```html
<!-- Stylesheet -->
<link rel="stylesheet" href="{{assets('/css/style.css')}}">

<!-- JavaScript -->
<script src="{{assets('/js/main.js')}}"></script>

<!-- Images (supports nested folders) -->
<img src="{{assets('/images/logo.png')}}" alt="Logo">
<img src="{{assets('/images/portfolio/item-1.jpg')}}" alt="Project">

<!-- Custom Asset Folders -->
<link rel="preload" href="{{assets('/fonts/inter.woff2')}}" as="font">
<img src="{{assets('/icons/check.svg')}}" alt="Check">
```

> **Subfolder & Production Safe:** `{{assets('/path')}}` automatically detects whether your site is on a root domain (`https://mysite.com`) or inside a server subfolder (`https://mysite.com/subfolder/`) and generates the exact absolute path.

---

## 🎨 Strict Templates & Master Layouts (`@Connect`)

### 1. Child Page (`Pages/portfolio.srin.php`):
```php
@Connect('layouts/main')
@title('My Portfolio')

<section class="portfolio">
    <h1>My Creative Work</h1>
    <p>Rendered securely through Srin Engine.</p>
</section>
```

### 2. Master Layout (`Pages/layouts/main.srin.php`):
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? config('name') ?></title>
    <link rel="stylesheet" href="{{assets('/css/style.css')}}">
</head>
<body>
    <header>
        <nav>
            <a href="{{route('home')}}">Home</a>
            <a href="{{route('portfolio')}}">Portfolio</a>
            <a href="{{route('contact')}}">Contact</a>
        </nav>
    </header>

    <main class="container">
        @flash('success')
        @flash('error')
        @content
    </main>

    <script src="{{assets('/js/main.js')}}"></script>
</body>
</html>
```

---

## 🛣️ Named Routing in `Routes/web.php`

Assign names to your routes using `->name('name')`:

```php
use Core\Router;

// Welcome Page
Router::get('/', function() {
    return view('welcome');
})->name('home');

// Controller Routes with Names
Router::get('/portfolio', 'PortfolioController@index')->name('portfolio');
Router::get('/contact', 'ContactController@index')->name('contact');
Router::post('/contact', 'ContactController@sendMail')->name('contact.send');

// Route with dynamic parameters
Router::get('/blog/{slug}', 'BlogController@show')->name('blog.show');
```

### Linking with Route Names in Templates:
```html
<a href="{{route('home')}}">Home</a>
<a href="{{route('portfolio')}}">Portfolio</a>
<a href="{{route('contact')}}">Contact Us</a>

<!-- With Parameters -->
<a href="{{route('blog.show', ['slug' => 'my-first-post'])}}">Read Article</a>
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
php srin make:error 404
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
php srin make:controller ContactController --mail
```

---

## 📦 Composer Support (`Others/` Vendor Directory)

All third-party packages installed via Composer are organized into `Others/` rather than the traditional `vendor/` directory, keeping your project organized:

```bash
composer require vlucas/phpdotenv
```

Autoloading is handled automatically in `Others/autoload.php` and loaded in `index.php`.

---

## 👤 Author & Credits

- **Creator & Lead Architect:** **Arindam Makar**
- **GitHub:** [https://github.com/deadxfire](https://github.com/deadxfire)
- **Project:** **SRIN Engine** (`deadxfire/srin`)
- **License:** MIT License
