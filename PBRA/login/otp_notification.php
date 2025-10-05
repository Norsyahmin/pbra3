<?php
function showOtpNotification($user, $mail)
{
    // Ensure session is active (do not redirect)
    if (session_status() === PHP_SESSION_NONE) {
        require_once __DIR__ . '/../includes/session.php';
    }

    try {
        // Generate OTP and store expiry (5 minutes)
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300; // 5 minutes

        // Clear previous recipients/attachments to avoid accidental reuse
        if (method_exists($mail, 'clearAllRecipients')) {
            $mail->clearAllRecipients();
        } else {
            $mail->clearAddresses();
            $mail->clearReplyTos();
        }
        if (method_exists($mail, 'clearAttachments')) {
            $mail->clearAttachments();
        }

        // Use the verified SendGrid sender from environment
        $fromEmail = getenv('MAIL_FROM') ?: 'pbrauser.help@gmail.com';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'Politeknik Brunei Role Appointment System';
        $mail->setFrom($fromEmail, $fromName);
        // Send OTP to primary email (explicit)
        $mail->addAddress($user['email']);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Your PBRA Admin Login OTP";
        $mail->Body = "Welcome to PBRA System, " . htmlspecialchars($user['full_name']) . "! <br><br>Enter this code to continue logging in as admin: <b>$otp</b><br>This code will expire in 5 minutes. <br>If you didn't attempt to log in, you can safely ignore this email.";
        $mail->AltBody = "Your OTP for admin login is: $otp\nThis code will expire in 5 minutes.";

        $mail->send();
        return true;
    } catch (\Exception $e) {
        // If the mail adapter exposes a last-error method, include it for debugging
        $adapterError = '';
        try {
            if (is_object($mail) && method_exists($mail, 'getLastError')) {
                $adapterError = ' AdapterLastError: ' . $mail->getLastError();
            }
        } catch (\Throwable $t) {
            // ignore
        }
        error_log("Error sending OTP email to " . $user['email'] . ": " . $e->getMessage() . $adapterError);
        return false;
    }
}
