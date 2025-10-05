<?php
require_once __DIR__ . '/../../includes/auth.php';
include '../../mypbra_connect.php';

$task_id = $_GET['task_id'] ?? null;
if (!$task_id) {
    echo 'Invalid task ID.';
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['id'];
    $comment = $_POST['comment'] ?? '';
    $file_path = '';

    // Handle file upload
    if (!empty($_FILES['attachment']['name'])) {
        $target_dir = '../../uploads/task_comments/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = basename($_FILES['attachment']['name']);
        $target_file = $target_dir . time() . '_' . $file_name;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        } else {
            $error = 'Failed to upload attachment.';
        }
    }

    if (!$error && $comment) {
        $stmt = $conn->prepare('INSERT INTO task_comments (task_id, user_id, comment, attachment_path, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('iiss', $task_id, $user_id, $comment, $file_path);
        if ($stmt->execute()) {
            $success = 'Comment added successfully!';
        } else {
            $error = 'Error adding comment.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Task Comment</title>
    <link rel="stylesheet" href="../../distributetask/distributetask.css">
</head>

<body>
    <?php include '../../navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>Add Comment/Feedback</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars((string)$error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?= htmlspecialchars((string)$success) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="comment">Comment/Feedback:</label>
            <textarea name="comment" id="comment" rows="4" required></textarea>
            <br>
            <label for="attachment">Attach File (optional):</label>
            <input type="file" name="attachment" id="attachment" accept="image/*,application/pdf">
            <br>
            <button type="submit">Add Comment</button>
        </form>
    </div>
    <?php include '../../footer/footer.php'; ?>
</body>

</html>