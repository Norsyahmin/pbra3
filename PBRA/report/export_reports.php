<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';
include '../dashboard_template/navbar/navbar.php';

if (!isset($_SESSION['id']) || $_SESSION['user_type'] !== 'super_admin') {
    header('Location: ../login/login.php');
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=reports_export.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['User', 'Category', 'Description', 'Status', 'Priority', 'Evidence', 'Submitted At']);

$stmt = $conn->prepare('SELECT u.full_name, r.category, r.description, r.status, r.priority, r.evidence_path, r.created_at FROM reports r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['full_name'], $row['category'], $row['description'], $row['status'], $row['priority'] ?? '', $row['evidence_path'], $row['created_at']]);
}
$stmt->close();
fclose($output);
exit();
