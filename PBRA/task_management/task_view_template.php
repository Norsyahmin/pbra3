<?php
// Expects $t (task row), $assignees (mysqli_result), $history (mysqli_result)
// Guard: ensure session & allowed roles
require_once __DIR__ . '/../includes/auth.php';
$user_id = $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? null;
$allowed_roles = ['regular', 'admin', 'super_admin'];
if ($user_id === null || !in_array($user_role, $allowed_roles, true)) {
    header('Location: /login/login.php');
    exit;
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Task #<?php echo intval($t['id']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/task_management/task_management.css">
    <style>
        body {
            font-family: Arial;
            padding: 16px
        }

        .btn {
            padding: 6px 10px
        }
    </style>
</head>

<body>
    <a href="task_management.php">← Back</a>
    <h2><?php echo htmlspecialchars($t['title'] ?? ''); ?></h2>
    <div><strong>Status:</strong> <?php echo htmlspecialchars($t['status'] ?? ''); ?></div>
    <div><strong>Priority:</strong> <?php echo htmlspecialchars($t['priority'] ?? ''); ?></div>
    <div><strong>Start:</strong> <?php echo htmlspecialchars($t['start_date'] ?? ''); ?></div>
    <div><strong>End:</strong> <?php echo htmlspecialchars($t['end_date'] ?? ''); ?></div>
    <div><strong>Created by:</strong> <?php echo htmlspecialchars($t['creator_name'] ?? ''); ?></div>
    <h3>Description</h3>
    <div><?php echo nl2br(htmlspecialchars($t['description'])); ?></div>

    <h3>Assignees</h3>
    <ul>
        <?php if ($assignees && $assignees !== false):
            while ($u = $assignees->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($u['full_name'] ?? ''); ?></li>
            <?php endwhile;
        else: ?>
            <li>No assignees.</li>
        <?php endif; ?>
    </ul>

    <h3>Attachments</h3>
    <ul>
        <?php
        // fetch attachments for this task
        $atts = $conn->query("SELECT * FROM task_attachments WHERE task_id=" . intval($t['id']) . " ORDER BY uploaded_at DESC");
        if ($atts && $atts->num_rows > 0) {
            while ($a = $atts->fetch_assoc()) {
                $url = '/task_management/download_attachment.php?id=' . intval($a['id']);
                echo '<li><a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($a['original_name']) . '</a> (' . intval($a['size']) . ' bytes)</li>';
            }
        } else {
            echo '<li>No attachments.</li>';
        }
        ?>
    </ul>

    <h3>History</h3>
    <ul>
        <?php if ($history && $history !== false):
            while ($h = $history->fetch_assoc()): ?>
                <li>[<?php echo htmlspecialchars($h['created_at'] ?? ''); ?>] <?php echo htmlspecialchars($h['action'] ?? ''); ?> by <?php echo htmlspecialchars($h['actor_name'] ?? ''); ?> — <?php echo htmlspecialchars($h['notes'] ?? ''); ?></li>
            <?php endwhile;
        else: ?>
            <li>No history.</li>
        <?php endif; ?>
    </ul>

    <?php $session_role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'regular';
    if ($session_role === 'admin' || $session_role === 'super_admin'): ?>
        <h3>Admin actions</h3>
        <form method="post" action="process_task.php">
            <input type="hidden" name="action" value="admin_review">
            <input type="hidden" name="id" value="<?php echo intval($t['id']); ?>">
            <label>Decision: <select name="decision">
                    <option value="approve">Approve completion</option>
                    <option value="reject">Reject</option>
                </select></label><br>
            <label>Notes<br><textarea name="notes" rows="3"></textarea></label><br>
            <button class="btn" type="submit">Submit</button>
        </form>
        <h4>Pending appeals</h4>
        <?php
        $aps = $conn->query("SELECT ta.*, u.full_name FROM task_appeals ta JOIN users u ON u.id=ta.user_id WHERE ta.task_id=" . intval($t['id']) . " AND ta.status='pending'");
        if ($aps && $aps->num_rows > 0) {
            while ($ap = $aps->fetch_assoc()) {
                echo '<div style="border:1px solid #ddd;padding:8px;margin:6px 0">';
                echo '<strong>Appeal #' . intval($ap['id']) . ' by ' . htmlspecialchars($ap['full_name']) . '</strong><div>' . htmlspecialchars($ap['reason']) . '</div>';
                echo '<form method="post" action="process_task.php"><input type="hidden" name="action" value="appeal_review"><input type="hidden" name="id" value="' . intval($t['id']) . '"><input type="hidden" name="appeal_id" value="' . intval($ap['id']) . '"><label>Decision <select name="decision"><option value="approve">Approve</option><option value="reject">Deny</option></select></label><br><label>Notes<br><textarea name="notes"></textarea></label><br><button class="btn" type="submit">Submit</button></form>';
                echo '</div>';
            }
        } else {
            echo '<p>No pending appeals.</p>';
        }
        ?>
    <?php endif; ?>

    <?php if ($session_role === 'super_admin'): ?>
        <h3>Super Admin</h3>
        <?php if (($t['status'] ?? '') === 'archived'): ?>
            <form method="post" action="process_task.php" onsubmit="return confirm('Unarchive this task?');">
                <input type="hidden" name="action" value="unarchive">
                <input type="hidden" name="id" value="<?php echo intval($t['id'] ?? 0); ?>">
                <button class="btn" type="submit">Unarchive Task</button>
            </form>
        <?php else: ?>
            <form method="post" action="process_task.php" onsubmit="return confirm('Archive this task?');">
                <input type="hidden" name="action" value="archive">
                <input type="hidden" name="id" value="<?php echo intval($t['id'] ?? 0); ?>">
                <button class="btn" type="submit">Archive Task</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

</body>

</html>