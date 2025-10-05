<?php
require_once __DIR__ . '/../../includes/auth.php';
include '../../mypbra_connect.php';

$task_id = $_GET['task_id'] ?? null;
if (!$task_id) {
    echo 'Invalid task ID.';
    exit();
}

$stmt = $conn->prepare('SELECT tc.comment, tc.attachment_path, tc.created_at, u.full_name FROM task_comments tc JOIN users u ON tc.user_id = u.id WHERE tc.task_id = ? ORDER BY tc.created_at ASC');
$stmt->bind_param('i', $task_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Task Comments</title>
    <link rel="stylesheet" href="../../distributetask/distributetask.css">
</head>

<body>
    <?php include '../../navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>Task Comments & Feedback</h2>
        <?php if (empty($comments)): ?>
            <p>No comments yet.</p>
        <?php else: ?>
            <ul style="list-style:none;padding:0;">
                <?php foreach ($comments as $c): ?>
                    <li style="margin-bottom:16px;">
                        <strong><?= htmlspecialchars((string)$c['full_name']) ?>:</strong>
                        <span><?= htmlspecialchars((string)$c['comment']) ?></span>
                        <?php if (!empty($c['attachment_path'])): ?>
                            <br><a href="<?= htmlspecialchars((string)$c['attachment_path']) ?>" target="_blank">View Attachment</a>
                        <?php endif; ?>
                        <span style="float:right; color:#888; font-size:12px;">
                            <?= htmlspecialchars((string)$c['created_at']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="add_comment.php?task_id=<?= $task_id ?>">Add Comment/Feedback</a>
    </div>
    <?php include '../../footer/footer.php'; ?>
</body>

</html>