@Connect('layouts/docs')

<p class="doc-lead">
    Real-world web applications depend on interconnected data. Srin Engine provides intuitive model relationships—including <strong>hasMany</strong>, <strong>belongsTo</strong>, and <strong>hasOne</strong>—coupled with eager loading to eliminate the infamous N+1 query performance bottleneck.
</p>

<h2>Defining Relationships</h2>

<h3>One to Many: <code>hasMany</code></h3>
<p>
    An author or post has many comments. Define this relation by returning <code>$this->hasMany()</code>:
</p>

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
     * Get all comments for this blog post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}</code></pre>
</div>

<h3>Inverse Relation: <code>belongsTo</code></h3>
<p>
    To navigate backward from a comment to its parent post or author, define a <code>belongsTo</code> relationship:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Model/Comment.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>namespace Model;

use Core\Model;

class Comment extends Model
{
    /**
     * Get the post that owns this comment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user author of this comment.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}</code></pre>
</div>

<h3>One to One: <code>hasOne</code></h3>
<p>
    For singular relations like a user profile or account settings:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Model/User.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>namespace Model;

use Core\Model;

class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}</code></pre>
</div>

<h2>Accessing Related Models</h2>
<p>
    Once defined, access related models as dynamic object properties:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>$post = Post::find(1);

// Access comments as array of Comment models
foreach ($post->comments as $comment) {
    echo $comment->content;
}

// Access parent model
$comment = Comment::find(5);
echo $comment->post->title;</code></pre>
</div>

<h2>Eager Loading: Solving the N+1 Query Problem</h2>
<p>
    When looping over 50 posts and reading each post's comments without eager loading, the application executes <strong>1 query for the posts + 50 queries for each comment set</strong> (51 database queries total).
</p>
<p>
    With Srin Engine's <code>with()</code> eager loading, this is reduced to <strong>just 2 queries</strong> regardless of whether you display 10 or 1,000 posts:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Executes exactly 2 optimized SQL queries:
// 1. SELECT * FROM posts WHERE is_published = 1
// 2. SELECT * FROM comments WHERE post_id IN (1, 2, 3, ...)
$posts = Post::where('is_published', true)
    ->with('comments')
    ->get();</code></pre>
</div>

<h3>Eager Loading Multiple Relations</h3>
<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>$posts = Post::with(['comments', 'author', 'tags'])->latest()->paginate(10);</code></pre>
</div>

<h3>Lazy Eager Loading: <code>load()</code></h3>
<p>
    If you already have a model instance and want to eager-load relations on demand:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>$post = Post::find(1);

if ($needComments) {
    $post->load('comments');
}</code></pre>
</div>
