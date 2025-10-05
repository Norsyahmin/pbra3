<?php
/**
 * OTP Email Test Script
 * Test if OTP emails are being sent correctly via SendGrid
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

// Start session for OTP testing
session_start();

// Include the OTP notification function
require_once 'login/otp_notification.php';

echo "Testing OTP Email Functionality...\n";
echo "==================================\n";

try {
    // Get mailer instance
    $mail = require 'mailer.php';
    echo "✓ Mailer loaded successfully\n";
    
    // Mock user data for testing
    $testUser = [
        'id' => 1,
        'email' => 'pbrauser.help@gmail.com', // Send to verified email
        'full_name' => 'Test Admin User',
        'user_type' => 'admin'
    ];
    
    echo "✓ Test user created: {$testUser['email']}\n";
    echo "Sending OTP email...\n";
    
    // Test the OTP notification function
    $result = showOtpNotification($testUser, $mail);
    
    if ($result) {
        echo "✓ SUCCESS! OTP email sent successfully!\n";
        if (isset($_SESSION['otp'])) {
            echo "✓ Generated OTP: " . $_SESSION['otp'] . "\n";
            echo "✓ OTP expires at: " . date('Y-m-d H:i:s', $_SESSION['otp_expiry']) . "\n";
        }
        echo "✓ Check your inbox at: {$testUser['email']}\n";
        echo "\n🎉 OTP email functionality is working!\n";
    } else {
        echo "✗ FAILED to send OTP email\n";
        echo "Check the error logs for more details\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Check your mailer configuration\n";
}

echo "\nDone.\n";
?>