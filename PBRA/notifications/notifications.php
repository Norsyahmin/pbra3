<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

if (!isset($_SESSION['id']) || ($_SESSION['user_type'] !== 'regular')) {
    header('Location: ../login/login.php');
    exit();
}

$user_id = $_SESSION['id'];

// Fetch notifications for the user
$stmt = $conn->prepare('SELECT message, url, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../dashboard_template/style.css">
</head>

<body>
    <?php include '../dashboard_template/navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>Your Notifications</h2>
        <?php if (empty($notifications)): ?>
            <p>No notifications found.</p>
        <?php else: ?>
            <ul style="list-style:none;padding:0;">
                <?php foreach ($notifications as $note): ?>
                    <li style="margin-bottom:12px;<?= $note['is_read'] ? 'opacity:0.7;' : '' ?>">
                        <strong><?= htmlspecialchars($note['message']) ?></strong>
                        <?php if ($note['url']): ?>
                            <a href="<?= htmlspecialchars($note['url']) ?>" target="_blank">View</a>
                        <?php endif; ?>
                        <span style="float:right; color:#888; font-size:12px;"><?= htmlspecialchars($note['created_at']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php include '../footer/footer.php'; ?>
</body>

</html>