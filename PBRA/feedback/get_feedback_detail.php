<?php
// Capture output as early as possible to avoid accidental HTML from includes
ob_start();
session_start();
// Ensure errors are not printed as HTML
ini_set('display_errors', '0');
error_reporting(E_ALL);
include '../mypbra_connect.php';

// First, check if required columns exist in the feedback table
$tableStructure = $conn->query("DESCRIBE feedback");
$columns = [];
if ($tableStructure) {
  while ($row = $tableStructure->fetch_assoc()) {
    $columns[$row['Field']] = true;
  }
}

// Also inspect the users table for optional profile columns
$userTableStructure = $conn->query("DESCRIBE users");
$userColumns = [];
if ($userTableStructure) {
  while ($r = $userTableStructure->fetch_assoc()) {
    $userColumns[$r['Field']] = true;
  }
}

// Safe JSON responder: clears any buffered output (logs it) and emits JSON
function send_json($payload, $status = 200) {
  // clear any accidental output and log it
  $buf = '';
  if (ob_get_level()) {
    $buf = ob_get_clean();
    if ($buf !== '') {
      error_log('get_feedback_detail: unexpected output before JSON response: ' . $buf);
    }
  }

  http_response_code($status);
  header('Content-Type: application/json');
  echo json_encode($payload);
  exit();
}

if (!isset($_SESSION['id'])) {
  error_log('get_feedback_detail: unauthenticated access attempt');
  send_json(['success' => false, 'error' => 'Not authenticated'], 401);
}

// Optional: require admin permissions to view details
$user_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($user_type);
$stmt->fetch();
$stmt->close();

// Temporary debug bypass: if ?debug=1 is present, allow any logged-in user to fetch details
$debugBypass = isset($_GET['debug']) && $_GET['debug'] === '1';
if ($debugBypass) {
  error_log('get_feedback_detail: debug bypass enabled for user_id=' . intval($user_id) . ' user_type=' . $user_type);
}

if (!$debugBypass && $user_type !== 'super_admin' && $user_type !== 'admin') {
  error_log('get_feedback_detail: forbidden user_id=' . intval($user_id) . ' user_type=' . $user_type);
  send_json(['success' => false, 'error' => 'Forbidden'], 403);
}

$timeColumnName = 'id';
if (isset($columns['submitted_at'])) {
  $timeColumnName = 'submitted_at';
} else if (isset($columns['created_at'])) {
  $timeColumnName = 'created_at';
}

$statusSelect = isset($columns['status']) ? 'f.status' : "NULL AS status";
$adminNotesSelect = isset($columns['admin_notes']) ? 'f.admin_notes' : "NULL AS admin_notes";
$assignedToSelect = isset($columns['assigned_to']) ? 'f.assigned_to' : "NULL AS assigned_to";


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  error_log('get_feedback_detail: invalid id param: ' . var_export($_GET['id'] ?? null, true));
  send_json(['success' => false, 'error' => 'Invalid id'], 400);
}

// Dynamically construct the SQL query based on existing columns
$sql_parts = [
  'f.id',
  'f.category',
  'f.message',
  'f.rating',
  'f.attachment',
  "COALESCE(f." . $timeColumnName . ", f.id) AS timestamp",
  'u.full_name',
  'u.email'
];

if (isset($columns['status'])) {
  $sql_parts[] = 'f.status';
} else {
  $sql_parts[] = "NULL AS status"; // Explicitly add NULL if column doesn't exist
}
if (isset($columns['admin_notes'])) {
  $sql_parts[] = 'f.admin_notes';
} else {
  $sql_parts[] = "NULL AS admin_notes";
}
if (isset($columns['assigned_to'])) {
  $sql_parts[] = 'f.assigned_to';
} else {
  $sql_parts[] = "NULL AS assigned_to";
}

// Optionally include user profile fields (work_experience, education) if they exist
if (isset($userColumns['work_experience'])) {
  $sql_parts[] = 'u.work_experience';
} else {
  $sql_parts[] = "NULL AS work_experience";
}

if (isset($userColumns['education'])) {
  $sql_parts[] = 'u.education';
} else {
  $sql_parts[] = "NULL AS education";
}

$columns_select_clause = implode(', ', $sql_parts);

$sql = "SELECT {$columns_select_clause} FROM feedback f LEFT JOIN users u ON f.user_id = u.id WHERE f.id = ? LIMIT 1";

$detail_stmt = $conn->prepare($sql);
if (!$detail_stmt) {
  error_log('get_feedback_detail: prepare failed: ' . $conn->error);
  send_json(['success' => false, 'error' => 'Prepare failed: ' . $conn->error], 500);
}
$detail_stmt->bind_param('i', $id);
$detail_stmt->execute();
$detail_result = $detail_stmt->get_result();
$row = $detail_result ? $detail_result->fetch_assoc() : null;
$detail_stmt->close();

if (!$row) {
  error_log('get_feedback_detail: not found id=' . $id);
  send_json(['success' => false, 'error' => 'Not found'], 404);
}

// Return only the fields the client needs, with fallback values
$data = [
  'id' => (int)$row['id'],
  'full_name' => $row['full_name'] ?? 'Unknown User',
  'email' => $row['email'] ?? '',
  'category' => $row['category'] ?? '',
  'rating' => isset($row['rating']) ? (int)$row['rating'] : null,
  'message' => $row['message'] ?? '',
  'attachment' => $row['attachment'] ?? null,
  'status' => $row['status'] ?? 'new', // Default to 'new' if column is NULL or not present
  'admin_notes' => $row['admin_notes'] ?? '', // Default to empty string
  'assigned_to' => isset($row['assigned_to']) ? (int)$row['assigned_to'] : null, // Default to null
  'timestamp' => $row['timestamp'] ?? null,
  'work_experience' => $row['work_experience'] ?? '',
  'education' => $row['education'] ?? '',
];

send_json(['success' => true, 'data' => $data], 200);
