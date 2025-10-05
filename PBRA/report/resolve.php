<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

if (!isset($_SESSION['id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: ../login/login.php');
    exit();
}

$report_id = $_GET['report_id'] ?? null;
if (!$report_id) {
    echo 'Invalid report ID.';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $status = ($action === 'resolve') ? 'Resolved' : 'Rejected';
    $stmt = $conn->prepare('UPDATE reports SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $report_id);
    $stmt->execute();
    $stmt->close();
    // Optionally, add notification logic here
    header('Location: list_reports.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resolve/Reject Report</title>
    <link rel="stylesheet" href="../dashboard_template/style.css">
</head>

<body>
    <?php include '../navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>Resolve or Reject Report</h2>
        <form method="post">
            <button type="submit" name="action" value="resolve">Resolve</button>
            <button type="submit" name="action" value="reject">Reject</button>
        </form>
    </div>
    <?php include '../footer/footer.php'; ?>
</body>

</html>