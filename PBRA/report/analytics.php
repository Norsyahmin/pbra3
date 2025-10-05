<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['user_type'] !== 'super_admin') {
    header('Location: ../login/login.php');
    exit();
}

// Simple analytics: count by status and category
$status_counts = [];
$category_counts = [];

$res = $conn->query('SELECT status, COUNT(*) as count FROM reports GROUP BY status');
while ($row = $res->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}
$res->close();

$res = $conn->query('SELECT category, COUNT(*) as count FROM reports GROUP BY category');
while ($row = $res->fetch_assoc()) {
    $category_counts[$row['category']] = $row['count'];
}
$res->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Analytics</title>
    <link rel="stylesheet" href="../dashboard_template/style.css">
</head>

<body>
    <?php include '../dashboard_template/navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>Report Analytics</h2>
        <h3>By Status</h3>
        <ul>
            <?php foreach ($status_counts as $status => $count): ?>
                <li><?= htmlspecialchars($status) ?>: <?= $count ?></li>
            <?php endforeach; ?>
        </ul>
        <h3>By Category</h3>
        <ul>
            <?php foreach ($category_counts as $cat => $count): ?>
                <li><?= htmlspecialchars($cat) ?>: <?= $count ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php include '../footer/footer.php'; ?>
</body>

</html>