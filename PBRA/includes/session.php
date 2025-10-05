<?php
// Lightweight session bootstrap used by pages that need session support
// This intentionally does NOT enforce authentication (use auth.php for that).

if (session_status() === PHP_SESSION_NONE) {
    // Start session if headers not sent; if headers already sent, we still attempt
    // to start the session — callers should include this before sending output.
    @session_start();
}

// Expose a helper to check auth without redirecting (optional convenience)
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in(): bool
    {
        return isset($_SESSION['id']);
    }
}

?>
