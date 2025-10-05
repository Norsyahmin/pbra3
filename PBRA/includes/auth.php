<?php

/**
 * Centralized authentication include.
 * - Starts session if possible
 * - Redirects to /login/login.php if user not authenticated
 * - Sets $logged_in_user_id for convenience
 */

// Start session if headers not sent
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_start();
    }
}

// Determine login URL (absolute from webroot). Priority:
// 1) PHP constant LOGIN_URL
// 2) environment variable LOGIN_URL
// 3) .env file entry LOGIN_URL
// 4) fallback '/login/login.php'
$loginUrl = null;

if (defined('LOGIN_URL')) {
    $loginUrl = LOGIN_URL;
} elseif (false !== getenv('LOGIN_URL')) {
    $loginUrl = getenv('LOGIN_URL');
} else {
    // Try a simple .env parse if present (non-invasive, no external dependency)
    $envPath = __DIR__ . '/../.env';
    if (is_readable($envPath)) {
        $env = file_get_contents($envPath);
        if (preg_match('/^LOGIN_URL=(?:\"|\')?(.*?)(?:\"|\')?\s*$/m', $env, $m)) {
            $loginUrl = trim($m[1]);
        }
    }
}

if (empty($loginUrl)) {
    $loginUrl = '/login/login.php';
}

// If user not logged in, handle redirect or JSON response for AJAX calls.
if (!isset($_SESSION['id'])) {
    // Detect AJAX / JSON requests
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $acceptsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

    if ($isAjax || $acceptsJson) {
        // Return a JSON 401 response for API/AJAX endpoints
        if (!headers_sent()) {
            header('Content-Type: application/json', true, 401);
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit();
        }
    }

    // Non-AJAX requests: redirect to login page (use header if possible, otherwise JS fallback)
    if (!headers_sent()) {
        header('Location: ' . $loginUrl);
        exit();
    } else {
        echo "<script>window.location.href='" . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . "'</script>";
        echo "<noscript>Please <a href='" . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . "'>login</a>.</noscript>";
        exit();
    }
}

// expose logged-in user id for consumers
$logged_in_user_id = $_SESSION['id'] ?? null;
