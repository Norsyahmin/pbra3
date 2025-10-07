<?php
// Start a session (no redirect) so login processing can set session values.
require_once __DIR__ . '/../includes/session.php';
require_once '../mypbra_connect.php'; // Ensure this path is correct!
require_once '../login/otp_notification.php'; // Include the file containing the showOtpNotification function

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Email and password are required.";
        header("Location: login.php");
        exit();
    }

    // First check if the is_verified column exists
    $columnExists = false;
    // Using prepare/execute for SHOW COLUMNS is not typical and can be less robust.
    // A direct query is often used for schema checks, but let's stick to your pattern if it works.
    $checkColumnStmt = $conn->prepare("SHOW COLUMNS FROM users LIKE 'is_verified'");
    if ($checkColumnStmt) {
        $checkColumnStmt->execute();
        $checkColumnResult = $checkColumnStmt->get_result();
        // get_result() can return null in some setups; check before using
        if ($checkColumnResult !== null && $checkColumnResult->num_rows > 0) {
            $columnExists = true;
        }
        $checkColumnStmt->close(); // Close the statement for column check
    }

    // Prepare and execute SQL query with or without is_verified column
    if ($columnExists) {
        $stmt = $conn->prepare("SELECT id, email, password, full_name, profile_pic, is_verified, user_type, must_change_password FROM users WHERE email = ?");
    } else {
        // Column doesn't exist; select without is_verified/must_change_password
        $stmt = $conn->prepare("SELECT id, email, password, full_name, profile_pic, user_type FROM users WHERE email = ?");
    }

    if (!$stmt) {
        $_SESSION['login_error'] = "Database error: Unable to prepare statement.";
        header("Location: ../login/login.php");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verify credentials
    // get_result() may return null depending on mysqli driver; guard against that
    if ($result !== null && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if user is verified (if column exists)
            if (!$columnExists || $user['is_verified'] == 1) {
                // Store user info in session
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['profile_pic'] = !empty($user['profile_pic']) ? $user['profile_pic'] : 'profile/images/default-profile.jpg';
                $_SESSION['user_type'] = $user['user_type'];

                // Check if user must change password
                if (isset($user['must_change_password']) && $user['must_change_password'] == 1) {
                    $stmt->close();
                    header("Location: ../forget_password/change_temp_password.php");
                    exit();
                }
                // Temporarily disable OTP only for admin/super_admin: bypass OTP sending/verification for those roles
                // if (in_array($user['user_type'], ['admin', 'super_admin'], true)) {
                //     // OTP bypass for admin/super_admin: direct to homepage
                //     $stmt->close();
                //     header("Location: ../homepage/homepage.php");
                //     exit();
                // }

                // Original OTP sending/verification logic (kept commented for reference)

                // If admin or super_admin, send OTP and redirect to OTP verification page; otherwise go to homepage
                if (in_array($user['user_type'], ['admin', 'super_admin'], true)) {
                    // Load mailer only when needed and handle configuration errors
                    try {
                        $mail = require __DIR__ . '/../mailer.php';
                    } catch (\Exception $e) {
                        // Mailer misconfiguration should not expose internals; show a generic message
                        $_SESSION['login_error'] = "OTP mailer configuration error. Please contact IT support.";
                        $stmt->close();
                        header("Location: ../login/login.php");
                        exit();
                    }

                    // Send OTP synchronously - this is more reliable than async methods
                    $stmt->close();
                    
                    try {
                        // Send OTP notification immediately
                        $otpSent = showOtpNotification($user, $mail);
                        
                        if ($otpSent) {
                            // OTP sent successfully, redirect to verification page
                            header("Location: ../login/verify_otp.php");
                            exit();
                        } else {
                            // OTP sending failed
                            $_SESSION['login_error'] = "Failed to send OTP email. Please try again or contact IT support.";
                            header("Location: ../login/login.php");
                            exit();
                        }
                    } catch (\Exception $e) {
                        error_log('OTP send failed: ' . $e->getMessage());
                        $_SESSION['login_error'] = "Unable to send OTP email. Please try again.";
                        header("Location: ../login/login.php");
                        exit();
                    }
                } 
                else {
                    // Regular user: direct to homepage
                    $stmt->close();
                    header("Location: ../homepage/homepage.php");
                    exit();
                }


                // Default behavior for non-admin users: direct to homepage
                $stmt->close();
                header("Location: ../homepage/homepage.php");
                exit();
            } else {
                // User is not verified
                $_SESSION['login_error'] = "Please verify your email address before logging in. Check your inbox for the verification link.";
                header("Location: ../login/login.php");
                exit();
            }
        } else {
            // Password does not match
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: ../login/login.php");
            exit();
        }
    } else {
        // No user found with that email
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: ../login/login.php");
        exit();
    }

    $stmt->close();
} else {
    // If someone tries to access process_login.php directly without POST request
    header("Location: ../login/login.php");
    exit();
}
