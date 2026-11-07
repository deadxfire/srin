@Connect('layouts/docs')

<p class="doc-lead">
    Srin Engine features an elegant <strong>ActiveRecord ORM</strong> implementation. Every database table maps directly to a Model class, allowing you to query, insert, update, and manage records with clean, expressive object-oriented syntax.
</p>

<h2>Defining a Model</h2>
<p>
    Generate a new model using the Srin CLI:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code># Generate model
php srin make:model Post

# Or generate model with its schema migration
php srin make:model Post -m</code></pre>
</div>

<p>Here is a typical Srin Model definition located in <code>Model/Post.php</code>:</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Model/Post.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>namespace Model;

use Core\Model;

class Post extends Model
{
    /**
     * The table associated with the model (optional).
     * If omitted, Srin pluralizes the class name (e.g. 'posts').
     */
    protected string $table = 'posts';

    /**
     * The primary key column name (defaults to 'id').
     */
    protected string $primaryKey = 'id';

    /**
     * The attributes that are mass-assignable.
     */
    protected array $fillable = [
        'title',
        'slug',
        'category',
        'content',
        'is_published',
    ];

    /**
     * Indicates if the model should be timestamped.
     * Automatically sets created_at and updated_at.
     */
    protected bool $timestamps = true;
}</code></pre>
</div>

<h2>Creating Records</h2>
<p>
    Insert new rows into the database using <code>create()</code> or by instantiating the model and calling <code>save()</code>:
</p>

<h3>Method 1: Mass Assignment via <code>create()</code></h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>$post = Post::create([
    'title'        => 'Mastering Srin Engine',
    'slug'         => 'mastering-srin-engine',
    'category'     => 'development',
    'content'      => 'A deep dive into Srin Engine superpowers.',
    'is_published' => true,
]);

// Access newly generated ID
echo $post->id;</code></pre>
</div>

<h3>Method 2: Instantiation &amp; <code>save()</code></h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>$post = new Post();
$post->title = 'Another Great Article';
$post->category = 'tutorials';
$post->save();</code></pre>
</div>

<h2>Reading &amp; Querying Records</h2>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Find a record by its primary key
$post = Post::find(1);

// Find or abort with 404 error if not found
$post = Post::findOrFail(99);

// Retrieve all records in the table
$allPosts = Post::all();

// Query with constraints
$popular = Post::where('views', '>', 1000)
    ->where('is_published', true)
    ->latest()
    ->get();

// Find the first matching record
$article = Post::where('slug', 'my-post')->first();

// First or Create
$tag = Tag::firstOrCreate(['name' => 'php']);</code></pre>
</div>

<h2>Updating Records</h2>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Find and mutate properties
$post = Post::find(1);
$post->title = 'Updated Title';
$post->views += 1;
$post->save();

// Or mass update using an array
$post->update([
    'category'     => 'news',
    'is_published' => false,
]);</code></pre>
</div>

<h2>Deleting Records</h2>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Delete an existing model instance
$post = Post::find(1);
$post->delete();

// Or delete by primary key directly
Post::destroy(42);

// Delete records via query
Post::where('is_published', false)->delete();</code></pre>
</div>

<h2>Serialization: Array &amp; JSON</h2>
<p>
    Easily convert models to native PHP arrays or JSON strings for API responses:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>$post = Post::find(1);

// Convert to associative array
$arrayData = $post->toArray();

// Convert to JSON string
$jsonString = $post->toJson();</code></pre>
</div>
