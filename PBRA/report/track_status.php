<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

if (!isset($_SESSION['id']) || ($_SESSION['user_type'] !== 'regular')) {
    header('Location: ../login/login.php');
    exit();
}

$user_id = $_SESSION['id'];

// Fetch user's reports
$stmt = $conn->prepare('SELECT id, category, description, status, evidence_path, created_at FROM reports WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Report Status</title>
    <link rel="stylesheet" href="../dashboard_template/style.css">
</head>

<body>
    <?php include '../dashboard_template/navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>Your Submitted Reports</h2>
        <?php if (empty($reports)): ?>
            <p>No reports submitted yet.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" style="width:100%;margin-top:16px;">
                <tr>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Evidence</th>
                    <th>Submitted At</th>
                </tr>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?= htmlspecialchars($report['category']) ?></td>
                        <td><?= htmlspecialchars($report['description']) ?></td>
                        <td><?= htmlspecialchars($report['status']) ?></td>
                        <td>
                            <?php if ($report['evidence_path']): ?>
                                <a href="<?= htmlspecialchars($report['evidence_path']) ?>" target="_blank">View</a>
                            <?php else: ?>
                                None
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($report['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <?php include '../footer/footer.php'; ?>
</body>

</html>