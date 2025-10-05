<?php
/**
 * Account Verification Email Test Script
 * Test if account verification emails are being sent correctly via SendGrid
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

echo "Testing Account Verification Email Functionality...\n";
echo "=================================================\n";

try {
    // Include database connection
    require_once 'mypbra_connect.php';
    
    // Include the verification email function
    require_once 'account_activation/send_verification_email.php';
    
    echo "✓ Database and verification function loaded\n";
    
    // Test with a mock user (using your email for testing)
    $test_user_id = 999; // Mock user ID
    $test_email = 'pbrauser.help@gmail.com'; // Your verified email
    $test_recovery_email = 'pbrauser.help@gmail.com';
    
    echo "✓ Test user: $test_email\n";
    echo "Sending verification email...\n";
    
    // Test the verification email function
    $result = send_verification_email($test_user_id, $test_email, $test_recovery_email, $conn);
    
    if ($result) {
        echo "✓ SUCCESS! Account verification email sent successfully!\n";
        echo "✓ Check your inbox at: $test_email\n";
        echo "✓ Look for subject: 'Verify Your PbRA Account'\n";
        echo "\n🎉 Account verification email functionality is working!\n";
    } else {
        echo "✗ FAILED to send verification email\n";
        echo "Check the error logs for more details\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Make sure your database is running and accessible\n";
}

echo "\nDone.\n";
?>