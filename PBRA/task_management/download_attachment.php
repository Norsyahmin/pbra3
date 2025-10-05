<?php
// Secure attachment download
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../mypbra_connect.php';

$user_id = $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? null;
$allowed_roles = ['regular', 'admin', 'super_admin'];
if ($user_id === null || !in_array($user_role, $allowed_roles, true)) {
    header('Location: /login/login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid attachment id.';
    exit;
}

// Prepare and check
$stmt = $conn->prepare("SELECT * FROM task_attachments WHERE id = ? LIMIT 1");
if (!$stmt) {
    http_response_code(500);
    echo 'Database error.';
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res === null || $res->num_rows === 0) {
    http_response_code(404);
    echo 'Attachment not found.';
    exit;
}
$att = $res->fetch_assoc();
$task_id = intval($att['task_id']);

// Check permission: admin/super_admin OR creator OR assignee
$allowed = false;
if ($user_role === 'admin' || $user_role === 'super_admin') {
    $allowed = true;
} else {
    // check creator
    $c = $conn->prepare("SELECT created_by FROM tasks WHERE id = ? LIMIT 1");
    if ($c) {
        $c->bind_param('i', $task_id);
        $c->execute();
        $cres = $c->get_result();
        if ($cres !== null && $row = $cres->fetch_assoc()) {
            if (intval($row['created_by']) === intval($user_id)) $allowed = true;
        }
        $c->close();
    }
    // check assignment
    if (!$allowed) {
        $a = $conn->prepare("SELECT 1 FROM task_assignments WHERE task_id = ? AND user_id = ? LIMIT 1");
        if ($a) {
            $a->bind_param('ii', $task_id, $user_id);
            $a->execute();
            $ares = $a->get_result();
            if ($ares !== null && $ares->num_rows > 0) $allowed = true;
            $a->close();
        }
    }
}

if (!$allowed) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$uploadsDir = __DIR__ . '/../uploads/task_attachments/';
$file = $uploadsDir . ($att['stored_name'] ?? '');
if (!is_file($file)) {
    http_response_code(404);
    echo 'File not found on disk.';
    exit;
}

$original = $att['original_name'] ?? 'attachment';
$mime = $att['mime_type'] ?? null;
if (!$mime) {
    $f = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($f, $file) ?: 'application/octet-stream';
    finfo_close($f);
}

// Stream file with appropriate headers
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . str_replace('"', '', basename($original)) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
