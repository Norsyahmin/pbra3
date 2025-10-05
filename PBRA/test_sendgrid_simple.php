<?php
/**
 * Simple SendGrid Test Script
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

echo "Testing SendGrid Configuration...\n";
echo "=================================\n";

// Check SendGrid API key
$apiKey = getenv('SENDGRID_API_KEY');
if (empty($apiKey)) {
    echo "✗ ERROR: SENDGRID_API_KEY not found\n";
    exit(1);
}
echo "✓ SendGrid API Key: " . substr($apiKey, 0, 10) . "...\n";

// Check sender email
$fromEmail = getenv('MAIL_FROM');
if (empty($fromEmail)) {
    echo "✗ ERROR: MAIL_FROM not found\n";
    exit(1);
}
echo "✓ From Email: $fromEmail\n";

// Test SendGrid API directly
function testSendGridAPI($apiKey, $fromEmail, $fromName) {
    $toEmail = $fromEmail; // Send to yourself for testing
    
    $payload = [
        'personalizations' => [[
            'to' => [['email' => $toEmail, 'name' => 'Test Recipient']],
            'subject' => 'PBRA SendGrid Test - ' . date('Y-m-d H:i:s')
        ]],
        'from' => ['email' => $fromEmail, 'name' => $fromName],
        'content' => [
            [
                'type' => 'text/plain',
                'value' => 'SendGrid Test Success! Your SendGrid integration is working correctly. Sent at: ' . date('Y-m-d H:i:s')
            ],
            [
                'type' => 'text/html',
                'value' => '<h2>SendGrid Test Success!</h2><p>Your SendGrid integration is working correctly.</p><p>Sent at: ' . date('Y-m-d H:i:s') . '</p>'
            ]
        ]
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'success' => ($httpCode >= 200 && $httpCode < 300),
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

$fromName = getenv('MAIL_FROM_NAME') ?: 'PBRA System';
echo "✓ From Name: $fromName\n";
echo "\nSending test email...\n";

$result = testSendGridAPI($apiKey, $fromEmail, $fromName);

if ($result['success']) {
    echo "✓ SUCCESS! Test email sent successfully!\n";
    echo "✓ HTTP Code: " . $result['http_code'] . "\n";
    echo "✓ Check your inbox at: $fromEmail\n";
    echo "\n🎉 Your SendGrid integration is working perfectly!\n";
    echo "\nNext steps:\n";
    echo "- Your PBRA application can now send emails through SendGrid\n";
    echo "- All email features (password reset, notifications, etc.) will use SendGrid\n";
    echo "- Check your SendGrid dashboard for email statistics\n";
} else {
    echo "✗ FAILED to send test email\n";
    echo "✗ HTTP Code: " . $result['http_code'] . "\n";
    echo "✗ Response: " . $result['response'] . "\n";
    if ($result['error']) {
        echo "✗ cURL Error: " . $result['error'] . "\n";
    }
    
    echo "\nTroubleshooting:\n";
    echo "1. Verify your SendGrid API key is correct\n";
    echo "2. Ensure your sender email ($fromEmail) is verified in SendGrid\n";
    echo "3. Check SendGrid dashboard for any account issues\n";
}

echo "\nDone.\n";
?>