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

        $to = $options['to'] ?? config('mail.admin_email');
        $subject = $options['subject'] ?? 'Notification';
        $body = $options['body'] ?? ($options['message'] ?? '');
        $isHtml = $options['is_html'] ?? true;
        $fromEmail = $options['from'] ?? config('mail.from.address', 'noreply@example.com');
        $fromName = $options['from_name'] ?? config('mail.from.name', 'My Website');
        $replyTo = $options['reply_to'] ?? null;
        $replyName = $options['reply_name'] ?? '';

        if (empty($to)) {
            return ['success' => false, 'message' => 'Recipient email (to) is required.'];
        }

        // Driver: 'log' - for local testing without mail server
        if ($driver === 'log') {
            return self::sendViaLog($to, $subject, $body, $fromEmail, $fromName, $replyTo);
        }

        // Driver: 'mail' - native PHP mail()
        if ($driver === 'mail') {
            return self::sendViaNativeMail($to, $subject, $body, $fromEmail, $fromName, $replyTo, $isHtml);
        }

        // Driver: 'smtp' - via Composer PHPMailer
        if (class_exists(PHPMailer::class)) {
            return self::sendViaPHPMailer($to, $subject, $body, $fromEmail, $fromName, $replyTo, $replyName, $isHtml);
        }

        // Fallback to native mail if PHPMailer class is not loaded
        return self::sendViaNativeMail($to, $subject, $body, $fromEmail, $fromName, $replyTo, $isHtml);
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
        bool $isHtml
    ): array {
        // Sanitize header fields against CRLF injection / Bcc injection
        $fromEmail = str_replace(["\r", "\n"], '', $fromEmail);
        $fromName  = str_replace(["\r", "\n"], '', $fromName);
        $to        = str_replace(["\r", "\n"], '', $to);
        $subject   = str_replace(["\r", "\n"], '', $subject);
        $replyTo   = $replyTo !== null ? str_replace(["\r", "\n"], '', $replyTo) : null;

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = $isHtml ? 'Content-type: text/html; charset=UTF-8' : 'Content-type: text/plain; charset=UTF-8';
        $headers[] = "From: {$fromName} <{$fromEmail}>";

        if ($replyTo) {
            $headers[] = "Reply-To: {$replyTo}";
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
        ?string $replyTo
    ): array {
        // Sanitize header values against log injection
        $fromEmail = str_replace(["\r", "\n"], '', $fromEmail);
        $fromName  = str_replace(["\r", "\n"], '', $fromName);
        $to        = str_replace(["\r", "\n"], '', $to);
        $subject   = str_replace(["\r", "\n"], '', $subject);
        $replyTo   = $replyTo !== null ? str_replace(["\r", "\n"], '', $replyTo) : null;

        $logDir = dirname(__DIR__) . '/Others/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0750, true);
        }

        $logFile = $logDir . '/mail.log';
        $timestamp = date('Y-m-d H:i:s');
        $divider = str_repeat('-', 50);

        $logEntry = <<<LOG
{$divider}
[{$timestamp}] MAIL DISPATCHED (LOG DRIVER)
To: {$to}
From: {$fromName} <{$fromEmail}>
Reply-To: {$replyTo}
Subject: {$subject}
Body:
{$body}
{$divider}

LOG;

        file_put_contents($logFile, $logEntry, FILE_APPEND);

        return [
            'success' => true,
            'message' => 'Email recorded to Others/logs/mail.log (Mail Driver is set to "log").',
        ];
    }
}

