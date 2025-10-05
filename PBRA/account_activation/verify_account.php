<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';
include_once '../includes/language_setup.php';

// Check DB connection
if (!$conn || $conn->connect_error) {
    $_SESSION['login_error'] = "Database connection failed. Please try again later.";
    header("Location: ../login/login.php");
    exit;
}

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['login_error'] = "Invalid verification link. Please request a new verification email.";
    header('Location: ../login/login.php');
    exit;
}

$token_hash = hash("sha256", $token);

// Check if token exists
$stmt = $conn->prepare("SELECT user_id, email, recovery_email FROM email_verifications WHERE token_hash = ? AND expires_at > NOW()");
if (!$stmt) {
    $_SESSION['login_error'] = "Server error. Please try again later.";
    header("Location: ../login/login.php");
    exit;
}
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];

    $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("i", $user_id);

        if ($update_stmt->execute()) {
            $delete_stmt = $conn->prepare("DELETE FROM email_verifications WHERE user_id = ?");
            if ($delete_stmt) {
                $delete_stmt->bind_param("i", $user_id);
                $delete_stmt->execute();
                $delete_stmt->close();
            }

            $_SESSION['login_success'] = "Your account has been successfully verified. You can now log in.";
            header("Location: ../login/login.php?success=verified");
            exit;
        } else {
            $_SESSION['login_error'] = "Failed to verify your account. Please try again or contact support.";
        }
        $update_stmt->close();
    }
} else {
    $_SESSION['login_error'] = "Invalid or expired verification link. Verification links expire after 24 hours. Please request a new verification email.";
}

$stmt->close();
header("Location: ../login/login.php");
exit;
