@Connect('layouts/docs')

<p class="doc-lead">
    Migrations are version control for your database, allowing your team to define and share the application's database schema definition across SQLite, MySQL, and PostgreSQL.
</p>

<h2>Creating a Migration</h2>
<p>
    Generate a new migration using the Srin CLI:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code># Create migration to create a new 'posts' table
php srin make:migration create_posts_table --create=posts

# Or generate a model with its migration simultaneously
php srin make:model Post -m</code></pre>
</div>

<p>
    This creates a timestamped migration file in <code>Database/Migrations/</code>:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Database/Migrations/2026_xx_xx_create_posts_table.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>use Core\Migration;
use Core\Schema;
use Core\Blueprint;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('general');
            $table->text('content');
            $table->boolean('is_published')->default(false);
            $table->timestamps(); // creates created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}</code></pre>
</div>

<h2>Available Column Types</h2>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Blueprint Method</th>
                <th>SQL Type (MySQL / SQLite / PgSQL)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>$table->id()</code></td>
                <td>Auto-incrementing Primary Key (BIGINT / INTEGER / BIGSERIAL).</td>
            </tr>
            <tr>
                <td><code>$table->string('name', 150)</code></td>
                <td>VARCHAR with customizable length (defaults to 255).</td>
            </tr>
            <tr>
                <td><code>$table->text('bio')</code></td>
                <td>TEXT column for long-form strings or markdown.</td>
            </tr>
            <tr>
                <td><code>$table->integer('votes')</code></td>
                <td>Standard signed 32-bit INTEGER.</td>
            </tr>
            <tr>
                <td><code>$table->bigInteger('user_id')</code></td>
                <td>64-bit BIGINT (great for foreign keys).</td>
            </tr>
            <tr>
                <td><code>$table->boolean('is_active')</code></td>
                <td>BOOLEAN / TINYINT(1).</td>
            </tr>
            <tr>
                <td><code>$table->float('rating')</code></td>
                <td>Floating-point number.</td>
            </tr>
            <tr>
                <td><code>$table->decimal('price', 10, 2)</code></td>
                <td>Exact DECIMAL with precision and scale.</td>
            </tr>
            <tr>
                <td><code>$table->date('published_on')</code></td>
                <td>DATE column (YYYY-MM-DD).</td>
            </tr>
            <tr>
                <td><code>$table->dateTime('expired_at')</code></td>
                <td>DATETIME / TIMESTAMP column.</td>
            </tr>
            <tr>
                <td><code>$table->timestamps()</code></td>
                <td>Adds both <code>created_at</code> and <code>updated_at</code> nullable timestamps.</td>
            </tr>
        </tbody>
    </table>
</div>

<h2>Column Modifiers</h2>
<p>
    Chain modifiers to refine column constraints and default states:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Allow NULL values
$table->string('subtitle')->nullable();

// Enforce unique constraint
$table->string('email')->unique();

// Assign default literal value
$table->string('status')->default('pending');
$table->boolean('is_featured')->default(false);
$table->integer('views')->default(0);</code></pre>
</div>

<h2>Executing Migrations</h2>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code># Run all pending migrations
php srin migrate

# Check status of executed vs pending migrations
php srin migrate:status

# Rollback the last migration batch
php srin migrate:rollback

# Wipe the database and re-run all migrations from scratch
php srin migrate:fresh</code></pre>
</div>
