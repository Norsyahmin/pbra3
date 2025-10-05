<?php
session_start();
include '../mypbra_connect.php';

// Check if user is admin
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// Verify admin permissions
$user_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_type);
$stmt->fetch();
$stmt->close();

if ($user_type !== 'admin') {
    header("Location: ../homepage/homepage.php");
    exit();
}

// Turn off display of errors for CSV output so warnings/deprecation messages are not injected into the file
ini_set('display_errors', '0');
// keep server-side logging but hide warnings/notices/deprecations from output
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="feedback_export_'.date('Y-m-d').'.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV header (provide explicit delimiter, enclosure and escape)
fputcsv($output, [
    'ID', 
    'Submitted By', 
    'Email',
    'Category', 
    'Rating', 
    'Message',
    'Has Attachment',
    'Status',
    'Admin Notes',
    'Assigned To',
    'Submission Date'
], ',', '"', '\\');

// Get all feedback with user info
$query = "SELECT f.*, u.full_name, u.email, a.full_name as assigned_name
          FROM feedback f 
          JOIN users u ON f.user_id = u.id 
          LEFT JOIN users a ON f.assigned_to = a.id
          ORDER BY f.submitted_at DESC"; // Changed created_at to submitted_at

$result = $conn->query($query);

// Output each row
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['full_name'],
        $row['email'],
        str_replace('_', ' ', ucfirst($row['category'])),
        $row['rating'],
        $row['message'],
        $row['attachment'] ? 'Yes' : 'No',
        $row['status'] ?: 'new',
        $row['admin_notes'] ?? '',
        $row['assigned_name'] ?: 'Not assigned',
        $row['submitted_at'] // Changed created_at to submitted_at
    ], ',', '"', '\\');
}

// Close the output stream
fclose($output);
exit();
?>
