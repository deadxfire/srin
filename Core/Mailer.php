<?php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Throwable;

class Mailer
{
    /**
     * Send an email.
     *
     * @param array $options [
     *     'to'       => 'recipient@example.com',
     *     'subject'  => 'Email Subject',
     *     'body'     => 'Email content or HTML',
     *     'is_html'  => true|false (optional, default: true),
     *     'from'     => 'custom@example.com' (optional),
     *     'from_name'=> 'Custom Name' (optional),
     *     'reply_to' => 'user@example.com' (optional),
     *     'reply_name'=> 'User Name' (optional)
     * ]
     * @return array ['success' => bool, 'message' => string]
     */
    public static function send(array $options): array
    {
        $driver = config('mail.driver', 'log');

        $to = Security::singleLine((string) ($options['to'] ?? config('mail.admin_email', '')));
        $subject = Security::singleLine((string) ($options['subject'] ?? 'Notification'));
        $body = $options['body'] ?? ($options['message'] ?? '');
        $isHtml = $options['is_html'] ?? true;
        $fromEmail = Security::singleLine((string) ($options['from'] ?? config('mail.from.address', 'noreply@example.com')));
        $fromName = trim(str_replace(['<', '>', '"'], '', Security::singleLine((string) ($options['from_name'] ?? config('mail.from.name', 'My Website')))));
        $replyTo = $options['reply_to'] ?? null;
        $replyName = trim(str_replace(['<', '>', '"'], '', Security::singleLine((string) ($options['reply_name'] ?? ''))));

        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Recipient email (to) is invalid or required.'];
        }

        if (empty($fromEmail) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Sender email (from) is invalid.'];
        }

        if ($replyTo !== null && trim((string) $replyTo) !== '') {
            $rawReplyTo = (string) $replyTo;
            if (str_contains($rawReplyTo, "\r") || str_contains($rawReplyTo, "\n") || !filter_var(trim($rawReplyTo), FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Reply-to email is invalid.'];
            }
            $replyTo = Security::singleLine($rawReplyTo);
            if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Reply-to email is invalid.'];
            }
        } else {
            $replyTo = null;
        }

        // Driver: 'log' - for local testing without mail server
        if ($driver === 'log') {
            return self::sendViaLog($to, $subject, $body, $fromEmail, $fromName, $replyTo, $replyName);
        }

        // Driver: 'mail' - native PHP mail()
        if ($driver === 'mail') {
            return self::sendViaNativeMail($to, $subject, $body, $fromEmail, $fromName, $replyTo, $isHtml, $replyName);
        }

        // Driver: 'smtp' - via Composer PHPMailer
        if (class_exists(PHPMailer::class)) {
            return self::sendViaPHPMailer($to, $subject, $body, $fromEmail, $fromName, $replyTo, $replyName, $isHtml);
        }

        // Fallback to native mail if PHPMailer class is not loaded
        return self::sendViaNativeMail($to, $subject, $body, $fromEmail, $fromName, $replyTo, $isHtml, $replyName);
    }

    /**
     * Send email via PHPMailer.
     */
    protected static function sendViaPHPMailer(
        string $to,
        string $subject,
        string $body,
        string $fromEmail,
        string $fromName,
        ?string $replyTo,
        string $replyName,
        bool $isHtml
    ): array {
        $to        = Security::singleLine($to);
        $fromEmail = Security::singleLine($fromEmail);
        $fromName  = trim(str_replace(['<', '>', '"'], '', Security::singleLine($fromName)));
        $subject   = Security::singleLine($subject);
        $replyName = trim(str_replace(['<', '>', '"'], '', Security::singleLine($replyName)));

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid recipient email address (to).'];
        }
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid sender email address (from).'];
        }

        if ($replyTo !== null && trim($replyTo) !== '') {
            $rawReplyTo = (string) $replyTo;
            if (str_contains($rawReplyTo, "\r") || str_contains($rawReplyTo, "\n") || !filter_var(trim($rawReplyTo), FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Reply-to email is invalid.'];
            }
            $replyTo = Security::singleLine($rawReplyTo);
            if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid reply-to email address.'];
            }
        } else {
            $replyTo = null;
        }

        $mail = new PHPMailer(true);

        try {
            $smtpConfig = config('mail.smtp', []);

            $mail->isSMTP();
            $mail->Host       = $smtpConfig['host'] ?? 'localhost';
            $mail->SMTPAuth   = !empty($smtpConfig['username']);
            $mail->Username   = $smtpConfig['username'] ?? '';
            $mail->Password   = $smtpConfig['password'] ?? '';
            $mail->Port       = (int) ($smtpConfig['port'] ?? 587);
            $mail->Timeout    = (int) ($smtpConfig['timeout'] ?? 10);

            $encryption = strtolower((string) ($smtpConfig['encryption'] ?? ''));
            if ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }

            // Sender & Recipient
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);

            if ($replyTo) {
                $mail->addReplyTo($replyTo, $replyName);
            }

            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            if ($isHtml) {
                $mail->AltBody = strip_tags($body);
            }

            $mail->send();
            return ['success' => true, 'message' => 'Email sent successfully via SMTP.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo . ' (' . $e->getMessage() . ')'];
        }
    }

    /**
     * Send email via native PHP mail() function.
     */
    protected static function sendViaNativeMail(
        string $to,
        string $subject,
        string $body,
        string $fromEmail,
        string $fromName,
        ?string $replyTo,
        bool $isHtml,
        string $replyName = ''
    ): array {
        // Sanitize and validate header fields against CRLF injection / Bcc injection
        $to        = Security::singleLine($to);
        $fromEmail = Security::singleLine($fromEmail);
        $fromName  = trim(str_replace(['<', '>', '"'], '', Security::singleLine($fromName)));
        $subject   = Security::singleLine($subject);
        $replyName = trim(str_replace(['<', '>', '"'], '', Security::singleLine($replyName)));

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid recipient email address (to).'];
        }
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid sender email address (from).'];
        }

        if ($replyTo !== null && trim($replyTo) !== '') {
            $rawReplyTo = (string) $replyTo;
            if (str_contains($rawReplyTo, "\r") || str_contains($rawReplyTo, "\n") || !filter_var(trim($rawReplyTo), FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid reply-to email address.'];
            }
            $replyTo = Security::singleLine($rawReplyTo);
            if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid reply-to email address.'];
            }
        } else {
            $replyTo = null;
        }

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = $isHtml ? 'Content-type: text/html; charset=UTF-8' : 'Content-type: text/plain; charset=UTF-8';
        $headers[] = $fromName !== '' ? "From: {$fromName} <{$fromEmail}>" : "From: <{$fromEmail}>";

        if ($replyTo !== null) {
            $headers[] = $replyName !== '' ? "Reply-To: {$replyName} <{$replyTo}>" : "Reply-To: <{$replyTo}>";
        }

        $headers[] = 'X-Mailer: PHP/' . phpversion();

        $success = @mail($to, $subject, $body, implode("\r\n", $headers));

        if ($success) {
            return ['success' => true, 'message' => 'Email sent successfully via native PHP mail.'];
        }

        return ['success' => false, 'message' => 'Native PHP mail() failed to dispatch the message.'];
    }

    /**
     * Local testing log driver.
     */
    protected static function sendViaLog(
        string $to,
        string $subject,
        string $body,
        string $fromEmail,
        string $fromName,
        ?string $replyTo,
        string $replyName = ''
    ): array {
        // Sanitize and validate header values against log injection
        $to        = Security::singleLine($to);
        $fromEmail = Security::singleLine($fromEmail);
        $fromName  = trim(str_replace(['<', '>', '"'], '', Security::singleLine($fromName)));
        $subject   = Security::singleLine($subject);
        $replyName = trim(str_replace(['<', '>', '"'], '', Security::singleLine($replyName)));

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid recipient email address (to).'];
        }
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid sender email address (from).'];
        }

        if ($replyTo !== null && trim($replyTo) !== '') {
            $rawReplyTo = (string) $replyTo;
            if (str_contains($rawReplyTo, "\r") || str_contains($rawReplyTo, "\n") || !filter_var(trim($rawReplyTo), FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid reply-to email address.'];
            }
            $replyTo = Security::singleLine($rawReplyTo);
            if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid reply-to email address.'];
            }
        } else {
            $replyTo = null;
        }

        $logDir = Log::getLogDir();
        $logFile = $logDir . '/mail.log';
        $timestamp = date('Y-m-d H:i:s');
        $divider = str_repeat('-', 50);

        $fromDisplay = $fromName !== '' ? "{$fromName} <{$fromEmail}>" : "<{$fromEmail}>";
        $replyDisplay = '';
        if ($replyTo !== null) {
            $replyDisplay = $replyName !== '' ? "{$replyName} <{$replyTo}>" : "<{$replyTo}>";
        }

        $logEntry = <<<LOG
{$divider}
[{$timestamp}] MAIL DISPATCHED (LOG DRIVER)
To: {$to}
From: {$fromDisplay}
Reply-To: {$replyDisplay}
Subject: {$subject}
Body:
{$body}
{$divider}

LOG;

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        return [
            'success' => true,
            'message' => 'Email recorded to Others/logs/mail.log (Mail Driver is set to "log").',
        ];
    }
}

