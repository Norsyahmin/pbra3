<?php
require_once __DIR__ . '/../includes/auth.php';
session_destroy(); // Destroy all session data
header("Location: ../login/login.php"); // Redirect to login page
exit();
