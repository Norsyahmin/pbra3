<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// Load environment variables if phpdotenv is available, otherwise continue using getenv()
if (class_exists(\Dotenv\Dotenv::class)) {
    try {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    } catch (\Throwable $e) {
        // Non-fatal: continue and rely on existing environment variables
        error_log('phpdotenv load warning: ' . $e->getMessage());
    }
}

// Read mailer driver: prefer getenv() so values work even when phpdotenv isn't used
// Acceptable values: 'smtp', 'sendgrid', 'mailgun', 'mailtrap'
$mailerDriver = strtolower(trim(getenv('MAILER_DRIVER') ?: ($_ENV['MAILER_DRIVER'] ?? 'mailtrap')));

// Support Mailtrap environment variables as an alternative to generic SMTP_* vars.
// If MAILTRAP_HOST etc are present, prefer those (useful for CI or Mailtrap accounts).
$smtpHost = $_ENV['MAILTRAP_HOST'] ?? $_ENV['SMTP_HOST'] ?? null;
$smtpUser = $_ENV['MAILTRAP_USERNAME'] ?? $_ENV['SMTP_USERNAME'] ?? null;
$smtpPass = $_ENV['MAILTRAP_PASSWORD'] ?? $_ENV['SMTP_PASSWORD'] ?? null;
$smtpPort = $_ENV['MAILTRAP_PORT'] ?? $_ENV['SMTP_PORT'] ?? null;

// If MAILER_DRIVER=mailtrap but there's no API token, prefer Mailtrap SMTP if configured
$envMailtrapToken = getenv('MAILTRAP_API_TOKEN') ?: ($_ENV['MAILTRAP_API_TOKEN'] ?? '');
if ($mailerDriver === 'mailtrap' && empty($envMailtrapToken)) {
    // If SMTP credentials specifically for Mailtrap exist, switch to SMTP mode so messages
    // still reach Mailtrap via its SMTP server instead of failing due to missing API token.
    $mailtrapSmtpUser = getenv('MAILTRAP_SMTP_USERNAME') ?: ($_ENV['MAILTRAP_SMTP_USERNAME'] ?? '');
    $mailtrapSmtpPass = getenv('MAILTRAP_SMTP_PASSWORD') ?: ($_ENV['MAILTRAP_SMTP_PASSWORD'] ?? '');
    $mailtrapSmtpHost = getenv('MAILTRAP_SMTP_HOST') ?: ($_ENV['MAILTRAP_SMTP_HOST'] ?? '');
    $mailtrapSmtpPort = getenv('MAILTRAP_SMTP_PORT') ?: ($_ENV['MAILTRAP_SMTP_PORT'] ?? '');
    if (!empty($mailtrapSmtpUser) && !empty($mailtrapSmtpPass) && !empty($mailtrapSmtpHost)) {
        // switch driver and set SMTP vars
        $mailerDriver = 'smtp';
        $smtpHost = $mailtrapSmtpHost;
        $smtpUser = $mailtrapSmtpUser;
        $smtpPass = $mailtrapSmtpPass;
        $smtpPort = $mailtrapSmtpPort ?: $smtpPort;
        error_log('Mailer: MAILER_DRIVER=mailtrap but MAILTRAP_API_TOKEN missing -> falling back to Mailtrap SMTP');
    }
}

// If using SMTP driver and SMTP credentials are empty, prefer MAILTRAP_SMTP_* if provided
if ($mailerDriver === 'smtp') {
    $mailtrapSmtpUser = getenv('MAILTRAP_SMTP_USERNAME') ?: ($_ENV['MAILTRAP_SMTP_USERNAME'] ?? '');
    $mailtrapSmtpPass = getenv('MAILTRAP_SMTP_PASSWORD') ?: ($_ENV['MAILTRAP_SMTP_PASSWORD'] ?? '');
    $mailtrapSmtpHost = getenv('MAILTRAP_SMTP_HOST') ?: ($_ENV['MAILTRAP_SMTP_HOST'] ?? '');
    $mailtrapSmtpPort = getenv('MAILTRAP_SMTP_PORT') ?: ($_ENV['MAILTRAP_SMTP_PORT'] ?? '');
    if (empty($smtpUser) && !empty($mailtrapSmtpUser)) {
        $smtpUser = $mailtrapSmtpUser;
        $smtpPass = $mailtrapSmtpPass;
        if (!empty($mailtrapSmtpHost)) $smtpHost = $mailtrapSmtpHost;
        if (!empty($mailtrapSmtpPort)) $smtpPort = $mailtrapSmtpPort;
        error_log('Mailer: using MAILTRAP_SMTP_* credentials for SMTP transport');
    }
}

// Simple API mailer adapter to provide a PHPMailer-like interface for existing code.
class ApiMailerAdapter
{
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    // PHPMailer compatibility: charset property
    public $CharSet = 'UTF-8';
    private $from = ['email' => '', 'name' => ''];
    private $to = [];
    private $is_html = false;
    private $driver = 'sendgrid';
    private $error = '';

    public function __construct($driver = 'sendgrid')
    {
        $this->driver = $driver;
    }

    public function setFrom($email, $name = '')
    {
        $this->from = ['email' => $email, 'name' => $name];
    }

    public function addAddress($email, $name = '')
    {
        $this->to[] = ['email' => $email, 'name' => $name];
    }

    public function clearAddresses()
    {
        $this->to = [];
    }

    // PHPMailer compatibility methods used elsewhere in the codebase
    public function clearAllRecipients()
    {
        // Clear to/cc/bcc/replyto if implemented. We only track "to" so clear it.
        $this->to = [];
    }

    public function clearReplyTos()
    {
        // No-op: reply-to is not tracked separately in this adapter.
        return;
    }

    public function clearAttachments()
    {
        // No-op for API adapter; attachments aren't supported in this lightweight adapter.
        return;
    }

    public function isHTML($bool)
    {
        $this->is_html = (bool)$bool;
    }

    public function send()
    {
        $ok = false;
        // Ensure a default from address from environment when not explicitly set by caller
        if (empty($this->from['email'])) {
            $envFrom = getenv('MAIL_FROM') ?: ($_ENV['MAIL_FROM'] ?? '');
            $envFromName = getenv('MAIL_FROM_NAME') ?: ($_ENV['MAIL_FROM_NAME'] ?? '');
            if (!empty($envFrom)) {
                $this->from = ['email' => $envFrom, 'name' => $envFromName];
            }
        }
        if ($this->driver === 'sendgrid') {
            $ok = $this->sendViaSendGrid();
        } elseif ($this->driver === 'mailgun') {
            $ok = $this->sendViaMailgun();
        } elseif ($this->driver === 'mailtrap') {
            $ok = $this->sendViaMailtrap();
        } else {
            $this->error = 'Unsupported mailer driver: ' . $this->driver;
            error_log('ApiMailerAdapter error: ' . $this->error);
            throw new \Exception('ApiMailerAdapter error: ' . $this->error);
        }

        if ($ok) {
            return true;
        }

        // Log and throw an exception so callers (which expect PHPMailer exceptions)
        // can handle API send failures uniformly.
        $err = $this->getLastError() ?: 'Unknown error';
        error_log('ApiMailerAdapter error: ' . $err);
        throw new \Exception('ApiMailerAdapter send failed: ' . $err);
    }

    private function sendViaSendGrid()
    {
        $apiKey = getenv('SENDGRID_API_KEY') ?: ($_ENV['SENDGRID_API_KEY'] ?? '');
        if (empty($apiKey)) {
            $this->error = 'SENDGRID_API_KEY not set';
            return false;
        }

        $personalizations = [[
            'to' => array_map(function ($r) {
                return ['email' => $r['email'], 'name' => $r['name']];
            }, $this->to)
        ]];

        if (!empty($this->Subject)) {
            $personalizations[0]['subject'] = $this->Subject;
        }

        $contents = [];
        if ($this->is_html) {
            $contents[] = ['type' => 'text/plain', 'value' => $this->AltBody ?: strip_tags($this->Body)];
            $contents[] = ['type' => 'text/html', 'value' => $this->Body];
        } else {
            $contents[] = ['type' => 'text/plain', 'value' => $this->AltBody ?: $this->Body];
        }

        $payload = [
            'personalizations' => $personalizations,
            'from' => ['email' => $this->from['email'], 'name' => $this->from['name']],
            'content' => $contents
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        // Handle SSL certificate issues for SendGrid API
        $debug = getenv('MAIL_DEBUG') ?: ($_ENV['MAIL_DEBUG'] ?? '');
        if ($debug) {
            // For development/debugging, disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($resp === false) {
            $this->error = 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        // SendGrid returns 202 on success
        if ($code >= 200 && $code < 300) {
            return true;
        }
        $this->error = 'SendGrid API error, HTTP ' . $code . ': ' . $resp;
        return false;
    }

    private function sendViaMailgun()
    {
        // Mailgun requires domain and API key
        $domain = getenv('MAILGUN_DOMAIN') ?: ($_ENV['MAILGUN_DOMAIN'] ?? '');
        $apiKey = getenv('MAILGUN_API_KEY') ?: ($_ENV['MAILGUN_API_KEY'] ?? '');
        if (empty($domain) || empty($apiKey)) {
            $this->error = 'MAILGUN_DOMAIN or MAILGUN_API_KEY not set';
            return false;
        }

        $url = 'https://api.mailgun.net/v3/' . $domain . '/messages';
        $toList = array_map(function ($r) { return $r['email']; }, $this->to);

        $post = [
            'from' => ($this->from['name'] ? ($this->from['name'] . ' <' . $this->from['email'] . '>') : $this->from['email']),
            'to' => implode(',', $toList),
            'subject' => $this->Subject,
            'text' => $this->AltBody ?: strip_tags($this->Body),
        ];
        if ($this->is_html) $post['html'] = $this->Body;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($resp === false) {
            $this->error = 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        if ($code >= 200 && $code < 300) return true;
        $this->error = 'Mailgun API error, HTTP ' . $code . ': ' . $resp;
        return false;
    }

    private function sendViaMailtrap()
    {
        // Generic Mailtrap Send API adapter. Configure MAILTRAP_API_TOKEN and optionally MAILTRAP_API_URL
        // Accept either MAILTRAP_API_TOKEN (older) or MAILTRAP_API_KEY (SDK/docs)
        $token = getenv('MAILTRAP_API_TOKEN') ?: (
            getenv('MAILTRAP_API_KEY') ?: ($_ENV['MAILTRAP_API_TOKEN'] ?? ($_ENV['MAILTRAP_API_KEY'] ?? ''))
        );
        $url = getenv('MAILTRAP_API_URL') ?: ($_ENV['MAILTRAP_API_URL'] ?? 'https://send.api.mailtrap.io/api/send');
        // Optional debug flag to write verbose mail API interactions to logs/mail_debug.log
        $debug = getenv('MAIL_DEBUG') ?: ($_ENV['MAIL_DEBUG'] ?? '');

        if (empty($token)) {
            $this->error = 'MAILTRAP_API_TOKEN not set';
            // Log for debugging
            $msg = '[' . date('c') . "] Mailtrap API token not set. MAILER_DRIVER=mailtrap requires MAILTRAP_API_TOKEN in environment.\n";
            error_log($msg);
            if ($debug) {
                $logPath = __DIR__ . '/logs/mail_debug.log';
                @file_put_contents($logPath, $msg, FILE_APPEND | LOCK_EX);
            }
            return false;
        }

        // Build payload using Mailtrap's send API schema (from/to/subject/text/html)
        $toArray = array_map(function ($r) {
            return ['email' => $r['email'], 'name' => $r['name']];
        }, $this->to);

        $payload = [
            'from' => ['email' => $this->from['email'], 'name' => $this->from['name']],
            'to' => $toArray,
            'subject' => $this->Subject ?: '',
            'text' => $this->AltBody ?: ($this->is_html ? strip_tags($this->Body) : $this->Body),
        ];
        if ($this->is_html) {
            $payload['html'] = $this->Body;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // SSL CA bundle handling: prefer explicit MAIL_CA_BUNDLE env var, then php.ini curl.cainfo.
        // If neither is present and MAIL_DEBUG is enabled, allow disabling verification for local dev only.
        $mailDebug = getenv('MAIL_DEBUG') ?: ($_ENV['MAIL_DEBUG'] ?? '');
        $caPath = getenv('MAIL_CA_BUNDLE') ?: ($_ENV['MAIL_CA_BUNDLE'] ?? '');
        $iniCA = ini_get('curl.cainfo');
        if (!empty($caPath) && file_exists($caPath)) {
            curl_setopt($ch, CURLOPT_CAINFO, $caPath);
        } elseif (!empty($iniCA) && file_exists($iniCA)) {
            // curl will use the ini value by default; nothing to do.
        } elseif ($mailDebug) {
            // Only for debugging: disable SSL verification if no CA bundle is available.
            error_log('Mailtrap debug: no CA bundle found; disabling SSL verification for debug run.');
            @file_put_contents(__DIR__ . '/logs/mail_debug.log', '[' . date('c') . '] Mailtrap debug: no CA bundle found; disabling SSL verification for debug run.' . "\n", FILE_APPEND | LOCK_EX);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        // Determine auth header scheme. Allow overriding via MAILTRAP_AUTH_SCHEME env: 'bearer'|'api-token'
        $authSchemeEnv = strtolower(getenv('MAILTRAP_AUTH_SCHEME') ?: ($_ENV['MAILTRAP_AUTH_SCHEME'] ?? ''));
        $attempts = [];
        if ($authSchemeEnv === 'api-token') {
            $attempts = ['api-token', 'bearer'];
        } elseif ($authSchemeEnv === 'bearer') {
            $attempts = ['bearer', 'api-token'];
        } else {
            // Default preference: Bearer (newer docs) then Api-Token
            $attempts = ['bearer', 'api-token'];
        }

        $resp = false;
        $code = 0;
        $usedScheme = null;
        foreach ($attempts as $scheme) {
            if ($scheme === 'bearer') {
                $headers = [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json',
                    'Content-Type: application/json'
                ];
            } else {
                $headers = [
                    'Api-Token: ' . $token,
                    'Accept: application/json',
                    'Content-Type: application/json'
                ];
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // If debug enabled, record which header is attempted (masked token)
            if ($debug) {
                $masked = substr($token, 0, 6) . str_repeat('*', max(0, strlen($token) - 10)) . substr($token, -4);
                @file_put_contents(__DIR__ . '/logs/mail_debug.log', '[' . date('c') . '] Mailtrap attempting auth scheme: ' . $scheme . "\n", FILE_APPEND | LOCK_EX);
            }

            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($resp === false) {
                $this->error = 'cURL error: ' . curl_error($ch);
                $err = '[' . date('c') . '] Mailtrap cURL error: ' . curl_error($ch) . "\n";
                error_log($err);
                if ($debug) @file_put_contents(__DIR__ . '/logs/mail_debug.log', $err, FILE_APPEND | LOCK_EX);
                // don't retry on low-level cURL error
                break;
            }

            // If we got a non-401 (i.e., success or other failure), stop trying alternate schemes
            if ($code !== 401) {
                $usedScheme = $scheme;
                break;
            }
            // else: 401 -> try next scheme
        }

        // Close curl only after attempts
        curl_close($ch);

        if ($debug) {
            $entry = '[' . date('c') . '] Mailtrap HTTP ' . $code . ' RESPONSE: ' . $resp . "\n";
            $logDir = __DIR__ . '/logs';
            if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
            @file_put_contents($logDir . '/mail_debug.log', $entry, FILE_APPEND | LOCK_EX);
        }

        // If Mailtrap official PHP SDK is available, attempt to send using it when configured.
        // This provides better MIME handling and uses PSR-18 HTTP client under the hood.
        if (class_exists(\Mailtrap\MailtrapClient::class)) {
            try {
                // Build Symfony/Mime Email using the SDK helper classes if available.
                if (class_exists(\Mailtrap\Mime\MailtrapEmail::class) && class_exists(\Symfony\Component\Mime\Email::class)) {
                    $mailtrapApi = \Mailtrap\MailtrapClient::initSendingEmails($token);

                    $email = new \Mailtrap\Mime\MailtrapEmail();
                    // set from
                    if (!empty($this->from['email'])) {
                        $email = $email->from(new \Symfony\Component\Mime\Address($this->from['email'], $this->from['name'] ?? ''));
                    }
                    // to
                    foreach ($this->to as $r) {
                        $email = $email->to(new \Symfony\Component\Mime\Address($r['email'], $r['name'] ?? ''));
                    }
                    if ($this->is_html) {
                        $email = $email->html($this->Body)->text($this->AltBody ?: strip_tags($this->Body));
                    } else {
                        $email = $email->text($this->AltBody ?: $this->Body);
                    }
                    if (!empty($this->Subject)) $email = $email->subject($this->Subject);

                    $response = $mailtrapApi->send($email);
                    // ResponseHelper not required; assume success if no exception
                    return true;
                }
            } catch (\Exception $e) {
                // SDK send failed: record and fall back to API HTTP call above
                $this->error = 'Mailtrap SDK error: ' . $e->getMessage();
                if ($debug) @file_put_contents(__DIR__ . '/logs/mail_debug.log', '[' . date('c') . '] Mailtrap SDK error: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
                // continue to evaluate $code from previous cURL attempt
            }
        }

        if ($code >= 200 && $code < 300) {
            return true;
        }

        // API failed — attempt SMTP fallback if SMTP credentials are available.
        $smtpHostEnv = getenv('MAILTRAP_SMTP_HOST') ?: (getenv('SMTP_HOST') ?: ($_ENV['MAILTRAP_SMTP_HOST'] ?? ($_ENV['SMTP_HOST'] ?? '')));
        $smtpUserEnv = getenv('MAILTRAP_SMTP_USERNAME') ?: (getenv('SMTP_USERNAME') ?: ($_ENV['MAILTRAP_SMTP_USERNAME'] ?? ($_ENV['SMTP_USERNAME'] ?? '')));
        $smtpPassEnv = getenv('MAILTRAP_SMTP_PASSWORD') ?: (getenv('SMTP_PASSWORD') ?: ($_ENV['MAILTRAP_SMTP_PASSWORD'] ?? ($_ENV['SMTP_PASSWORD'] ?? '')));
        $smtpPortEnv = getenv('MAILTRAP_SMTP_PORT') ?: (getenv('SMTP_PORT') ?: ($_ENV['MAILTRAP_SMTP_PORT'] ?? ($_ENV['SMTP_PORT'] ?? '')));

        $smtpAvailable = !empty($smtpHostEnv) && !empty($smtpUserEnv) && !empty($smtpPassEnv);
        if ($smtpAvailable) {
            // Attempt SMTP send using PHPMailer as a fallback.
            try {
                $phpmailer = new PHPMailer(true);
                $phpmailer->isSMTP();
                $phpmailer->Host = $smtpHostEnv;
                $phpmailer->SMTPAuth = true;
                $phpmailer->Username = $smtpUserEnv;
                $phpmailer->Password = $smtpPassEnv;
                $phpmailer->Port = (int)$smtpPortEnv ?: 587;

                // Decide encryption: prefer SMTP_ENCRYPTION or default to STARTTLS on common ports
                $smtpEnc = strtolower(trim(getenv('SMTP_ENCRYPTION') ?: ($_ENV['SMTP_ENCRYPTION'] ?? '')));
                if ($smtpEnc === 'ssl' || (empty($smtpEnc) && ((int)$phpmailer->Port === 465))) {
                    $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($smtpEnc === 'none' || $smtpEnc === 'no') {
                    $phpmailer->SMTPSecure = false;
                } else {
                    $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }

                $phpmailer->isHTML($this->is_html);
                // From address fallback
                $fromEmail = !empty($this->from['email']) ? $this->from['email'] : (getenv('MAIL_FROM') ?: ($_ENV['MAIL_FROM'] ?? 'pbrauser.help@gmail.com'));
                $fromName = !empty($this->from['name']) ? $this->from['name'] : (getenv('MAIL_FROM_NAME') ?: ($_ENV['MAIL_FROM_NAME'] ?? 'PBRA'));
                $phpmailer->setFrom($fromEmail, $fromName);

                // Add recipients
                foreach ($this->to as $r) {
                    $phpmailer->addAddress($r['email'], $r['name'] ?? '');
                }

                if (!empty($this->Subject)) $phpmailer->Subject = $this->Subject;
                if ($this->is_html) {
                    $phpmailer->Body = $this->Body;
                    $phpmailer->AltBody = $this->AltBody ?: strip_tags($this->Body);
                } else {
                    $phpmailer->Body = $this->AltBody ?: $this->Body;
                }

                // Optional debug logging to file
                $debug = getenv('MAIL_DEBUG') ?: ($_ENV['MAIL_DEBUG'] ?? '');
                if (!empty($debug)) {
                    $logDir = __DIR__ . '/logs';
                    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                    @file_put_contents($logDir . '/mail_debug.log', '[' . date('c') . '] Mailtrap SMTP fallback attempting to ' . $smtpHostEnv . ':' . $phpmailer->Port . "\n", FILE_APPEND | LOCK_EX);
                }

                $phpmailer->send();
                // If send succeeds, treat as success
                return true;
            } catch (\Exception $e) {
                // Append SMTP fallback error to adapter error for diagnostics
                $smtpErr = 'SMTP fallback error: ' . $e->getMessage();
                $this->error = 'Mailtrap API error, HTTP ' . $code . ': ' . $resp . ' | ' . $smtpErr;
                error_log($this->error);
                if (!empty($debug)) {
                    $logDir = __DIR__ . '/logs';
                    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                    @file_put_contents($logDir . '/mail_debug.log', '[' . date('c') . '] ' . $this->error . "\n", FILE_APPEND | LOCK_EX);
                }
                return false;
            }
        }

        $this->error = 'Mailtrap API error, HTTP ' . $code . ': ' . $resp;
        return false;
    }

    public function getLastError()
    {
        return $this->error;
    }

    public function __clone()
    {
        // When cloned, reset recipient list and error state so callers can safely reuse a
        // configured base mailer instance and populate recipients per message (like PHPMailer).
        $this->to = [];
        $this->error = '';
    }
}
try {
    // instantiate PHPMailer and enable exceptions
    if ($mailerDriver === 'smtp') {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $smtpHost;
        // Mailtrap and most SMTP providers require authentication
        $mail->SMTPAuth = !empty($smtpUser);
        if ($mail->SMTPAuth) {
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
        }
        // Determine encryption mode. Support optional SMTP_ENCRYPTION env: 'tls'|'ssl'|'none'
        $smtpEnc = strtolower(trim($_ENV['SMTP_ENCRYPTION'] ?? ''));
        if ($smtpEnc === 'ssl' || (empty($smtpEnc) && (int)($_ENV['SMTP_PORT'] ?? 0) === 465)) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // implicit TLS on 465
        } elseif ($smtpEnc === 'none' || $smtpEnc === 'no') {
            $mail->SMTPSecure = false;
        } else {
            // Default to STARTTLS on submission port (587)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port = (int) $smtpPort;

        $mail->isHTML(true);
        // Ensure a sensible default From for SMTP sends if not set by caller
        $defaultFrom = getenv('MAIL_FROM') ?: ($_ENV['MAIL_FROM'] ?? '');
        $defaultFromName = getenv('MAIL_FROM_NAME') ?: ($_ENV['MAIL_FROM_NAME'] ?? '');
        if (!empty($defaultFrom)) {
            try {
                $mail->setFrom($defaultFrom, $defaultFromName ?: 'PBRA');
            } catch (\Exception $e) {
                // If setting default from fails, log and continue; callers may set it later.
                error_log('Failed to set default MAIL_FROM: ' . $e->getMessage());
            }
        }

        // When using Mailtrap SMTP (or when explicitly forcing), ensure the envelope Sender
        // uses the verified sending address. This prevents SMTP servers (like Mailtrap) from
        // rejecting the MAIL FROM command if the envelope domain is not allowed.
        $forceFrom = getenv('MAIL_FORCE_FROM') ?: ($_ENV['MAIL_FORCE_FROM'] ?? '');
        if (!empty($defaultFrom) && ($forceFrom || (stripos($smtpHost, 'mailtrap') !== false))) {
            // Set the envelope sender
            $mail->Sender = $defaultFrom;
            try {
                $mail->setFrom($defaultFrom, $defaultFromName ?: 'PBRA');
            } catch (\Exception $e) {
                // ignore - Sender is most important for SMTP envelope
                error_log('Failed to set MAIL_FROM header: ' . $e->getMessage());
            }
        }
        // Optional debug flag to help diagnose SMTP problems. 0 = off, 1|2 = verbose.
        $smtpDebug = (int) ($_ENV['SMTP_DEBUG'] ?? 0);
        $mailDebug = getenv('MAIL_DEBUG') ?: ($_ENV['MAIL_DEBUG'] ?? '');
        if ($smtpDebug > 0) {
            // Use PHPMailer debug constant values; DEBUG_SERVER (2) is useful.
            $mail->SMTPDebug = $smtpDebug;
            // Write debug output to PHP error log and to logs/mail_debug.log when MAIL_DEBUG is set
            $mail->Debugoutput = function ($str, $level) use ($mailDebug) {
                $msg = sprintf("PHPMailer debug [%s]: %s", $level, $str);
                error_log($msg);
                if (!empty($mailDebug)) {
                    $logPath = __DIR__ . '/logs/mail_debug.log';
                    @file_put_contents($logPath, '[' . date('c') . '] ' . $msg . "\n", FILE_APPEND | LOCK_EX);
                }
            };
        }
    } else {
        // Use API-based adapter
        // Currently supported drivers: sendgrid, mailgun, mailtrap
        if (in_array($mailerDriver, ['sendgrid', 'mailgun', 'mailtrap'], true)) {
            $mail = new ApiMailerAdapter($mailerDriver);
        } else {
            throw new \Exception('Unsupported MAILER_DRIVER: ' . $mailerDriver);
        }
    }
} catch (Exception $e) {
    // so a single catch for Exception handles all PHPMailer-related exceptions.
    throw new \Exception("Failed to configure PHPMailer: " . $e->getMessage());
}

return $mail;
