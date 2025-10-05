<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../mypbra_connect.php';

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'regular';

// Authorization guard: ensure only logged-in users with allowed roles can use this handler
$allowed_roles = ['regular', 'admin', 'super_admin'];
if ($user_id === null || !in_array($user_role, $allowed_roles, true)) {
    // For AJAX or POST requests, send 403; for simple GETs redirect to login
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Location: /login/login.php');
    } else {
        http_response_code(403);
        echo 'Forbidden';
    }
    exit;
}

function add_history($conn, $task_id, $action, $actor_id, $notes = '')
{
    // Use safe prepare to avoid calling methods on null
    $stmt = safe_prepare($conn, "INSERT INTO task_history (task_id, action, actor_id, notes, created_at) VALUES (?,?,?,?,NOW())");
    if ($stmt) {
        // types: int, string, int, string
        $stmt->bind_param('isis', $task_id, $action, $actor_id, $notes);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log('add_history: prepare failed: ' . ($conn->error ?? 'unknown'));
    }
}

/**
 * Prepare a statement and optionally throw on failure.
 * Returns mysqli_stmt or null.
 */
function safe_prepare(mysqli $conn, string $sql, bool $throw = false)
{
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        if ($throw) {
            throw new Exception('DB prepare failed: ' . $conn->error);
        }
        return null;
    }
    return $stmt;
}

if ($action === 'create' && ($user_role === 'admin' || $user_role === 'super_admin')) {
    // Basic server-side validation and sanitization
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $start = trim($_POST['start_date'] ?? '');
    $end = trim($_POST['end_date'] ?? '');
    $priority = $_POST['priority'] ?? 'low';
    $assignees = $_POST['assignees'] ?? [];

    // Handle task type
    $task_type = trim($_POST['task_type'] ?? '');
    $custom_task_type = trim($_POST['custom_task_type'] ?? '');

    // If "other" is selected, use the custom task type
    if ($task_type === 'other' && $custom_task_type !== '') {
        $task_type = $custom_task_type;
    }

    // Add task type to description if provided
    if ($task_type !== '') {
        $desc = "Task Type: " . $task_type . "\n\n" . $desc;
    }

    // validate title
    if ($title === '') {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Title is required.'];
        header('Location: /task_management/create_task.php');
        exit;
    }
    if (mb_strlen($title) > 255) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Title must be 255 characters or fewer.'];
        header('Location: /task_management/create_task.php');
        exit;
    }

    // validate priority
    $allowed_priorities = ['low', 'medium', 'high'];
    if (!in_array($priority, $allowed_priorities, true)) $priority = 'low';

    // validate dates (basic YYYY-MM-DD format)
    $start_val = ($start !== '') ? $start : null;
    $end_val = ($end !== '') ? $end : null;
    if ($start_val && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_val)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid start date format. Use YYYY-MM-DD.'];
        header('Location: /task_management/create_task.php');
        exit;
    }
    if ($end_val && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_val)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid end date format. Use YYYY-MM-DD.'];
        header('Location: /task_management/create_task.php');
        exit;
    }
    if ($start_val && $end_val && strtotime($end_val) < strtotime($start_val)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'End date cannot be before start date.'];
        header('Location: /task_management/create_task.php');
        exit;
    }

    // Use a transaction so inserts are atomic
    $conn->begin_transaction();
    try {
        $stmt = safe_prepare($conn, "INSERT INTO tasks (title, description, start_date, end_date, priority, status, created_by, created_at) VALUES (?,?,?,?,?, 'pending', ?, NOW())", true);
        // types: title, desc, start, end, priority, created_by
        $stmt->bind_param('sssssi', $title, $desc, $start_val, $end_val, $priority, $user_id);
        $stmt->execute();
        $task_id = $stmt->insert_id;

        // Handle uploaded attachments (optional)
        if (!empty($_FILES['attachments']) && isset($_FILES['attachments']['name']) && is_array($_FILES['attachments']['name'])) {
            // Basic upload constraints
            $allowed_mime_prefixes = ['image/', 'application/', 'text/'];
            $max_per_file = 10 * 1024 * 1024; // 10 MB per file

            $uploadDir = __DIR__ . '/../uploads/task_attachments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $insAttach = safe_prepare($conn, "INSERT INTO task_attachments (task_id, stored_name, original_name, mime_type, size, uploaded_at) VALUES (?,?,?,?,?,NOW())", true);

            // iterate uploaded files
            for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
                $origName = $_FILES['attachments']['name'][$i];
                $tmpName = $_FILES['attachments']['tmp_name'][$i] ?? '';
                $error = $_FILES['attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                $size = $_FILES['attachments']['size'][$i] ?? 0;
                $mime = $_FILES['attachments']['type'][$i] ?? null;

                if ($error !== UPLOAD_ERR_OK) continue; // skip files with errors
                if ($size <= 0 || $size > $max_per_file) continue; // skip oversized or empty files

                // basic mime check (prefix match)
                $ok = false;
                if ($mime) {
                    foreach ($allowed_mime_prefixes as $p) {
                        if (stripos($mime, $p) === 0) {
                            $ok = true;
                            break;
                        }
                    }
                }
                if (!$ok) continue;

                // generate unique stored name
                $ext = pathinfo($origName, PATHINFO_EXTENSION);
                $stored = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
                $dest = $uploadDir . $stored;

                if (move_uploaded_file($tmpName, $dest)) {
                    $insAttach->bind_param('isssi', $task_id, $stored, $origName, $mime, $size);
                    $insAttach->execute();
                }
            }
        }

        // Assignments: validate assignees and insert
        $assignedUsers = [];
        if (is_array($assignees) && count($assignees) > 0) {
            $ins = safe_prepare($conn, "INSERT INTO task_assignments (task_id, user_id, assigned_at) VALUES (?,?,NOW())", true);
            $chk = safe_prepare($conn, "SELECT id, email, full_name FROM users WHERE id = ? LIMIT 1", true);
            // prepare notification insert if notifications table exists
            $notifStmt = null;
            $tmpNot = $conn->query("SHOW TABLES LIKE 'notifications'");
            $hasNotificationsTable = ($tmpNot instanceof mysqli_result && $tmpNot->num_rows > 0);
            if ($hasNotificationsTable) {
                $notifStmt = safe_prepare($conn, "INSERT INTO notifications (user_id, message, url, is_read, created_at) VALUES (?,?,?,?,NOW())", false);
            }

            $assignedCount = 0;
            foreach ($assignees as $a) {
                $uid = intval($a);
                if ($uid <= 0) continue;
                // ensure user exists and fetch email/name
                if ($chk) {
                    $chk->bind_param('i', $uid);
                    $chk->execute();
                    $res = $chk->get_result();
                    if (!($res instanceof mysqli_result) || $res->num_rows === 0) continue; // skip invalid user id
                    $userRow = $res->fetch_assoc();
                } else {
                    // if user lookup can't be prepared, skip assigning this user
                    continue;
                }

                $ins->bind_param('ii', $task_id, $uid);
                if (!$ins->execute()) {
                    // if insert failed, continue and collect error later
                    continue;
                }
                $assignedCount++;
                $assignedUsers[] = $userRow; // id, email, full_name

                // create a notification for the assigned user if table exists
                if ($notifStmt) {
                    $message = "You have been assigned a new task: " . $title;
                    $url = "/task_management/process_task.php?action=view&id=" . intval($task_id);
                    $is_read = 0;
                    // notifStmt may be null if prepare failed
                    if ($notifStmt) {
                        $notifStmt->bind_param('issi', $uid, $message, $url, $is_read);
                        $notifStmt->execute();
                    }
                }
            }

            // if assignees were provided but none were assigned, throw
            if (count($assignees) > 0 && $assignedCount === 0) {
                throw new Exception('No valid assignees were found or inserts failed.');
            }
        }

        add_history($conn, $task_id, 'created', $user_id, 'Task created and assigned');
        $conn->commit();

        // After successful commit, optionally send email notifications to assigned users
        if (!empty($assignedUsers)) {
            try {
                // require mailer config (returns configured PHPMailer instance)
                $baseMailer = require __DIR__ . '/../mailer.php';
                foreach ($assignedUsers as $au) {
                    try {
                        $m = clone $baseMailer;
                        $fromEmail = $_ENV['MAIL_FROM'] ?? ($_ENV['SMTP_USERNAME'] ?? 'no-reply@example.com');
                        $fromName = $_ENV['MAIL_FROM_NAME'] ?? 'PBRA';
                        $m->setFrom($fromEmail, $fromName);
                        $m->addAddress($au['email'], $au['full_name']);
                        $m->Subject = "New task assigned: " . $title;
                        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $link = "http://" . $host . "/task_management/process_task.php?action=view&id=" . intval($task_id);
                        $body = "Hello " . htmlspecialchars($au['full_name']) . ",<br><br>You have been assigned a new task: <strong>" . htmlspecialchars($title) . "</strong>.<br><br><a href='" . $link . "'>View task</a>";
                        $m->Body = $body;
                        $m->send();
                    } catch (Exception $me) {
                        error_log('Failed to send task assignment email to user ' . intval($au['id']) . ': ' . $me->getMessage());
                        // continue sending to other users
                    }
                }
            } catch (Exception $e) {
                // mailer setup failed; log and continue
                error_log('Mailer setup failed after task creation: ' . $e->getMessage());
            }
        }

        $assignedNames = [];
        foreach ($assignedUsers as $au) {
            if (!empty($au['full_name'])) $assignedNames[] = $au['full_name'];
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Task created successfully.', 'assigned' => $assignedNames];
        header('Location: /task_management/task_management.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to create task: ' . $e->getMessage()];
        header('Location: /task_management/create_task.php');
        exit;
    }
}

if ($action === 'mark_complete' && $user_id) {
    $task_id = intval($_POST['id'] ?? 0);
    // Mark as completed but set status to 'pending_review' for admin review
    $stmt = safe_prepare($conn, "UPDATE tasks SET status = 'pending_review' WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $task_id);
        $stmt->execute();
        $stmt->close();
    }
    add_history($conn, $task_id, 'marked_complete', $user_id, 'User marked task complete; awaiting admin review');
    header('Location: /task_management/task_management.php');
    exit;
}

if ($action === 'appeal' && $user_id) {
    $task_id = intval($_POST['id'] ?? 0);
    $reason = $_POST['reason'] ?? '';
    $stmt = safe_prepare($conn, "INSERT INTO task_appeals (task_id, user_id, reason, status, created_at) VALUES (?,?,?,?,NOW())", true);
    $stat = 'pending';
    $stmt->bind_param('iiss', $task_id, $user_id, $reason, $stat);
    $stmt->execute();
    $appeal_id = $stmt->insert_id ?? 0;
    // Handle uploaded appeal attachments if present
    if (!empty($_FILES['attachments']) && isset($_FILES['attachments']['name']) && is_array($_FILES['attachments']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/task_appeal_attachments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Check if table exists to record attachments
        $tmp = $conn->query("SHOW TABLES LIKE 'task_appeal_attachments'");
        $hasAppealAttachTable = ($tmp instanceof mysqli_result && $tmp->num_rows > 0);
        $insAttach = null;
        if ($hasAppealAttachTable) {
            $insAttach = safe_prepare($conn, "INSERT INTO task_appeal_attachments (appeal_id, stored_name, original_name, mime_type, size, uploaded_at) VALUES (?,?,?,?,?,NOW())", true);
        }

        $max_per_file = 10 * 1024 * 1024; // 10MB
        for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
            $orig = $_FILES['attachments']['name'][$i];
            $tmpn = $_FILES['attachments']['tmp_name'][$i] ?? '';
            $err = $_FILES['attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            $size = $_FILES['attachments']['size'][$i] ?? 0;
            $mime = $_FILES['attachments']['type'][$i] ?? null;
            if ($err !== UPLOAD_ERR_OK) continue;
            if ($size <= 0 || $size > $max_per_file) continue;

            $ext = pathinfo($orig, PATHINFO_EXTENSION);
            $stored = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
            $dest = $uploadDir . $stored;
            if (move_uploaded_file($tmpn, $dest)) {
                if ($insAttach) {
                    $insAttach->bind_param('isssi', $appeal_id, $stored, $orig, $mime, $size);
                    $insAttach->execute();
                }
            }
        }
    }
    add_history($conn, $task_id, 'appeal_requested', $user_id, $reason);
    header('Location: /task_management/task_management.php');
    exit;
}

if ($action === 'view') {
    $task_id = intval($_GET['id'] ?? 0);
    $tq = $conn->query("SELECT t.*, u.full_name as creator_name FROM tasks t LEFT JOIN users u ON u.id = t.created_by WHERE t.id=" . $task_id);
    $t = ($tq instanceof mysqli_result) ? $tq->fetch_assoc() : null;
    $assignees = $conn->query("SELECT u.id,u.full_name FROM task_assignments a JOIN users u ON u.id=a.user_id WHERE a.task_id=" . $task_id);
    $history = $conn->query("SELECT th.*, u.full_name as actor_name FROM task_history th LEFT JOIN users u ON u.id=th.actor_id WHERE th.task_id=" . $task_id . " ORDER BY th.created_at DESC");

    include __DIR__ . '/task_view_template.php';
    exit;
}

// AJAX endpoint to get task details for modal
if ($action === 'get_task_details' && $user_id) {
    header('Content-Type: application/json');

    $task_id = intval($_GET['id'] ?? 0);
    if ($task_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit;
    }

    // Fetch task details
    $task_query = $conn->query("SELECT t.*, u.full_name as creator_name FROM tasks t LEFT JOIN users u ON u.id = t.created_by WHERE t.id=" . $task_id);
    if (!($task_query instanceof mysqli_result) || $task_query->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Task not found']);
        exit;
    }
    $task = $task_query->fetch_assoc();

    // Fetch assignees
    $assignees = [];
    $assignee_query = $conn->query("SELECT u.id, u.full_name FROM task_assignments a JOIN users u ON u.id=a.user_id WHERE a.task_id=" . $task_id);
    if ($assignee_query instanceof mysqli_result) {
        while ($row = $assignee_query->fetch_assoc()) {
            $assignees[] = $row;
        }
    }

    // Fetch attachments
    $attachments = [];
    $attachment_query = $conn->query("SELECT * FROM task_attachments WHERE task_id=" . $task_id . " ORDER BY uploaded_at DESC");
    if ($attachment_query instanceof mysqli_result) {
        while ($row = $attachment_query->fetch_assoc()) {
            $attachments[] = $row;
        }
    }

    // We'll attach attachments to each appeal entry below (if available)

    // Fetch history
    $history = [];
    $history_query = $conn->query("SELECT th.*, u.full_name as actor_name FROM task_history th LEFT JOIN users u ON u.id=th.actor_id WHERE th.task_id=" . $task_id . " ORDER BY th.created_at DESC");
    if ($history_query instanceof mysqli_result) {
        while ($row = $history_query->fetch_assoc()) {
            $history[] = $row;
        }
    }

    // Fetch pending appeals (for admin) and include attachments for each appeal
    $appeals = [];
    if ($user_role === 'admin' || $user_role === 'super_admin') {
        $appeal_query = $conn->query("SELECT ta.*, u.full_name FROM task_appeals ta JOIN users u ON u.id=ta.user_id WHERE ta.task_id=" . $task_id . " AND ta.status='pending'");
        if ($appeal_query instanceof mysqli_result) {
            while ($row = $appeal_query->fetch_assoc()) {
                $row['attachments'] = [];
                // fetch attachments for this appeal if table exists
                $tmpAA = $conn->query("SHOW TABLES LIKE 'task_appeal_attachments'");
                $hasAA = ($tmpAA instanceof mysqli_result && $tmpAA->num_rows > 0);
                if ($hasAA && !empty($row['id'])) {
                    $aq = $conn->query("SELECT * FROM task_appeal_attachments WHERE appeal_id = " . intval($row['id']) . " ORDER BY uploaded_at ASC");
                    if ($aq instanceof mysqli_result) {
                        while ($ar = $aq->fetch_assoc()) $row['attachments'][] = $ar;
                    }
                }
                $appeals[] = $row;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'task' => $task,
        'assignees' => $assignees,
        'attachments' => $attachments,
        'history' => $history,
        'appeals' => $appeals,
        'user_role' => $user_role
    ]);
    exit;
}

if ($action === 'view_comments') {
    $task_id = intval($_GET['id'] ?? 0);
    // Redirect to main task list and open inline comments for the task
    header('Location: task_management.php#comments-section-' . $task_id);
    exit;
}

// Add comment via AJAX
if ($action === 'add_comment' && $user_id) {
    header('Content-Type: application/json');

    $task_id = intval($_POST['task_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($task_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit;
    }

    if (empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
        exit;
    }

    // Check if comments table exists
    $tmpComments = $conn->query("SHOW TABLES LIKE 'task_comments'");
    $comments_table_exists = ($tmpComments instanceof mysqli_result && $tmpComments->num_rows > 0);
    if (!$comments_table_exists) {
        echo json_encode(['success' => false, 'message' => 'Comments feature not available']);
        exit;
    }

    // Verify user has access to this task
    $access_check = false;
    if ($user_role === 'admin' || $user_role === 'super_admin') {
        $access_check = true;
    } else {
        // Check if user is assigned to this task or created it
        $check = safe_prepare($conn, "SELECT 1 FROM tasks t LEFT JOIN task_assignments ta ON ta.task_id = t.id WHERE t.id = ? AND (t.created_by = ? OR ta.user_id = ?) LIMIT 1");
        if ($check) {
            $check->bind_param('iii', $task_id, $user_id, $user_id);
            $check->execute();
            $checkRes = $check->get_result();
            $access_check = ($checkRes instanceof mysqli_result && $checkRes->num_rows > 0);
            $check->close();
        } else {
            $access_check = false;
        }
    }

    if (!$access_check) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    // Insert the comment
    $stmt = safe_prepare($conn, "INSERT INTO task_comments (task_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param('iis', $task_id, $user_id, $comment);

        if ($stmt->execute()) {
            $comment_id = $stmt->insert_id ?? 0;

            // handle comment attachments if present
            if (!empty($_FILES['attachments']) && isset($_FILES['attachments']['name']) && is_array($_FILES['attachments']['name'])) {
                $uploadDir = __DIR__ . '/../uploads/task_comment_attachments/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                // Ensure comment attachments table exists, create if missing
                $createSql = "CREATE TABLE IF NOT EXISTS task_comment_attachments (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    comment_id INT NOT NULL,
                    stored_name VARCHAR(512) NOT NULL,
                    original_name VARCHAR(512) NOT NULL,
                    mime_type VARCHAR(255) DEFAULT NULL,
                    size INT DEFAULT 0,
                    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX (comment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                $conn->query($createSql);

                // Prepare insert statement for attachments
                $insAttach = safe_prepare($conn, "INSERT INTO task_comment_attachments (comment_id, stored_name, original_name, mime_type, size, uploaded_at) VALUES (?,?,?,?,?,NOW())", true);

                $max_per_file = 10 * 1024 * 1024; // 10MB
                for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
                    $orig = $_FILES['attachments']['name'][$i];
                    $tmpn = $_FILES['attachments']['tmp_name'][$i] ?? '';
                    $err = $_FILES['attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    $size = $_FILES['attachments']['size'][$i] ?? 0;
                    $mime = $_FILES['attachments']['type'][$i] ?? null;
                    if ($err !== UPLOAD_ERR_OK) continue;
                    if ($size <= 0 || $size > $max_per_file) continue;

                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    $stored = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
                    $dest = $uploadDir . $stored;
                    if (move_uploaded_file($tmpn, $dest)) {
                        if ($insAttach) {
                            $insAttach->bind_param('isssi', $comment_id, $stored, $orig, $mime, $size);
                            $insAttach->execute();
                        }
                    }
                }
            }

            // Get user name and formatted time for response
            $user_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'Unknown User';
            $formatted_time = date('M j, Y g:i A');
            $comment_html = nl2br(htmlspecialchars($comment));

            // Add notification to other participants (if notifications table exists)
            $tmpNotifTable = $conn->query("SHOW TABLES LIKE 'task_notifications'");
            $notifications_table_exists = ($tmpNotifTable instanceof mysqli_result && $tmpNotifTable->num_rows > 0);
            if ($notifications_table_exists) {
                $participants = $conn->query("SELECT DISTINCT user_id FROM task_assignments WHERE task_id = " . $task_id . " AND user_id != " . $user_id);
                if ($participants instanceof mysqli_result) {
                    while ($p = $participants->fetch_assoc()) {
                        $notif_stmt = safe_prepare($conn, "INSERT INTO task_notifications (user_id, task_id, message, type, created_at) VALUES (?, ?, ?, 'comment', NOW())", false);
                        $message = $user_name . " commented on task: " . substr($comment, 0, 100) . (strlen($comment) > 100 ? '...' : '');
                        if ($notif_stmt) {
                            $notif_stmt->bind_param('iis', $p['user_id'], $task_id, $message);
                            $notif_stmt->execute();
                            $notif_stmt->close();
                        }
                    }
                }
            }

            // Fetch any recorded attachments for this comment (if table exists)
            $commentAttachments = [];
            $tmpCA = $conn->query("SHOW TABLES LIKE 'task_comment_attachments'");
            $hasCA = ($tmpCA instanceof mysqli_result && $tmpCA->num_rows > 0);
            if ($hasCA && $comment_id) {
                $q = $conn->query("SELECT * FROM task_comment_attachments WHERE comment_id = " . intval($comment_id) . " ORDER BY uploaded_at ASC");
                if ($q instanceof mysqli_result) {
                    while ($r = $q->fetch_assoc()) $commentAttachments[] = $r;
                }
            }

            echo json_encode([
                'success' => true,
                'user_name' => htmlspecialchars($user_name),
                'formatted_time' => $formatted_time,
                'comment_html' => $comment_html,
                'comment_attachments' => $commentAttachments
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
        }
        exit;
    }

    // Time tracking handlers removed
}

if ($action === 'get_notifications' && $user_id) {
    // Check if notifications table exists
    $tmpNotifTbl = $conn->query("SHOW TABLES LIKE 'task_notifications'");
    $notif_table_exists = ($tmpNotifTbl instanceof mysqli_result && $tmpNotifTbl->num_rows > 0);
    if (!$notif_table_exists) {
        echo json_encode(['unread_count' => 0]);
        exit;
    }

    $unread = safe_prepare($conn, "SELECT COUNT(*) as count FROM task_notifications WHERE user_id = ? AND is_read = 0", false);
    if ($unread) {
        $unread->bind_param('i', $user_id);
        $unread->execute();
        $countRes = $unread->get_result();
        $countRow = ($countRes instanceof mysqli_result) ? $countRes->fetch_assoc() : ['count' => 0];
        $count = $countRow['count'] ?? 0;
        $unread->close();
    } else {
        $count = 0;
    }

    echo json_encode(['unread_count' => intval($count)]);
    exit;
} // Admin review actions: approve/reject completion or appeals
if (($action === 'admin_review' || $action === 'appeal_review') && ($user_role === 'admin' || $user_role === 'super_admin')) {
    $task_id = intval($_POST['id'] ?? 0);
    $decision = $_POST['decision'] ?? 'reject';
    $notes = $_POST['notes'] ?? '';

    if ($action === 'admin_review') {
        if ($decision === 'approve') {
            $conn->query("UPDATE tasks SET status='completed' WHERE id=" . $task_id);
            add_history($conn, $task_id, 'completion_approved', $user_id, $notes);
        } else {
            $conn->query("UPDATE tasks SET status='in_progress' WHERE id=" . $task_id);
            add_history($conn, $task_id, 'completion_rejected', $user_id, $notes);
        }
    } else {
        // appeal review
        $appeal_id = intval($_POST['appeal_id'] ?? 0);
        if ($decision === 'approve') {
            $conn->query("UPDATE task_appeals SET status='approved', reviewed_by=" . $user_id . ", reviewed_at=NOW(), review_notes='" . $conn->real_escape_string($notes) . "' WHERE id=" . $appeal_id);
            add_history($conn, $task_id, 'appeal_approved', $user_id, $notes);
        } else {
            $conn->query("UPDATE task_appeals SET status='denied', reviewed_by=" . $user_id . ", reviewed_at=NOW(), review_notes='" . $conn->real_escape_string($notes) . "' WHERE id=" . $appeal_id);
            add_history($conn, $task_id, 'appeal_denied', $user_id, $notes);
        }
    }

    header('Location: /task_management/task_management.php');
    exit;
}

// Super admin: archive a task
if ($action === 'archive' && $user_role === 'super_admin') {
    $task_id = intval($_POST['id'] ?? $_REQUEST['id'] ?? 0);
    if ($task_id <= 0) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid task id for archive.'];
        header('Location: /task_management/task_management.php');
        exit;
    }

    // store previous status and set archived
    $get = safe_prepare($conn, "SELECT status FROM tasks WHERE id = ? LIMIT 1", false);
    if ($get) {
        $get->bind_param('i', $task_id);
        $get->execute();
        $res = $get->get_result();
        $prev = null;
        if ($res instanceof mysqli_result) {
            $row = $res->fetch_assoc();
            if ($row) $prev = $row['status'];
        }
        $get->close();
    } else {
        $prev = null;
    }

    $stmt = safe_prepare($conn, "UPDATE tasks SET previous_status = ?, status='archived' WHERE id = ?", true);
    $stmt->bind_param('si', $prev, $task_id);
    if ($stmt->execute()) {
        add_history($conn, $task_id, 'archived', $user_id, 'Task archived by super_admin');
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Task archived successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to archive task.'];
    }
    header('Location: /task_management/task_management.php');
    exit;
}

// Super admin: unarchive a task
if ($action === 'unarchive' && $user_role === 'super_admin') {
    $task_id = intval($_POST['id'] ?? $_REQUEST['id'] ?? 0);
    if ($task_id <= 0) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid task id for unarchive.'];
        header('Location: /task_management/task_management.php');
        exit;
    }

    // restore previous_status if available
    $get = safe_prepare($conn, "SELECT previous_status FROM tasks WHERE id = ? LIMIT 1", false);
    if ($get) {
        $get->bind_param('i', $task_id);
        $get->execute();
        $res = $get->get_result();
        $prev = 'in_progress';
        if ($res instanceof mysqli_result) {
            $row = $res->fetch_assoc();
            if ($row && !empty($row['previous_status'])) $prev = $row['previous_status'];
        }
        $get->close();
    } else {
        $prev = 'in_progress';
    }

    $stmt = safe_prepare($conn, "UPDATE tasks SET status = ?, previous_status = NULL WHERE id = ?", true);
    $stmt->bind_param('si', $prev, $task_id);
    if ($stmt->execute()) {
        add_history($conn, $task_id, 'unarchived', $user_id, 'Task unarchived by super_admin');
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Task unarchived successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to unarchive task.'];
    }
    header('Location: /task_management/task_management.php');
    exit;
}

// Default fallback
header('Location: /task_management/task_management.php');
exit;
