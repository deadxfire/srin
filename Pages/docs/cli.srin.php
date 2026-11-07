@Connect('layouts/docs')

<p class="doc-lead">
    The <strong>Srin CLI</strong> (<code>srin</code>) is your command-line companion for scaffolding architecture, executing database migrations, inspecting registered routes, and running the local development server.
</p>

<h2>Overview &amp; Usage</h2>
<p>
    You can invoke the Srin CLI from the root of your project using <code>php srin &lt;command&gt; [options]</code>. To display the full interactive command list at any time, run:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>php srin help</code></pre>
</div>

<h2>Command Matrix</h2>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Command</th>
                <th>Arguments / Flags</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>serve</code></td>
                <td><code>--port=8000</code>, <code>--host=127.0.0.1</code></td>
                <td>Starts the embedded PHP development web server.</td>
            </tr>
            <tr>
                <td><code>routes</code></td>
                <td>None</td>
                <td>Displays a clean formatted table of all registered routes, HTTP methods, and handlers.</td>
            </tr>
            <tr>
                <td><code>make:controller</code></td>
                <td><code>&lt;Name&gt; [--mail]</code></td>
                <td>Generates a new controller inside <code>Controller/</code> extending <code>BaseController</code>.</td>
            </tr>
            <tr>
                <td><code>make:model</code></td>
                <td><code>&lt;Name&gt; [-m]</code></td>
                <td>Generates a new ActiveRecord model in <code>Model/</code> with fillable attributes. <code>-m</code> flag auto-creates a paired migration.</td>
            </tr>
            <tr>
                <td><code>make:migration</code></td>
                <td><code>&lt;name&gt; [--create=table]</code></td>
                <td>Generates a timestamped schema migration file in <code>Database/Migrations/</code>.</td>
            </tr>
            <tr>
                <td><code>migrate</code></td>
                <td>None</td>
                <td>Executes all pending database migrations in chronological order.</td>
            </tr>
            <tr>
                <td><code>migrate:rollback</code></td>
                <td>None</td>
                <td>Rolls back the last executed batch of database migrations.</td>
            </tr>
            <tr>
                <td><code>migrate:status</code></td>
                <td>None</td>
                <td>Shows migration status: ran vs pending migrations.</td>
            </tr>
            <tr>
                <td><code>migrate:fresh</code></td>
                <td><code>[--seed]</code></td>
                <td>Drops all tables and re-executes all migrations from scratch. Optionally runs seeders.</td>
            </tr>
            <tr>
                <td><code>make:seeder</code></td>
                <td><code>&lt;Name&gt;</code></td>
                <td>Creates a new database seeder class in <code>Database/Seeders/</code>.</td>
            </tr>
            <tr>
                <td><code>db:seed</code></td>
                <td><code>[--class=Name]</code></td>
                <td>Runs database seeders to populate initial or test data.</td>
            </tr>
            <tr>
                <td><code>make:page</code></td>
                <td><code>&lt;name&gt;</code></td>
                <td>Generates a new view template in <code>Pages/</code> with <code>@Connect('layouts/app')</code>.</td>
            </tr>
            <tr>
                <td><code>make:layout</code></td>
                <td><code>&lt;name&gt;</code></td>
                <td>Creates a new master layout file in <code>Pages/layouts/</code>.</td>
            </tr>
            <tr>
                <td><code>make:partial</code></td>
                <td><code>&lt;name&gt;</code></td>
                <td>Creates a reusable partial component in <code>Pages/partials/</code>.</td>
            </tr>
            <tr>
                <td><code>make:error</code></td>
                <td><code>&lt;status_code&gt;</code></td>
                <td>Scaffolds a customized HTTP error view (e.g. 403, 404, 500) in <code>Pages/errors/</code>.</td>
            </tr>
            <tr>
                <td><code>clear:logs</code></td>
                <td>None</td>
                <td>Flushes error and debug logs in <code>Storage/logs/</code>.</td>
            </tr>
        </tbody>
    </table>
</div>

<h2>Deep Dive: Generating Controllers</h2>
<p>
    When creating a controller, Srin automatically appends the <code>Controller</code> suffix if omitted and scaffolds standard CRUD actions:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code># Generates Controller/ArticleController.php
php srin make:controller Article

# Scaffold a contact controller pre-configured with PHPMailer logic
php srin make:controller Contact --mail</code></pre>
</div>

<h2>Deep Dive: Generating Models &amp; Migrations</h2>
<p>
    You can generate an ActiveRecord model and its database table migration simultaneously using the <code>-m</code> flag:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code># Generates Model/Product.php and Database/Migrations/2026_xx_xx_create_products_table.php
php srin make:model Product -m</code></pre>
</div>

<h2>Inspecting Registered Routes</h2>
<p>
    Ever wonder what routes are currently active or which controller action handles an endpoint? Use <code>php srin routes</code> to print an ASCII table:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>php srin routes

+--------+------------------+--------------------+-----------------------------+
| Method | URI              | Name               | Action                      |
+--------+------------------+--------------------+-----------------------------+
| GET    | /                | home               | HomeController@index        |
| GET    | /docs            | docs               | DocsController@index        |
| GET    | /docs/{topic}    | docs.show          | DocsController@show         |
| GET    | /blog            | blog.index         | BlogController@index        |
| POST   | /contact         | contact.submit     | ContactController@submit    |
+--------+------------------+--------------------+-----------------------------+</code></pre>
</div>

<h2>Database Migration Management</h2>
<p>
    Execute, rollback, and check migration progress seamlessly:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code># Run all pending migrations
php srin migrate

# Check migration history
php srin migrate:status

# Rollback the most recent migration batch
php srin migrate:rollback

# Wipe database, rerun all migrations, and seed fresh test data
php srin migrate:fresh --seed</code></pre>
</div>
