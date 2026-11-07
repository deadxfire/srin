@Connect('layouts/docs')

<p class="doc-lead">
    Security is not an afterthought in Srin Engine—it is woven directly into every layer of the core. With automatic CSRF tokens, anti-spam honeypot traps, built-in rate limiting, null-byte sanitization, and mass-assignment protection, your application is hardened by default.
</p>

<h2>The 7 Pillars of Srin Security</h2>
<div class="feature-grid">
    <div class="feature-card">
        <div class="feature-icon">&#128272;</div>
        <div class="feature-title">CSRF Protection</div>
        <div class="feature-desc">Cryptographically secure session tokens validated using timing-attack safe <code>hash_equals()</code>.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#128029;</div>
        <div class="feature-title">Anti-Bot Honeypot</div>
        <div class="feature-desc">Transparent anti-spam traps that trick automated bots without annoying human users with CAPTCHAs.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#9201;</div>
        <div class="feature-title">Rate Limiting</div>
        <div class="feature-desc">IP-based throttling protecting login forms and sensitive API endpoints against brute-force attacks.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#128737;</div>
        <div class="feature-title">XSS Neutralization</div>
        <div class="feature-desc">Automatic HTML entity escaping via <code>@{{ $var }}</code> preventing malicious script injection.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#128273;</div>
        <div class="feature-title">Mass Assignment Defense</div>
        <div class="feature-desc">Model-level <code>$fillable</code> whitelists and controller-level <code>$this->onlyInputs()</code>.</div>
    </div>

    <div class="feature-card">
        <div class="feature-icon">&#128683;</div>
        <div class="feature-title">Injection Protection</div>
        <div class="feature-desc">Automatic stripping of null bytes (<code>\0</code>), control characters, and CRLF header splitting.</div>
    </div>
</div>

<h2>1. Cross-Site Request Forgery (CSRF)</h2>
<p>
    Every POST, PUT, or DELETE form should include the <code>@csrf</code> directive. This embeds a hidden token validated against the user's active session:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>&lt;form method="POST" action="@{{route('contact.submit')}}"&gt;
    @@csrf
    &lt;input type="text" name="name" required&gt;
    &lt;button type="submit"&gt;Send&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<p>In your controller, verify the token with one method call:</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Controller)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>public function submit(): void
{
    // Aborts with HTTP 419 Page Expired if missing or invalid
    $this->validateCsrf();

    // Process safe form...
}</code></pre>
</div>

<h3>AJAX &amp; JavaScript CSRF Headers</h3>
<p>
    When making asynchronous JavaScript Fetch or Axios requests, pass the CSRF token in the request headers:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">JavaScript</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>fetch('/api/cart/add', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '&lt;?= csrf_token() ?&gt;'
    },
    body: JSON.stringify({ item_id: 42 })
});</code></pre>
</div>

<h2>2. Anti-Bot Honeypot Traps</h2>
<p>
    CAPTCHAs create friction for genuine users and hurt conversion rates. Srin Engine features a built-in <strong>Honeypot Trap</strong>:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">HTML / Srin Template</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>&lt;form method="POST" action="@{{route('newsletter.signup')}}"&gt;
    @@csrf
    @@honeypot

    &lt;input type="email" name="email" placeholder="Your email" required&gt;
    &lt;button type="submit"&gt;Subscribe&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<p>
    The <code>@@honeypot</code> directive inserts an invisible input field styled off-screen. Real human users cannot see it and leave it empty. Automated spam scripts blindly fill all inputs. Validate it in your controller:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// Aborts with HTTP 400 Bad Request if field contains content
$this->validateHoneypot();</code></pre>
</div>

<h2>3. Rate Limiting (Throttle)</h2>
<p>
    Prevent brute-force password cracking and form spam by throttling requests based on client IP:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>public function login(): void
{
    // Limit to 5 attempts per 60 seconds
    $this->rateLimit('login-attempt', maxAttempts: 5, decaySeconds: 60);

    // Continue authentication...
}</code></pre>
</div>

<div class="callout callout-warning">
    <div class="callout-title">Automatic 429 Too Many Requests</div>
    <p>If the user exceeds the specified attempt threshold within the decay window, Srin halts execution immediately and renders <code>Pages/errors/429.srin.php</code>.</p>
</div>

<h2>4. Mass Assignment Whitelisting</h2>
<p>
    Never pass unfiltered raw request arrays directly to model creation or updates:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>// VULNERABLE: A malicious user could send 'is_admin' => 1
// User::create($this->allInputs());

// SECURE: Whitelist only permitted attributes
$safeData = $this->onlyInputs(['name', 'email', 'bio']);
User::create($safeData);</code></pre>
</div>
