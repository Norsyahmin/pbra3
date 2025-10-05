<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

if (!isset($_SESSION['id']) || !in_array($_SESSION['user_type'], ['admin', 'super_admin'])) {
    header('Location: ../login/login.php');
    exit();
}

// Fetch all reports
$stmt = $conn->prepare('SELECT r.id, u.full_name, r.category, r.description, r.status, r.evidence_path, r.created_at FROM reports r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC');
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
    <title>All Reports</title>
    <link rel="stylesheet" href="../dashboard_template/style.css">
</head>

<body>
    <?php include '../dashboard_template/navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>All Submitted Reports</h2>
        <?php if (empty($reports)): ?>
            <p>No reports found.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" style="width:100%;margin-top:16px;">
                <tr>
                    <th>User</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Evidence</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?= htmlspecialchars($report['full_name']) ?></td>
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
                        <td>
                            <a href="prioritize.php?report_id=<?= $report['id'] ?>">Prioritize</a> |
                            <a href="resolve.php?report_id=<?= $report['id'] ?>">Resolve/Reject</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <?php include '../footer/footer.php'; ?>
</body>

</html>