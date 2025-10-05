<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

if (!isset($_SESSION['id']) || !in_array($_SESSION['user_type'], ['admin', 'super_admin'])) {
    header('Location: ../login/login.php');
    exit();
}

$report_id = $_GET['report_id'] ?? null;
if (!$report_id) {
    echo 'Invalid report ID.';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $priority = $_POST['priority'] ?? 'Normal';
    $stmt = $conn->prepare('UPDATE reports SET priority = ? WHERE id = ?');
    $stmt->bind_param('si', $priority, $report_id);
    $stmt->execute();
    $stmt->close();
    header('Location: list_reports.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prioritize Report</title>
    <link rel="stylesheet" href="../dashboard_template/style.css">
</head>

<body>
    <?php include '../dashboard_template/navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>Prioritize Report</h2>
        <form method="post">
            <label for="priority">Priority:</label>
            <select name="priority" id="priority">
                <option value="Low">Low</option>
                <option value="Normal" selected>Normal</option>
                <option value="High">High</option>
                <option value="Critical">Critical</option>
            </select>
            <br>
            <button type="submit">Set Priority</button>
        </form>
    </div>
    <?php include '../footer/footer.php'; ?>
</body>

</html>