@Connect('layouts/docs')

<p class="doc-lead">
    Srin Engine features a versatile email subsystem powered by <strong>PHPMailer</strong> with fallback to native PHP mail and a local <strong>log driver</strong> that writes outgoing messages to disk during local development.
</p>

<h2>Mail Configuration</h2>
<p>
    Configure your email credentials in <code>Config/mail.php</code> and provide values via <code>.env</code>:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">.env</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code># Mail Driver: "smtp", "log", or "mail"
MAIL_DRIVER=smtp

# SMTP Credentials
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@your-domain.com
MAIL_PASSWORD=secret_smtp_password
MAIL_ENCRYPTION=tls

# Default Sender Identity
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Srin Engine Notification"</code></pre>
</div>

<div class="callout callout-tip">
    <div class="callout-title">Local Development with the 'log' Driver</div>
    <p>Set <code>MAIL_DRIVER=log</code> while developing locally. Instead of attempting to connect to external SMTP servers, outgoing emails are recorded directly into <code>Storage/logs/mail.log</code>, complete with headers, recipients, and HTML content.</p>
</div>

<h2>Sending an Email</h2>
<p>
    Use the <code>Core\Mailer::send()</code> method or the global <code>mail_send()</code> helper:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">PHP (Controller/ContactController.php)</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>namespace Controller;

use Core\BaseController;
use Core\Mailer;

class ContactController extends BaseController
{
    public function submit(): void
    {
        $this->validateCsrf();
        $this->validateHoneypot();
        $this->rateLimit('contact-form', maxAttempts: 3, decaySeconds: 120);

        $name    = $this->post('name');
        $email   = $this->post('email');
        $message = $this->post('message');

        $result = Mailer::send([
            'to'         => 'support@example.com',
            'subject'    => "New Inquiry from {$name}",
            'body'       => nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
            'reply_to'   => $email,
            'reply_name' => $name,
            'is_html'    => true,
        ]);

        if ($result['success']) {
            $this->flashSuccess('Your message has been sent successfully!')
                 ->back();
        } else {
            $this->flashError('Unable to send message: ' . $result['message'])
                 ->back();
        }
    }
}</code></pre>
</div>

<h2>Mailer Options Reference</h2>
<div class="docs-table">
    <table>
        <thead>
            <tr>
                <th>Option Key</th>
                <th>Type</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>to</code></td>
                <td><code>string</code></td>
                <td>Recipient email address (Required).</td>
            </tr>
            <tr>
                <td><code>subject</code></td>
                <td><code>string</code></td>
                <td>Email subject line.</td>
            </tr>
            <tr>
                <td><code>body</code></td>
                <td><code>string</code></td>
                <td>Body content (HTML or plain text).</td>
            </tr>
            <tr>
                <td><code>is_html</code></td>
                <td><code>bool</code></td>
                <td>Defaults to <code>true</code>. Set <code>false</code> for plain-text email.</td>
            </tr>
            <tr>
                <td><code>from</code></td>
                <td><code>string</code></td>
                <td>Sender email address (falls back to <code>MAIL_FROM_ADDRESS</code>).</td>
            </tr>
            <tr>
                <td><code>from_name</code></td>
                <td><code>string</code></td>
                <td>Sender display name (falls back to <code>MAIL_FROM_NAME</code>).</td>
            </tr>
            <tr>
                <td><code>reply_to</code></td>
                <td><code>string</code></td>
                <td>Optional Reply-To email address.</td>
            </tr>
            <tr>
                <td><code>reply_name</code></td>
                <td><code>string</code></td>
                <td>Optional Reply-To display name.</td>
            </tr>
        </tbody>
    </table>
</div>

<h2>CLI Scaffolding with <code>--mail</code></h2>
<p>
    Srin CLI can automatically scaffold contact controllers pre-wired with validation and mail sending logic:
</p>

<div class="code-block">
    <div class="code-header">
        <span class="code-lang">Bash</span>
        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
    </div>
    <pre><code>php srin make:controller Contact --mail</code></pre>
</div>
