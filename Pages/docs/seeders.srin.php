@Connect('layouts/docs')

<p class="doc-lead">
    Database Seeders allow you to populate your database tables with dummy or default records for local development, automated testing, and initial application setup.
</p>

<h2>Creating a Seeder</h2>
<p>
    Scaffold a new seeder using the Srin CLI:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>php srin make:seeder PostSeeder</code></pre>
</div>

<p>
    This generates a seeder class in <code>Database/Seeders/PostSeeder.php</code>:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Database/Seeders/PostSeeder.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>namespace Database\Seeders;

use Core\Seeder;
use Model\Post;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $samplePosts = [
            [
                'title'        => 'Welcome to Srin Engine',
                'slug'         => 'welcome-to-srin-engine',
                'category'     => 'development',
                'content'      => 'Srin Engine makes PHP web development clean, fast, and fun.',
                'is_published' => true,
            ],
            [
                'title'        => 'Building High Performance APIs',
                'slug'         => 'building-high-performance-apis',
                'category'     => 'architecture',
                'content'      => 'Learn how to build lightweight endpoints with zero bloat.',
                'is_published' => true,
            ],
            [
                'title'        => 'Mastering Database Relationships',
                'slug'         => 'mastering-database-relationships',
                'category'     => 'tutorials',
                'content'      => 'Eliminate N+1 queries using eager loading in Srin Engine.',
                'is_published' => true,
            ],
        ];

        foreach ($samplePosts as $postData) {
            Post::firstOrCreate(['slug' => $postData['slug']], $postData);
        }
    }
}</code></pre>
</div>

<h2>The Master DatabaseSeeder</h2>
<p>
    Srin uses <code>Database\Seeders\DatabaseSeeder</code> as the root entry point. You can orchestrate multiple child seeders using the <code>$this->call()</code> method:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Database/Seeders/DatabaseSeeder.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>namespace Database\Seeders;

use Core\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            PostSeeder::class,
        ]);
    }
}</code></pre>
</div>

<h2>Running Seeders via CLI</h2>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code># Run the default DatabaseSeeder
php srin db:seed

# Run a specific seeder class
php srin db:seed --class=PostSeeder

# Fresh migration + seeding in one step
php srin migrate:fresh --seed</code></pre>
</div>
