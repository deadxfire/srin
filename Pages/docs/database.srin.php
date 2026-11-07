@Connect('layouts/docs')

<p class="doc-lead">
    Srin Engine provides a unified database abstraction layer built directly on top of native PHP PDO. It supports SQLite, MySQL / MariaDB, and PostgreSQL out of the box, with automatic connection pooling, transaction isolation, and a fluent query gateway.
</p>

<h2>Supported Drivers &amp; Configuration</h2>
<p>
    Database options are configured in <code>Config/database.php</code> and customized via environment variables in <code>.env</code>.
</p>

<h3>1. SQLite Configuration</h3>
<p>
    SQLite is the default driver—ideal for rapid local prototyping, testing, and embedded microservices without requiring a separate database server daemon:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">.env</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>DB_CONNECTION=sqlite
DB_DATABASE=Storage/database.sqlite</code></pre>
</div>

<div class="callout callout-info">
    <div class="callout-title">Auto-Initialization</div>
    <p>If the SQLite database file does not exist, Srin automatically creates the file and directory with <code>PRAGMA foreign_keys = ON;</code> enabled.</p>
</div>

<h3>2. MySQL / MariaDB Configuration</h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">.env</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=srin_app
DB_USERNAME=srin_user
DB_PASSWORD=secret_password
DB_CHARSET=utf8mb4</code></pre>
</div>

<h3>3. PostgreSQL Configuration</h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">.env</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=srin_app
DB_USERNAME=postgres
DB_PASSWORD=secret_password</code></pre>
</div>

<h2>The Unified <code>db()</code> Gateway Helper</h2>
<p>
    Srin features a dual-purpose <code>db()</code> helper function that eliminates verbose namespace imports:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// 1. Pass a table name to start a QueryBuilder instance:
$users = db('users')->where('status', 'active')->get();

// 2. Call without arguments to execute raw SQL / Database manager commands:
$count = db()->scalar("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);</code></pre>
</div>

<h2>Executing Raw SQL Queries</h2>
<p>
    When you need raw SQL performance or vendor-specific features, Srin provides clean prepared statements with automatic parameter binding:
</p>

<h3>Selecting Multiple Rows</h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>use Core\Database;

// Returns array of associative arrays
$users = Database::select("SELECT id, name, email FROM users WHERE role = ? ORDER BY created_at DESC", ['admin']);</code></pre>
</div>

<h3>Selecting a Single Row</h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Returns associative array or null if not found
$user = Database::selectOne("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);</code></pre>
</div>

<h3>Retrieving a Single Scalar Value</h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Returns direct scalar value (e.g. integer or string)
$totalRevenue = Database::scalar("SELECT SUM(total_price) FROM orders WHERE status = ?", ['paid']);</code></pre>
</div>

<h3>Inserts, Updates &amp; Deletes</h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Insert returns last insert ID
$newId = Database::insert(
    "INSERT INTO logs (level, message, created_at) VALUES (?, ?, ?)",
    ['info', 'User signed in', date('Y-m-d H:i:s')]
);

// Update returns number of affected rows
$affected = Database::update(
    "UPDATE users SET last_login = ? WHERE id = ?",
    [date('Y-m-d H:i:s'), 42]
);

// Delete returns number of deleted rows
$deleted = Database::delete("DELETE FROM sessions WHERE expired_at < ?", [time()]);</code></pre>
</div>

<h2>Database Transactions</h2>
<p>
    Srin makes ACID transactions safe and simple with the <code>Database::transaction()</code> method. If an exception is thrown inside the closure, the transaction is automatically rolled back:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>use Core\Database;

Database::transaction(function ($pdo) {
    // Deduct balance from sender
    Database::update("UPDATE accounts SET balance = balance - ? WHERE id = ?", [100, $senderId]);

    // Credit balance to recipient
    Database::update("UPDATE accounts SET balance = balance + ? WHERE id = ?", [100, $recipientId]);

    // Record audit ledger entry
    Database::insert("INSERT INTO transfers (sender_id, recipient_id, amount) VALUES (?, ?, ?)", [
        $senderId, $recipientId, 100
    ]);
});</code></pre>
</div>

<div class="callout callout-tip">
    <div class="callout-title">Accessing the Native PDO Object</div>
    <p>Need access to underlying PDO driver features or attributes? Call <code>Database::connection()</code> or <code>Database::connection('mysql')</code> to retrieve the active <code>\PDO</code> instance directly.</p>
</div>
