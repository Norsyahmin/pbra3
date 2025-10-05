<?php
/**
 * SendGrid Email Test Script
 * Run this to test your SendGrid configuration
 */

require_once 'mailer.php';

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!empty($key) && !empty($value)) {
            putenv("$key=$value");
        }
    }
}

try {
    echo "Testing SendGrid Email Configuration...\n";
    echo "=====================================\n";
    
    // Check if SendGrid API key is set
    $apiKey = getenv('SENDGRID_API_KEY');
    if (empty($apiKey)) {
        throw new Exception("SENDGRID_API_KEY not found in environment");
    }
    echo "✓ SendGrid API Key found\n";
    
    // Check mailer driver
    $driver = getenv('MAILER_DRIVER');
    echo "✓ Mailer driver: $driver\n";
    
    // Get mail instance
    $mail = require_once 'mailer.php';
    echo "✓ Mailer instance created successfully\n";
    
    // Configure test email
    $fromEmail = getenv('MAIL_FROM') ?: 'pbrauser.help@gmail.com';
    $fromName = getenv('MAIL_FROM_NAME') ?: 'PBRA System';
    
    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress('pbrauser.help@gmail.com', 'Test Recipient'); // Send to yourself for testing
    
    $mail->isHTML(true);
    $mail->Subject = 'PBRA SendGrid Test Email - ' . date('Y-m-d H:i:s');
    $mail->Body = '
        <h2>SendGrid Test Email</h2>
        <p>This is a test email to verify your SendGrid integration is working correctly.</p>
        <p><strong>Sent at:</strong> ' . date('Y-m-d H:i:s') . '</p>
        <p><strong>From:</strong> PBRA System</p>
        <p><strong>Configuration:</strong></p>
        <ul>
            <li>Driver: ' . htmlspecialchars($driver) . '</li>
            <li>From Email: ' . htmlspecialchars($fromEmail) . '</li>
            <li>From Name: ' . htmlspecialchars($fromName) . '</li>
        </ul>
        <p>If you receive this email, your SendGrid configuration is working correctly! ✓</p>
    ';
    $mail->AltBody = 'SendGrid Test Email - This is a test email to verify your SendGrid integration is working correctly. Sent at: ' . date('Y-m-d H:i:s');
    
    echo "✓ Email configured\n";
    echo "Sending test email...\n";
    
    // Send the email
    $result = $mail->send();
    
    if ($result) {
        echo "✓ SUCCESS! Test email sent successfully via SendGrid!\n";
        echo "Check your inbox at: $fromEmail\n";
    } else {
        throw new Exception("Failed to send email");
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    
    // Try to get more error details
    if (isset($mail) && method_exists($mail, 'getLastError')) {
        echo "Details: " . $mail->getLastError() . "\n";
    }
    
    echo "\nTroubleshooting Steps:\n";
    echo "1. Verify your SendGrid API key is correct\n";
    echo "2. Ensure your sender email is verified in SendGrid\n";
    echo "3. Check your .env file configuration\n";
    echo "4. Check logs/mail_debug.log for detailed error information\n";
}

echo "\nDone.\n";
?>