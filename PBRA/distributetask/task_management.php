<?php
require_once __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../mypbra_connect.php';


// Fetch all users for assignment dropdown
$users = [];
$user_res = $conn->query('SELECT id, full_name FROM users ORDER BY full_name ASC');
while ($u = $user_res->fetch_assoc()) {
    $users[] = $u;
}
$user_res->close();

// Handle new task submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $assignee = $_POST['assignee'] ?? $_SESSION['id'];
    $status = 'Pending';
    if ($title && $due_date && $assignee) {
        $stmt = $conn->prepare('INSERT INTO tasks (title, description, due_date, assignee_id, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->bind_param('sssiss', $title, $description, $due_date, $assignee, $status);
        if (!$stmt->execute()) {
            $error = 'Failed to add task.';
        }
        $stmt->close();
    } else {
        $error = 'Title, due date, and assignee are required.';
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare('UPDATE tasks SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $new_status, $task_id);
    $stmt->execute();
    $stmt->close();
}

// Filtering
$filter_status = $_GET['filter_status'] ?? '';
$filter_assignee = $_GET['filter_assignee'] ?? '';
$where = [];
if ($filter_status) $where[] = "t.status='" . $conn->real_escape_string($filter_status) . "'";
if ($filter_assignee) $where[] = "t.assignee_id='" . intval($filter_assignee) . "'";
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Fetch filtered tasks
$tasks = [];
$sql = "SELECT t.*, u.full_name as assignee_name FROM tasks t LEFT JOIN users u ON t.assignee_id = u.id $where_sql ORDER BY t.due_date ASC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $tasks[] = $row;
}
$res->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link rel="stylesheet" href="distributetask.css">
</head>

<body>
    <?php include '../dashboard_template/navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>Task Management</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <!-- Task Filters -->
        <form method="get" style="margin-bottom:16px;">
            <label>Status:</label>
            <select name="filter_status">
                <option value="">All</option>
                <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
            <label>Assignee:</label>
            <select name="filter_assignee">
                <option value="">All</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $filter_assignee == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filter</button>
        </form>
        <!-- Add Task -->
        <form method="post" style="margin-bottom:24px;">
            <input type="text" name="title" placeholder="Task Title" required>
            <input type="text" name="description" placeholder="Description">
            <input type="date" name="due_date" required>
            <select name="assignee" required>
                <option value="">Assign to...</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $u['id'] == $_SESSION['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add_task">Add Task</button>
        </form>
        <table border="1" cellpadding="8" style="width:100%;">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Assignee</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Update Status</th>
                <th>Comments</th>
            </tr>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['title']) ?></td>
                    <td><?= htmlspecialchars($task['description']) ?></td>
                    <td><?= htmlspecialchars($task['assignee_name'] ?? 'Unassigned') ?></td>
                    <td><?= htmlspecialchars($task['due_date']) ?></td>
                    <td><?= htmlspecialchars($task['status']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                            <select name="status">
                                <option value="Pending" <?= $task['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="In Progress" <?= $task['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= $task['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                    </td>
                    <td>
                        <a href="/task_management/task_management.php#comments-section-<?= $task['id'] ?>">View/Add Comments</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php include '../footer/footer.php'; ?>
</body>

</html>