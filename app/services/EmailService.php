<?php

class EmailService
{
    private ?array $settings;

    public function __construct()
    {
        $model = new SmtpSetting();
        $this->settings = $model->getByClient($GLOBALS['client_id']);
    }

    public function isConfigured(): bool
    {
        return !empty($this->settings['is_configured']);
    }

    public function send(string $to, string $subject, string $htmlBody): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Email not configured. Set up SMTP in settings.'];
        }

        $provider = $this->settings['provider'] ?? 'smtp';

        $result = match ($provider) {
            'sendgrid' => $this->sendViaSendGrid($to, $subject, $htmlBody),
            'mailgun' => $this->sendViaMailgun($to, $subject, $htmlBody),
            'emailit' => $this->sendViaEmailit($to, $subject, $htmlBody),
            default => $this->sendViaSmtp($to, $subject, $htmlBody),
        };

        // Fallback to Emailit if primary provider fails
        if (!($result['success'] ?? false) && $provider !== 'emailit') {
            $emailitKey = $this->settings['emailit_api_key'] ?? '';
            if (!empty($emailitKey)) {
                $fallback = $this->sendViaEmailit($to, $subject, $htmlBody);
                if ($fallback['success'] ?? false) {
                    return $fallback;
                }
            }
        }

        return $result;
    }

    private function sendViaSmtp(string $to, string $subject, string $htmlBody): array
    {
        $host = $this->settings['smtp_host'] ?? '';
        $port = (int)($this->settings['smtp_port'] ?? 587);
        $user = $this->settings['smtp_user'] ?? '';
        $pass = $this->settings['smtp_pass'] ?? '';
        $encryption = $this->settings['smtp_encryption'] ?? 'tls';
        $fromName = $this->settings['from_name'] ?? 'System';
        $fromEmail = $this->settings['from_email'] ?? $user;

        if (empty($host) || empty($user)) {
            return ['success' => false, 'error' => 'SMTP host or user not configured'];
        }

        // Always use authenticated SMTP socket (mail() silently fails on shared hosting)
        return $this->sendSmtpSocket($host, $port, $user, $pass, $encryption, $fromEmail, $fromName, $to, $subject, $htmlBody);
    }

    private function sendSmtpSocket(string $host, int $port, string $user, string $pass, string $enc, string $from, string $fromName, string $to, string $subject, string $body): array
    {
        // SSL wraps the entire connection (port 465 typically)
        // TLS uses STARTTLS upgrade after connecting in plain (port 587 typically)
        $useImplicitSSL = ($enc === 'ssl' || $port == 465);
        $useSTARTTLS = ($enc === 'tls' || $enc === 'ssl') && $port != 465;

        $prefix = $useImplicitSSL ? 'ssl://' : '';
        $sock = @fsockopen($prefix . $host, $port, $errno, $errstr, 15);
        if (!$sock) {
            return ['success' => false, 'error' => "Connection failed: {$errstr} (host: {$prefix}{$host}:{$port})"];
        }

        // Helper to read full multi-line SMTP response
        $readResponse = function() use ($sock) {
            $response = '';
            while ($line = fgets($sock, 512)) {
                $response .= $line;
                if (isset($line[3]) && $line[3] === ' ') break;
            }
            return $response;
        };

        $readResponse(); // greeting
        fputs($sock, "EHLO " . gethostname() . "\r\n");
        $readResponse(); // EHLO response (multi-line with capabilities)

        // STARTTLS for port 587 with TLS or SSL setting
        if ($useSTARTTLS && !$useImplicitSSL) {
            fputs($sock, "STARTTLS\r\n");
            $starttlsReply = $readResponse();
            if (!str_starts_with(trim($starttlsReply), '220')) {
                fclose($sock);
                return ['success' => false, 'error' => 'STARTTLS failed: ' . trim($starttlsReply)];
            }
            $crypto = stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
            if (!$crypto) {
                fclose($sock);
                return ['success' => false, 'error' => 'TLS encryption handshake failed'];
            }
            // Re-EHLO after TLS upgrade
            fputs($sock, "EHLO " . gethostname() . "\r\n");
            $readResponse();
        }

        fputs($sock, "AUTH LOGIN\r\n");
        $authPrompt = $readResponse();
        fputs($sock, base64_encode($user) . "\r\n");
        $readResponse(); // username accepted
        fputs($sock, base64_encode($pass) . "\r\n");
        $authReply = $readResponse();
        if (!str_starts_with(trim($authReply), '235')) {
            fclose($sock);
            return ['success' => false, 'error' => 'SMTP authentication failed: ' . trim($authReply)];
        }

        fputs($sock, "MAIL FROM:<{$from}>\r\n"); $readResponse();
        fputs($sock, "RCPT TO:<{$to}>\r\n"); $readResponse();
        fputs($sock, "DATA\r\n"); $readResponse();

        $msg = "From: {$fromName} <{$from}>\r\n";
        $msg .= "To: <{$to}>\r\n";
        $msg .= "Subject: {$subject}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $msg .= $body . "\r\n.\r\n";

        fputs($sock, $msg);
        $dataReply = $readResponse();
        fputs($sock, "QUIT\r\n");
        @fclose($sock);

        if (str_starts_with(trim($dataReply), '250')) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'SMTP send failed: ' . trim($dataReply)];
    }

    private function sendViaSendGrid(string $to, string $subject, string $htmlBody): array
    {
        $apiKey = $this->settings['sendgrid_api_key'] ?? '';
        $fromEmail = $this->settings['from_email'] ?? '';
        $fromName = $this->settings['from_name'] ?? '';

        if (empty($apiKey) || empty($fromEmail)) {
            return ['success' => false, 'error' => 'SendGrid API key or From email not configured'];
        }

        $payload = json_encode([
            'personalizations' => [['to' => [['email' => $to]]]],
            'from' => ['email' => $fromEmail, 'name' => $fromName],
            'subject' => $subject,
            'content' => [['type' => 'text/html', 'value' => $htmlBody]],
        ]);

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true];
        }
        $data = json_decode($response, true);
        $err = $data['errors'][0]['message'] ?? "HTTP {$httpCode}";
        return ['success' => false, 'error' => $err];
    }

    private function sendViaMailgun(string $to, string $subject, string $htmlBody): array
    {
        $apiKey = $this->settings['mailgun_api_key'] ?? '';
        $domain = $this->settings['mailgun_domain'] ?? '';
        $fromEmail = $this->settings['from_email'] ?? '';
        $fromName = $this->settings['from_name'] ?? '';

        if (empty($apiKey) || empty($domain) || empty($fromEmail)) {
            return ['success' => false, 'error' => 'Mailgun settings incomplete'];
        }

        $ch = curl_init("https://api.mailgun.net/v3/{$domain}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => 'api:' . $apiKey,
            CURLOPT_POSTFIELDS => [
                'from' => "{$fromName} <{$fromEmail}>",
                'to' => $to,
                'subject' => $subject,
                'html' => $htmlBody,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return ['success' => true];
        }
        $data = json_decode($response, true);
        return ['success' => false, 'error' => $data['message'] ?? "HTTP {$httpCode}"];
    }

    private function sendViaEmailit(string $to, string $subject, string $htmlBody): array
    {
        $apiKey = $this->settings['emailit_api_key'] ?? '';
        $fromEmail = $this->settings['from_email'] ?? '';
        $fromName = $this->settings['from_name'] ?? '';

        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'Emailit API key not configured'];
        }

        $from = $fromName ? "{$fromName} <{$fromEmail}>" : $fromEmail;

        $payload = json_encode([
            'from' => $from,
            'to' => [$to],
            'subject' => $subject,
            'html' => $htmlBody,
        ]);

        $ch = curl_init('https://api.emailit.com/v2/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'Emailit connection error: ' . $error];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true];
        }

        $data = json_decode($response, true);
        return ['success' => false, 'error' => 'Emailit: ' . ($data['message'] ?? $data['error'] ?? "HTTP {$httpCode}")];
    }

    public function buildInvitationHtml(string $logoUrl, string $primaryColor, string $companyName, string $recipientName, string $tempPassword, string $loginUrl): string
    {
        ob_start();
        include APP_ROOT . '/app/views/emails/invitation.php';
        return ob_get_clean();
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Not configured'];
        }
        $fromEmail = $this->settings['from_email'] ?? '';
        return $this->send($fromEmail, 'Test Email — Solid Social', '<html><body><p>This is a test email from your social media management tool. If you received this, email is working correctly.</p></body></html>');
    }
}
