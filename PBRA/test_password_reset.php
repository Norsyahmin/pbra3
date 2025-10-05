<?php
/**
 * Password Reset Email Test (No Database)
 * Test the email sending part of password reset
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

echo "Testing Password Reset Email Functionality...\n";
echo "=============================================\n";

try {
    // Get mailer instance
    $mail = require 'mailer.php';
    echo "✓ Mailer loaded successfully\n";
    
    // Simulate password reset email
    $test_email = 'pbrauser.help@gmail.com';
    $test_name = 'Test User';
    $reset_token = bin2hex(random_bytes(32));
    $reset_link = "http://localhost/forget_password/reset_password.php?token=" . $reset_token;
    
    echo "✓ Test email: $test_email\n";
    echo "✓ Generated reset token\n";
    
    // Configure email (similar to send_password_reset.php)
    $mail->isHTML(true);
    $mail->clearAddresses();
    
    // Use verified SendGrid sender
    $fromEmail = getenv('MAIL_FROM') ?: 'pbrauser.help@gmail.com';
    $fromName = getenv('MAIL_FROM_NAME') ?: 'Politeknik Brunei Role Appointment System';
    $mail->setFrom($fromEmail, $fromName);
    
    $mail->addAddress($test_email);
    $mail->Subject = "Password Reset Request";
    $mail->Body = "Dear {$test_name},<br><br>"
        . "You have requested to reset your password for your PBRA account. Please click on the link below to reset your password:<br><br>"
        . "<a href='$reset_link'>Reset Password</a><br><br>"
        . "This link will expire in 24 hours. If you did not request this password reset, please ignore this email.";
    $mail->AltBody = "Dear {$test_name},\n\n"
        . "You have requested to reset your password for your PBRA account. Please visit: $reset_link\n\n"
        . "This link will expire in 24 hours. If you did not request this password reset, please ignore this email.";
    
    echo "✓ Email configured\n";
    echo "Sending password reset email...\n";
    
    // Send the email
    $result = $mail->send();
    
    if ($result) {
        echo "✓ SUCCESS! Password reset email sent successfully!\n";
        echo "✓ Check your inbox at: $test_email\n";
        echo "✓ Look for subject: 'Password Reset Request'\n";
        echo "\n🎉 Password reset email functionality is working with SendGrid!\n";
    } else {
        echo "✗ FAILED to send password reset email\n";
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