<?php
/**
 * Background OTP Email Worker
 * This script runs in the background to send OTP emails without blocking the login process
 */

// Get the temporary file path from command line argument
if ($argc < 2) {
    error_log('OTP background worker: Missing temp file argument');
    exit(1);
}

$tempFile = $argv[1];

if (!file_exists($tempFile)) {
    error_log('OTP background worker: Temp file not found: ' . $tempFile);
    exit(1);
}

try {
    // Load user data from temp file
    $userData = json_decode(file_get_contents($tempFile), true);
    
    if (!$userData || !isset($userData['email'], $userData['full_name'])) {
        error_log('OTP background worker: Invalid user data in temp file');
        unlink($tempFile);
        exit(1);
    }
    
    // Change to the correct directory for includes
    chdir(__DIR__);
    
    // Load environment variables if .env exists
    if (file_exists('../.env')) {
        $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
    
    // Start session to store OTP
    session_start();
    
    // Include required files
    require_once '../mypbra_connect.php';
    require_once 'otp_notification.php';
    
    // Create user array with the data we have
    $user = [
        'id' => $userData['id'],
        'email' => $userData['email'],
        'full_name' => $userData['full_name']
    ];
    
    // Get mailer instance
    $mail = require __DIR__ . '/../mailer.php';
    
    // Send OTP notification
    $result = showOtpNotification($user, $mail);
    
    if ($result) {
        error_log('OTP background worker: Successfully sent OTP to ' . $user['email']);
    } else {
        error_log('OTP background worker: Failed to send OTP to ' . $user['email']);
    }
    
    // Clean up temp file
    unlink($tempFile);
    
} catch (Exception $e) {
    error_log('OTP background worker error: ' . $e->getMessage());
    if (isset($tempFile) && file_exists($tempFile)) {
        unlink($tempFile);
    }
    exit(1);
}
?>