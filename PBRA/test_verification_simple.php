<?php
/**
 * Simple Account Verification Email Test (No Database)
 * Test the email sending part of account verification
 */

// Load environment variables manually
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, '"\'');
        if (!empty($key) && !empty($value)) {
            putenv("$key=$value");
        }
    }
}

echo "Testing Account Verification Email (Email Only)...\n";
echo "=================================================\n";

try {
    // Get mailer instance
    $mail = require 'mailer.php';
    echo "✓ Mailer loaded successfully\n";
    
    // Simulate verification email sending
    $test_email = 'pbrauser.help@gmail.com';
    $test_name = 'Test User';
    $verification_token = bin2hex(random_bytes(32));
    $verification_link = "http://localhost/account_activation/verify_account.php?token=" . $verification_token;
    
    echo "✓ Test email: $test_email\n";
    echo "✓ Generated verification token\n";
    
    // Configure email (similar to send_verification_email.php)
    $mail->isHTML(true);
    $mail->clearAddresses();
    
    // Use verified SendGrid sender
    $fromEmail = getenv('MAIL_FROM') ?: 'pbrauser.help@gmail.com';
    $fromName = getenv('MAIL_FROM_NAME') ?: 'PBRA System';
    $mail->setFrom($fromEmail, $fromName);
    
    $mail->addAddress($test_email);
    $mail->Subject = "Verify Your PbRA Account";
    $mail->Body = "Dear {$test_name},<br><br>"
        . "Thank you for registering with PbRA. Please verify your account by clicking on the link below:<br><br>"
        . "<a href='$verification_link'>$verification_link</a><br><br>"
        . "This link will expire in 24 hours. If you did not create this account, please ignore this email.";
    $mail->AltBody = "Dear {$test_name},\n\n"
        . "Thank you for registering with PbRA. Please verify your account by visiting: $verification_link\n\n"
        . "This link will expire in 24 hours. If you did not create this account, please ignore this email.";
    
    echo "✓ Email configured\n";
    echo "Sending verification email...\n";
    
    // Send the email
    $result = $mail->send();
    
    if ($result) {
        echo "✓ SUCCESS! Account verification email sent successfully!\n";
        echo "✓ Check your inbox at: $test_email\n";
        echo "✓ Look for subject: 'Verify Your PbRA Account'\n";
        echo "\n🎉 Account verification email functionality is working with SendGrid!\n";
    } else {
        echo "✗ FAILED to send verification email\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    
    // Try to get more error details
    if (isset($mail) && method_exists($mail, 'getLastError')) {
        echo "Details: " . $mail->getLastError() . "\n";
    }
}

echo "\nDone.\n";
?>