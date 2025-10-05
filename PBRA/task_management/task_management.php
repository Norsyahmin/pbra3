<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../mypbra_connect.php';

// Assumptions:
// - User authentication uses $_SESSION['user_id'], $_SESSION['role'] (values: 'regular','admin','super_admin') and $_SESSION['full_name']
// - There is a `users` table with id, full_name, email, user_type, office, etc.

$user_id = $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? null;

// Only allow authenticated users with one of these roles to access this page.
$allowed_roles = ['regular', 'admin', 'super_admin'];
if ($user_id === null || !in_array($user_role, $allowed_roles, true)) {
    // Not authenticated or not authorized — redirect to login page
    // You can change this path to your app's login/landing page as appropriate.
    header('Location: /login/login.php');
    exit;
}

$user_name = $_SESSION['full_name'] ?? $_SESSION['email'] ?? $_SESSION['name'] ?? '';

// Fetch users for assignment dropdown (admin)
$users = [];
// select users with the actual column name `full_name` from your DB
$res = $conn->query("SELECT id, full_name FROM users ORDER BY full_name");
if ($res instanceof mysqli_result) {
    while ($r = $res->fetch_assoc()) $users[] = $r;
}

// Basic filters from GET
$filter_status = $_GET['status'] ?? '';
$filter_priority = $_GET['priority'] ?? '';
$filter_search = $_GET['q'] ?? '';
$filter_task_type = $_GET['task_type'] ?? '';
$filter_show_archived = isset($_GET['show_archived']) ? ($_GET['show_archived'] ? true : false) : false;

// Build main query: Admin sees created tasks (or all), regular sees assigned tasks
if ($user_role === 'admin' || $user_role === 'super_admin') {
    $sql = "SELECT t.* FROM tasks t";
} else {
    // Join assignments
    $sql = "SELECT t.* FROM tasks t JOIN task_assignments a ON a.task_id = t.id WHERE a.user_id = " . intval($user_id);
}

$where = [];
if ($filter_status) {
    // Special-case an "overdue" pseudo-status: tasks with end_date before today and not completed
    if ($filter_status === 'overdue') {
        // include tasks due today as overdue
        $where[] = "t.end_date <= CURDATE() AND t.status <> 'completed'";
    } else {
        $where[] = "t.status = '" . $conn->real_escape_string($filter_status) . "'";
    }
}
if ($filter_priority) $where[] = "t.priority = '" . $conn->real_escape_string($filter_priority) . "'";
if ($filter_search) $where[] = "(t.title LIKE '%" . $conn->real_escape_string($filter_search) . "%' OR t.description LIKE '%" . $conn->real_escape_string($filter_search) . "%')";
if ($filter_task_type) {
    // If the UI requested the special "other" filter, match any task that has a Task Type
    // but is not one of the predefined types. We keep a list of predefined values used
    // in the select above and exclude them.
    if (strtolower($filter_task_type) === 'other') {
        $predefined_types = [
            'Lesson Planning','Teaching a Class','Substituting a Teacher','Student Attendance Checking','Student Counseling',
            'Exam Question Preparation','Exam Supervision','Paper Marking','Grade Submission',
            'Faculty Meeting','Department Meeting','Administrative Paperwork','Performance Review',
            'Student Mentoring','School Event Management','Club or Society Management','Social Event Coordination','Parent-Teacher Meeting',
            'Research Paper Review','Syllabus Development','Course Material Preparation',
            'Budget Planning','Resource Management','Staff Training','Quality Assurance',
            'Curriculum Development','Technology Integration','Community Outreach','Assessment Design'
        ];

        $notClauses = [];
        foreach ($predefined_types as $pt) {
            $notClauses[] = "t.description NOT LIKE 'Task Type: " . $conn->real_escape_string($pt) . "%'";
        }

        // Tasks that have any Task Type but do not match any predefined type
        $where[] = "t.description LIKE 'Task Type: %' AND (" . implode(' AND ', $notClauses) . ")";
    } else {
        $where[] = "t.description LIKE 'Task Type: " . $conn->real_escape_string($filter_task_type) . "%'";
    }
}

if (count($where) > 0) {
    if (stripos($sql, 'WHERE') !== false) {
        $sql .= ' AND ' . implode(' AND ', $where);
    } else {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
}

// By default hide archived tasks unless the user explicitly requests them or filters by status=archived
if (!$filter_show_archived && $filter_status !== 'archived') {
    // If no explicit archived filter, ensure archived tasks are excluded
    // Only add this if not already restricting status
    $sql .= (stripos($sql, 'WHERE') !== false) ? " AND t.status <> 'archived'" : " WHERE t.status <> 'archived'";
}

$sql .= ' ORDER BY t.created_at DESC';
$tasks = [];
$res = $conn->query($sql);
if ($res instanceof mysqli_result) {
    while ($r = $res->fetch_assoc()) $tasks[] = $r;
}

// Check if comments table exists (once for all tasks)
$tmp = $conn->query("SHOW TABLES LIKE 'task_comments'");
$comments_table_exists = ($tmp instanceof mysqli_result && $tmp->num_rows > 0);

// Small helper to fetch assigned users for a task
function get_assignees($conn, $task_id)
{
    $out = [];
    $r = $conn->query("SELECT u.id,u.full_name FROM task_assignments a JOIN users u ON u.id=a.user_id WHERE a.task_id=" . intval($task_id));
    if ($r instanceof mysqli_result) while ($row = $r->fetch_assoc()) $out[] = $row;
    return $out;
}

?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Task Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../page_title.css">
    <link rel="stylesheet" href="/task_management/task_management.css">
</head>

<body>

    <?php
    // Include shared navbar (topbar + sidebar)
    include __DIR__ . '/../navbar/navbar.php';
    ?>

    <!-- Page Title -->
    <div class="page-title">
        <h1 style="font-size: 30px;">TASK MANAGEMENT</h1>
    </div>

    <!-- Main content area expected by navbar.js / navbar.css -->
    <div id="content" class="content">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-card">
                <div class="welcome-header">
                    <h2>Welcome, <?php echo htmlspecialchars($user_name ?? ''); ?></h2>
                    <span class="user-badge <?php echo strtolower($user_role); ?>"><?php echo htmlspecialchars($user_role ?? ''); ?></span>
                </div>

                <?php if (isset($_SESSION['flash'])): $f = $_SESSION['flash'];
                    unset($_SESSION['flash']); ?>
                    <div class="alert <?php echo ($f['type'] === 'success') ? 'alert-success' : 'alert-error'; ?>">
                        <div class="alert-content">
                            <?php echo htmlspecialchars($f['message']); ?>
                            <?php if (!empty($f['assigned']) && is_array($f['assigned'])): ?>
                                <div class="assigned-list">
                                    <strong>Assigned to:</strong>
                                    <ul>
                                        <?php foreach ($f['assigned'] as $an): ?>
                                            <li><?php echo htmlspecialchars($an); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>


            <div class="dashboard-grid">
                <!-- Tasks Section -->
                <div class="main-content-area">
                    <div class="section-card">
                        <div class="section-header">
                            <h3>Your Tasks</h3>
                            <div class="task-stats">
                                <?php
                                $total_tasks = count($tasks);
                                $completed_tasks = count(array_filter($tasks, function ($t) {
                                    return $t['status'] === 'completed';
                                }));
                                $pending_tasks = count(array_filter($tasks, function ($t) {
                                    return $t['status'] === 'pending';
                                }));
                                // Top-level overdue count for the tasks currently loaded (user view respects assignments)
                                $overdue_tasks_top = count(array_filter($tasks, function ($t) {
                                    if (empty($t['end_date'])) return false;
                                    // include tasks due today as overdue
                                    return (strtotime($t['end_date']) <= strtotime(date('Y-m-d')) && ($t['status'] ?? '') !== 'completed');
                                }));
                                ?>
                                <span class="stat-item">Total: <?php echo $total_tasks; ?></span>
                                <span class="stat-item completed">Completed: <?php echo $completed_tasks; ?></span>
                                <span class="stat-item overdue">Overdue: <?php echo $overdue_tasks_top; ?></span>
                                <span class="stat-item pending">Pending: <?php echo $pending_tasks; ?></span>
                            </div>
                        </div>

                        <!-- Enhanced Filter Section -->
                        <div class="filter-section">
                            <form method="get" class="filter-form">
                                <div class="filter-row">
                                    <div class="filter-group">
                                        <label>Search</label>
                                        <input type="text" name="q" placeholder="Search tasks..." value="<?php echo htmlspecialchars($filter_search ?? ''); ?>">
                                    </div>
                                    <div class="filter-group">
                                        <label>Status</label>
                                        <select name="status">
                                            <option value="">All statuses</option>
                                            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo $filter_status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="archived" <?php echo $filter_status === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>Priority</label>
                                        <select name="priority">
                                            <option value="">All priorities</option>
                                            <option value="high" <?php echo $filter_priority === 'high' ? 'selected' : ''; ?>>High</option>
                                            <option value="medium" <?php echo $filter_priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                            <option value="low" <?php echo $filter_priority === 'low' ? 'selected' : ''; ?>>Low</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>Task Type</label>
                                        <select name="task_type">
                                            <option value="">All types</option>
                                            <optgroup label="Class-Related">
                                                <option value="Lesson Planning" <?php echo ($_GET['task_type'] ?? '') === 'Lesson Planning' ? 'selected' : ''; ?>>Lesson Planning</option>
                                                <option value="Teaching a Class" <?php echo ($_GET['task_type'] ?? '') === 'Teaching a Class' ? 'selected' : ''; ?>>Teaching a Class</option>
                                                <option value="Substituting a Teacher" <?php echo ($_GET['task_type'] ?? '') === 'Substituting a Teacher' ? 'selected' : ''; ?>>Substituting a Teacher</option>
                                                <option value="Student Attendance Checking" <?php echo ($_GET['task_type'] ?? '') === 'Student Attendance Checking' ? 'selected' : ''; ?>>Student Attendance Checking</option>
                                                <option value="Student Counseling" <?php echo ($_GET['task_type'] ?? '') === 'Student Counseling' ? 'selected' : ''; ?>>Student Counseling</option>
                                            </optgroup>
                                            <optgroup label="Exam-Related Tasks">
                                                <option value="Exam Question Preparation" <?php echo ($_GET['task_type'] ?? '') === 'Exam Question Preparation' ? 'selected' : ''; ?>>Exam Question Preparation</option>
                                                <option value="Exam Supervision" <?php echo ($_GET['task_type'] ?? '') === 'Exam Supervision' ? 'selected' : ''; ?>>Exam Supervision</option>
                                                <option value="Paper Marking" <?php echo ($_GET['task_type'] ?? '') === 'Paper Marking' ? 'selected' : ''; ?>>Paper Marking</option>
                                                <option value="Grade Submission" <?php echo ($_GET['task_type'] ?? '') === 'Grade Submission' ? 'selected' : ''; ?>>Grade Submission</option>
                                            </optgroup>
                                            <optgroup label="Meeting & Administration">
                                                <option value="Faculty Meeting" <?php echo ($_GET['task_type'] ?? '') === 'Faculty Meeting' ? 'selected' : ''; ?>>Faculty Meeting</option>
                                                <option value="Department Meeting" <?php echo ($_GET['task_type'] ?? '') === 'Department Meeting' ? 'selected' : ''; ?>>Department Meeting</option>
                                                <option value="Administrative Paperwork" <?php echo ($_GET['task_type'] ?? '') === 'Administrative Paperwork' ? 'selected' : ''; ?>>Administrative Paperwork</option>
                                                <option value="Performance Review" <?php echo ($_GET['task_type'] ?? '') === 'Performance Review' ? 'selected' : ''; ?>>Performance Review</option>
                                            </optgroup>
                                            <optgroup label="Student Activities & Events">
                                                <option value="Student Mentoring" <?php echo ($_GET['task_type'] ?? '') === 'Student Mentoring' ? 'selected' : ''; ?>>Student Mentoring</option>
                                                <option value="School Event Management" <?php echo ($_GET['task_type'] ?? '') === 'School Event Management' ? 'selected' : ''; ?>>School Event Management</option>
                                                <option value="Club or Society Management" <?php echo ($_GET['task_type'] ?? '') === 'Club or Society Management' ? 'selected' : ''; ?>>Club or Society Management</option>
                                                <option value="Social Event Coordination" <?php echo ($_GET['task_type'] ?? '') === 'Social Event Coordination' ? 'selected' : ''; ?>>Social Event Coordination</option>
                                                <option value="Parent-Teacher Meeting" <?php echo ($_GET['task_type'] ?? '') === 'Parent-Teacher Meeting' ? 'selected' : ''; ?>>Parent-Teacher Meeting</option>
                                            </optgroup>
                                            <optgroup label="Research & Development">
                                                <option value="Research Paper Review" <?php echo ($_GET['task_type'] ?? '') === 'Research Paper Review' ? 'selected' : ''; ?>>Research Paper Review</option>
                                                <option value="Syllabus Development" <?php echo ($_GET['task_type'] ?? '') === 'Syllabus Development' ? 'selected' : ''; ?>>Syllabus Development</option>
                                                <option value="Course Material Preparation" <?php echo ($_GET['task_type'] ?? '') === 'Course Material Preparation' ? 'selected' : ''; ?>>Course Material Preparation</option>
                                            </optgroup>
                                            <optgroup label="Other">
                                                <option value="other" <?php echo ($_GET['task_type'] ?? '') === 'other' ? 'selected' : ''; ?>>Others</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                                <div class="filter-actions">
                                    <label>
                                        <input type="checkbox" name="show_archived" value="1" <?php echo $filter_show_archived ? 'checked' : ''; ?>>
                                        Show archived
                                    </label>
                                    <button class="btn confirm-btn" type="submit">Apply Filters</button>
                                </div>
                            </form>
                        </div> <!-- Task List -->
                        <div class="task-list">
                            <?php if (count($tasks) === 0): ?>
                                <div class="empty-state">
                                    <div class="empty-icon"></div>
                                    <h4>No tasks found</h4>
                                    <p>No tasks match your current filters.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($tasks as $t): ?>
                                    <div class="task-card" data-task-id="<?php echo $t['id']; ?>">
                                        <div class="task-header">
                                            <div class="task-info">
                                                <h4 class="task-title"><?php echo htmlspecialchars($t['title'] ?? ''); ?></h4>
                                                <span class="task-id">#<?php echo $t['id']; ?></span>
                                            </div>
                                            <div class="task-meta">
                                                <span class="priority priority-<?php echo $t['priority']; ?>"><?php echo ucfirst($t['priority'] ?? ''); ?></span>
                                                <span class="status status-<?php echo $t['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $t['status'] ?? '')); ?></span>
                                            </div>
                                        </div>

                                        <div class="task-details">
                                            <?php
                                            // Extract task type from description if present
                                            $task_type_display = '';
                                            if (preg_match('/^Task Type: (.+?)\n/', $t['description'] ?? '', $matches)) {
                                                $task_type_display = $matches[1];
                                            }
                                            ?>
                                            <?php if ($task_type_display): ?>
                                                <div class="detail-row">
                                                    <span class="detail-label">Type:</span>
                                                    <span class="detail-value task-type-badge"><?php echo htmlspecialchars($task_type_display); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="detail-row">
                                                <span class="detail-label">📅 Due:</span>
                                                <span class="detail-value <?php echo (strtotime($t['end_date']) < time() && $t['status'] !== 'completed') ? 'overdue' : ''; ?>">
                                                    <?php echo htmlspecialchars($t['end_date'] ?? '—'); ?>
                                                </span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">👥 Assigned to:</span>
                                                <span class="detail-value">
                                                    <?php
                                                    $ass = get_assignees($conn, $t['id']);
                                                    $names = array_map(function ($u) {
                                                        return $u['full_name'];
                                                    }, $ass);
                                                    echo htmlspecialchars(implode(', ', $names));
                                                    ?>
                                                </span>
                                            </div>

                                            <?php
                                            // Show recent comments count (if table exists)
                                            if ($comments_table_exists) {
                                                $cq = $conn->query("SELECT COUNT(*) as count FROM task_comments WHERE task_id=" . intval($t['id']));
                                                $comment_count = 0;
                                                if ($cq instanceof mysqli_result) {
                                                    $assoc = $cq->fetch_assoc();
                                                    $comment_count = $assoc['count'] ?? 0;
                                                }
                                                if ($comment_count > 0) {
                                                    echo '<div class="detail-row"><span class="detail-label">Comments:</span><span class="detail-value">' . $comment_count . '</span></div>';
                                                }
                                            }

                                            // Time tracking display removed
                                            ?>
                                        </div>

                                        <div class="task-actions">
                                            <div class="action-group">
                                                <button class="btn confirm-btn" onclick="openTaskModal(<?php echo intval($t['id']); ?>)">View</button>
                                                <?php if ($t['status'] !== 'completed' && $t['status'] !== 'archived'): ?>
                                                    <form style="display:inline" method="post" action="process_task.php">
                                                        <input type="hidden" name="action" value="mark_complete">
                                                        <input type="hidden" name="id" value="<?php echo intval($t['id']); ?>">
                                                        <button class="btn success-btn" type="submit">Complete</button>
                                                    </form>
                                                <?php endif; ?>
                                                <button class="btn comment-btn" onclick="toggleComments(<?php echo intval($t['id']); ?>)">Comments</button>
                                                <button class="btn info-btn" onclick="toggleAppeal(<?php echo intval($t['id']); ?>)">Appeal</button>
                                                <?php // Time tracking button removed 
                                                ?>
                                            </div>

                                            <!-- Comments Section -->
                                            <div id="comments-section-<?php echo intval($t['id']); ?>" class="comments-section" style="display: none;">
                                                <div class="comments-container">
                                                    <h4>Comments</h4>
                                                    <div id="comments-list-<?php echo intval($t['id']); ?>" class="comments-list">
                                                        <?php
                                                        // Load existing comments
                                                        if ($comments_table_exists) {
                                                            $comment_query = $conn->query("SELECT tc.*, u.full_name as user_name FROM task_comments tc JOIN users u ON u.id = tc.user_id WHERE tc.task_id = " . intval($t['id']) . " ORDER BY tc.created_at ASC");
                                                            if ($comment_query instanceof mysqli_result && $comment_query->num_rows > 0) {
                                                                while ($comment = $comment_query->fetch_assoc()) {
                                                                    echo '<div class="comment-item">';
                                                                    echo '<div class="comment-meta">';
                                                                    echo '<strong>' . htmlspecialchars($comment['user_name']) . '</strong>';
                                                                    echo '<span class="comment-time">' . date('M j, Y g:i A', strtotime($comment['created_at'])) . '</span>';
                                                                    echo '</div>';
                                                                    echo '<div class="comment-text">' . nl2br(htmlspecialchars($comment['comment'])) . '</div>';
                                                                    echo '</div>';
                                                                }
                                                            } else {
                                                                echo '<div class="no-comments">No comments yet.</div>';
                                                            }
                                                        } else {
                                                            echo '<div class="no-comments">Comments system not available.</div>';
                                                        }
                                                        ?>
                                                    </div>

                                                    <?php if ($comments_table_exists): ?>
                                                        <div class="add-comment-form">
                                                            <textarea id="comment-input-<?php echo intval($t['id']); ?>"
                                                                class="comment-input"
                                                                placeholder="Add a comment... (Ctrl+Enter to submit)"
                                                                rows="3"></textarea>
                                                            <input id="comment-files-<?php echo intval($t['id']); ?>" class="comment-file-input" type="file" multiple style="margin-top:8px;" />
                                                            <div class="comment-actions">
                                                                <button class="btn primary-btn" onclick="addComment(<?php echo intval($t['id']); ?>, this)">Add Comment</button>
                                                                <button class="btn secondary-btn" onclick="toggleComments(<?php echo intval($t['id']); ?>)">Cancel</button>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="appeal-section">
                                                <div id="appeal-section-<?php echo intval($t['id']); ?>" class="comments-section appeal-comments" style="display: none;">
                                                    <div class="comments-container">
                                                        <h4>Appeal</h4>
                                                        <div class="add-comment-form">
                                                            <form method="post" action="process_task.php" class="appeal-form" onsubmit="return submitAppeal(this);" enctype="multipart/form-data">
                                                                <input type="hidden" name="action" value="appeal">
                                                                <input type="hidden" name="id" value="<?php echo intval($t['id']); ?>">
                                                                <textarea name="reason" class="comment-input appeal-input" placeholder="Appeal / notes" required rows="3"></textarea>
                                                                <input type="file" name="attachments[]" multiple style="margin-top:8px;" />
                                                                <div class="comment-actions">
                                                                    <button class="btn primary-btn" type="submit">Appeal</button>
                                                                    <button class="btn secondary-btn" type="button" onclick="this.closest('.appeal-section').querySelector('.appeal-form textarea').value='';">Cancel</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if ($user_role === 'super_admin'): ?>
                                                <div class="admin-actions">
                                                    <?php if (($t['status'] ?? '') === 'archived'): ?>
                                                        <form style="display:inline" method="post" action="process_task.php" onsubmit="return confirm('Unarchive this task?');">
                                                            <input type="hidden" name="action" value="unarchive">
                                                            <input type="hidden" name="id" value="<?php echo intval($t['id']); ?>">
                                                            <button class="btn confirm-btn" type="submit">Unarchive</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form style="display:inline" method="post" action="process_task.php" onsubmit="return confirm('Archive this task?');">
                                                            <input type="hidden" name="action" value="archive">
                                                            <input type="hidden" name="id" value="<?php echo intval($t['id']); ?>">
                                                            <button class="btn cancel-btn" type="submit">Archive</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Section -->
                <div class="sidebar-section">
                    <?php if ($user_role === 'admin' || $user_role === 'super_admin'): ?>
                        <div class="section-card">
                            <div class="section-header">
                                <h3>Admin Tools</h3>
                            </div>
                            <div class="admin-tools">
                                <a class="tool-btn primary" href="/task_management/create_task.php">
                                    <span class="tool-icon"></span>
                                    <span class="tool-text">Create New Task</span>
                                </a>
                                <a class="tool-btn accent" href="/task_management/task_analytics.php">
                                    <span class="tool-icon"></span>
                                    <span class="tool-text">Analytics Dashboard</span>
                                </a>
                            </div>
                        </div>

                        <!-- Quick Create Task Section removed -->
                    <?php else: ?>
                        <div class="section-card">
                            <div class="section-header">
                                <h3>📅 Upcoming Tasks</h3>
                            </div>
                            <div class="upcoming-tasks">
                                <?php
                                $now = date('Y-m-d');
                                $q = $conn->query("SELECT t.* FROM tasks t JOIN task_assignments a ON a.task_id=t.id WHERE a.user_id=" . intval($user_id) . " AND t.end_date >= '" . $conn->real_escape_string($now) . "' ORDER BY t.end_date ASC LIMIT 6");
                                if ($q instanceof mysqli_result && $q->num_rows > 0) {
                                    while ($row = $q->fetch_assoc()) {
                                        $title = $row['title'] ?? '';
                                        $due = $row['end_date'] ?? '';
                                        $priority = $row['priority'] ?? 'medium';
                                        echo '<div class="upcoming-task">';
                                        echo '<div class="upcoming-title">' . htmlspecialchars($title) . '</div>';
                                        echo '<div class="upcoming-meta">';
                                        echo '<span class="upcoming-due">📅 ' . htmlspecialchars($due) . '</span>';
                                        echo '<span class="priority priority-' . $priority . '">' . ucfirst($priority) . '</span>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="empty-upcoming">No upcoming tasks.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Task History Section -->
            <div class="section-card">
                <div class="section-header">
                    <h3>Recent Activity</h3>
                </div>
                <div class="history-list">
                    <?php
                    $hres = $conn->query("SELECT th.*, u.full_name as actor_name FROM task_history th LEFT JOIN users u ON u.id = th.actor_id WHERE th.task_id IN (SELECT id FROM tasks) ORDER BY th.created_at DESC LIMIT 20");
                    if ($hres instanceof mysqli_result && $hres->num_rows > 0) {
                        while ($h = $hres->fetch_assoc()) {
                            $created_at = $h['created_at'] ?? '';
                            $action = $h['action'] ?? '';
                            $actor_name = $h['actor_name'] ?? '';
                            $notes = $h['notes'] ?? '';
                            echo '<div class="history-item">';
                            echo '<div class="history-header">';
                            echo '<span class="history-action">' . htmlspecialchars($action) . '</span>';
                            echo '<span class="history-time">' . date('M j, g:i A', strtotime($created_at)) . '</span>';
                            echo '</div>';
                            echo '<div class="history-details">';
                            echo '<span class="history-actor">by ' . htmlspecialchars($actor_name) . '</span>';
                            echo '<span class="history-task">Task #' . intval($h['task_id']) . '</span>';
                            if ($notes) {
                                echo '<div class="history-notes">' . htmlspecialchars($notes) . '</div>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="empty-history">No activity yet.</div>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </div> <!-- /#content -->

    <?php
    // Add scroll-to-top component and footer
    include __DIR__ . '/../scrolltop/scrolltop.php';
    include __DIR__ . '/../footer/footer.php';
    ?>

    <!-- Ensure scrolltop logic is loaded -->
    <script src="../scrolltop/scrolltop.js"></script>

    <!-- Enhanced Task Management Features -->
    <script>
        // Time tracking features removed

        // Auto-refresh notifications every 30 seconds
        setInterval(() => {
            fetch('process_task.php?action=get_notifications')
                .then(response => response.json())
                .then(data => {
                    if (data.unread_count > 0) {
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => console.error('Notification check error:', error));
        }, 30000);

        function updateNotificationBadge(count) {
            let badge = document.getElementById('notification-badge');
            if (!badge) {
                badge = document.createElement('span');
                badge.id = 'notification-badge';
                badge.style.cssText = `
                    background: #e74c3c;
                    color: white;
                    border-radius: 50%;
                    padding: 2px 6px;
                    font-size: 12px;
                    margin-left: 5px;
                `;
                const title = document.querySelector('h1');
                if (title) title.appendChild(badge);
            }
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }

        // Quick-create form removed; no quick-create helpers required.

        // Comment system functions
        function toggleComments(taskId) {
            const commentsSection = document.getElementById(`comments-section-${taskId}`);
            if (commentsSection) {
                if (commentsSection.style.display === 'none' || commentsSection.style.display === '') {
                    commentsSection.style.display = 'block';
                    // Focus on the comment input
                    const commentInput = document.getElementById(`comment-input-${taskId}`);
                    if (commentInput) {
                        setTimeout(() => {
                            commentInput.focus();
                            // Add Enter key handler
                            commentInput.onkeydown = function(e) {
                                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                                    e.preventDefault();
                                    addComment(taskId);
                                }
                            };
                        }, 100);
                    }
                } else {
                    commentsSection.style.display = 'none';
                }
            }
        }

        // Toggle appeal textarea visibility (mirrors toggleComments behavior)
        function toggleAppeal(taskId) {
            const appealSection = document.getElementById(`appeal-section-${taskId}`);
            if (appealSection) {
                if (appealSection.style.display === 'none' || appealSection.style.display === '') {
                    appealSection.style.display = 'block';
                    // Focus on the appeal textarea
                    const textarea = appealSection.querySelector('textarea');
                    if (textarea) {
                        setTimeout(() => {
                            textarea.focus();
                            textarea.onkeydown = function(e) {
                                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                                    // submit the form
                                    const form = textarea.closest('form');
                                    if (form) form.submit();
                                }
                            };
                        }, 100);
                    }
                } else {
                    appealSection.style.display = 'none';
                }
            }
        }

        function addComment(taskId, btn) {
            const commentInput = document.getElementById(`comment-input-${taskId}`);
            if (!commentInput) {
                alert('Comment input not found');
                return;
            }

            const comment = commentInput.value.trim();
            if (!comment) {
                alert('Please enter a comment');
                return;
            }

            // Determine the button element (may be called via keyboard without passing the button)
            let addButton = null;
            if (btn && typeof btn === 'object') addButton = btn;
            else {
                // try to find the button inside the same comments section
                const container = document.getElementById(`comments-section-${taskId}`);
                if (container) addButton = container.querySelector('.add-comment-form .primary-btn');
            }

            const originalText = addButton ? addButton.textContent : null;
            if (addButton) {
                addButton.textContent = 'Adding...';
                addButton.disabled = true;
            }

            // Build FormData to include optional files
            const formData = new FormData();
            formData.append('action', 'add_comment');
            formData.append('task_id', taskId);
            formData.append('comment', comment);
            const fileInput = document.getElementById(`comment-files-${taskId}`);
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append('attachments[]', fileInput.files[i]);
                }
            }

            fetch('process_task.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear the input
                        commentInput.value = '';
                                // clear file input if present
                                const fileInputClear = document.getElementById(`comment-files-${taskId}`);
                                if (fileInputClear) fileInputClear.value = '';

                        // Add the new comment to the list
                        const commentsList = document.getElementById(`comments-list-${taskId}`);
                        if (commentsList) {
                            const noCommentsDiv = commentsList.querySelector('.no-comments');
                            if (noCommentsDiv) noCommentsDiv.remove();

                            const newCommentDiv = document.createElement('div');
                            newCommentDiv.className = 'comment-item';
                            newCommentDiv.innerHTML = `
                                <div class="comment-meta">
                                    <strong>${data.user_name}</strong>
                                    <span class="comment-time">${data.formatted_time}</span>
                                </div>
                                <div class="comment-text">${data.comment_html}</div>
                            `;

                            // If server returned attachments, append them
                            if (data.comment_attachments && Array.isArray(data.comment_attachments) && data.comment_attachments.length > 0) {
                                const attachList = document.createElement('div');
                                attachList.className = 'comment-attachments';
                                data.comment_attachments.forEach(a => {
                                    const aEl = document.createElement('div');
                                    aEl.innerHTML = `<a href="/uploads/task_comment_attachments/${encodeURIComponent(a.stored_name)}" target="_blank">📎 ${a.original_name}</a>`;
                                    attachList.appendChild(aEl);
                                });
                                newCommentDiv.appendChild(attachList);
                            }

                            commentsList.appendChild(newCommentDiv);
                            newCommentDiv.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest'
                            });
                        }
                    } else {
                        alert('Failed to add comment: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error adding comment');
                })
                .finally(() => {
                    if (addButton) {
                        addButton.textContent = originalText;
                        addButton.disabled = false;
                    }
                });
        }
    </script>

    <script>
        // Prevent double-submit for appeals and provide feedback
        function submitAppeal(form) {
            try {
                const btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.dataset.orig = btn.textContent;
                    btn.textContent = 'Submitting...';
                    btn.disabled = true;
                }
                // allow form to submit normally
                return true;
            } catch (e) {
                console.error('submitAppeal error', e);
                return true;
            }
        }
    </script>

    <script>
        // Make Quick Stats cards clickable: filter tasks by status
        (function() {
            function setQueryParam(key, value) {
                const url = new URL(window.location.href);
                if (value === null || value === undefined || value === '') {
                    url.searchParams.delete(key);
                } else {
                    url.searchParams.set(key, value);
                }
                return url.toString();
            }

            document.querySelectorAll('.quick-stats .stat-item').forEach(el => {
                el.addEventListener('click', () => {
                    const status = el.getAttribute('data-status');
                    // map friendly statuses if needed
                    const url = new URL(window.location.href);
                    // preserve other params but set status
                    if (status) {
                        url.searchParams.set('status', status);
                    } else {
                        url.searchParams.delete('status');
                    }
                    // Navigate to the updated URL
                    window.location.href = url.toString();
                });
            });
        })();

        // Auto-open comments when URL contains fragment like #comments-section-<id>
        (function() {
            function openFromHash() {
                try {
                    const hash = window.location.hash || '';
                    if (hash.startsWith('#comments-section-')) {
                        const id = hash.replace('#comments-section-', '');
                        const el = document.getElementById('comments-section-' + id);
                        if (el) {
                            // ensure it's visible
                            el.style.display = 'block';
                            const input = document.getElementById('comment-input-' + id);
                            if (input) input.focus();
                            // scroll into view
                            setTimeout(() => el.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            }), 150);
                        }
                    }
                } catch (e) {
                    console.error('Error opening comments from hash', e);
                }
            }

            // Run on load
            window.addEventListener('load', openFromHash);
            // Also handle hashchange
            window.addEventListener('hashchange', openFromHash);
        })();
    </script>

    <!-- Task Details Modal -->
    <div id="taskModal" class="task-modal" style="display: none;">
        <div class="modal-overlay" onclick="closeTaskModal()"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="modalTaskTitle">Task Details</h2>
                <button class="modal-close" onclick="closeTaskModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalContent">
                <div class="modal-loading">Loading...</div>
            </div>
        </div>
    </div>

    <script>
        // Task Modal Functions
        function openTaskModal(taskId) {
            const modal = document.getElementById('taskModal');
            const modalContent = document.getElementById('modalContent');
            const modalTitle = document.getElementById('modalTaskTitle');

            // Show modal with loading state
            modal.style.display = 'block';
            modalContent.innerHTML = '<div class="modal-loading">Loading task details...</div>';
            modalTitle.textContent = 'Loading...';

            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';

            // Fetch task details
            fetch(`process_task.php?action=get_task_details&id=${taskId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderTaskDetails(data);
                    } else {
                        modalContent.innerHTML = `<div class="modal-error">Error: ${data.message || 'Failed to load task details'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContent.innerHTML = '<div class="modal-error">Error loading task details. Please try again.</div>';
                });
        }

        function closeTaskModal() {
            const modal = document.getElementById('taskModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function renderTaskDetails(data) {
            const task = data.task;
            const assignees = data.assignees;
            const attachments = data.attachments;
            const history = data.history;
            const appeals = data.appeals;
            const userRole = data.user_role;

            // Update modal title
            document.getElementById('modalTaskTitle').textContent = task.title || 'Task Details';

            // Extract task type from description if present
            let taskTypeDisplay = '';
            let description = task.description || '';
            const typeMatch = description.match(/^Task Type: (.+?)\n/);
            if (typeMatch) {
                taskTypeDisplay = typeMatch[1];
                description = description.replace(/^Task Type: .+?\n\n?/, '');
            }

            // Build HTML content
            let html = `
                <div class="modal-section">
                    <div class="modal-info-grid">
                        <div class="modal-info-item">
                            <span class="modal-label">Status:</span>
                            <span class="status status-${task.status}">${task.status ? task.status.replace(/_/g, ' ').toUpperCase() : 'N/A'}</span>
                        </div>
                        <div class="modal-info-item">
                            <span class="modal-label">Priority:</span>
                            <span class="priority priority-${task.priority}">${task.priority ? task.priority.toUpperCase() : 'N/A'}</span>
                        </div>
                        ${taskTypeDisplay ? `
                        <div class="modal-info-item">
                            <span class="modal-label">Type:</span>
                            <span class="task-type-badge">${escapeHtml(taskTypeDisplay)}</span>
                        </div>` : ''}
                        <div class="modal-info-item">
                            <span class="modal-label">Start Date:</span>
                            <span>${escapeHtml(task.start_date || '—')}</span>
                        </div>
                        <div class="modal-info-item">
                            <span class="modal-label">End Date:</span>
                            <span>${escapeHtml(task.end_date || '—')}</span>
                        </div>
                        <div class="modal-info-item">
                            <span class="modal-label">Created By:</span>
                            <span>${escapeHtml(task.creator_name || 'Unknown')}</span>
                        </div>
                    </div>
                </div>

                <div class="modal-section">
                    <h3>Description</h3>
                    <div class="modal-description">${description ? escapeHtml(description).replace(/\n/g, '<br>') : 'No description provided.'}</div>
                </div>

                <div class="modal-section">
                    <h3>Assignees</h3>
                    <div class="modal-assignees">
                        ${assignees.length > 0 ? assignees.map(a => `<span class="assignee-badge">${escapeHtml(a.full_name)}</span>`).join('') : '<span class="text-muted">No assignees</span>'}
                    </div>
                </div>

                ${attachments.length > 0 ? `
                <div class="modal-section">
                    <h3>Attachments</h3>
                    <ul class="modal-attachments">
                        ${attachments.map(att => `
                            <li>
                                <a href="/task_management/download_attachment.php?id=${att.id}" target="_blank">
                                    📎 ${escapeHtml(att.original_name)} (${formatFileSize(att.size)})
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                </div>` : ''}

                <div class="modal-section">
                    <h3>History</h3>
                    <div class="modal-history">
                        ${history.length > 0 ? history.map(h => `
                            <div class="history-item-modal">
                                <div class="history-meta">
                                    <span class="history-action-badge">${escapeHtml(h.action)}</span>
                                    <span class="history-time">${formatDate(h.created_at)}</span>
                                </div>
                                <div class="history-details-modal">
                                    <span>by ${escapeHtml(h.actor_name || 'Unknown')}</span>
                                    ${h.notes ? `<div class="history-notes-modal">${escapeHtml(h.notes)}</div>` : ''}
                                </div>
                            </div>
                        `).join('') : '<div class="text-muted">No history available</div>'}
                    </div>
                </div>

                ${(userRole === 'admin' || userRole === 'super_admin') ? `
                <div class="modal-section">
                    <h3>Admin Actions</h3>
                    <form method="post" action="process_task.php" class="modal-admin-form">
                        <input type="hidden" name="action" value="admin_review">
                        <input type="hidden" name="id" value="${task.id}">
                        <div class="form-group">
                            <label>Decision:</label>
                            <select name="decision" class="form-control">
                                <option value="approve">Approve Completion</option>
                                <option value="reject">Reject</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes:</label>
                            <textarea name="notes" rows="3" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn confirm-btn">Submit Decision</button>
                    </form>

                    ${appeals.length > 0 ? `
                    <h4 style="margin-top: 20px;">Pending Appeals</h4>
                    ${appeals.map(appeal => `
                        <div class="appeal-item-modal">
                            <div class="appeal-header">
                                <strong>Appeal #${appeal.id} by ${escapeHtml(appeal.full_name)}</strong>
                            </div>
                            <div class="appeal-reason">${escapeHtml(appeal.reason)}</div>
                            <form method="post" action="process_task.php" class="appeal-review-form">
                                <input type="hidden" name="action" value="appeal_review">
                                <input type="hidden" name="id" value="${task.id}">
                                <input type="hidden" name="appeal_id" value="${appeal.id}">
                                <div class="form-group">
                                    <select name="decision" class="form-control">
                                        <option value="approve">Approve</option>
                                        <option value="reject">Deny</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <textarea name="notes" placeholder="Review notes..." class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn confirm-btn">Submit</button>
                            </form>
                        </div>
                    `).join('')}` : '<p style="margin-top: 10px;">No pending appeals.</p>'}
                </div>` : ''}
            `;

            document.getElementById('modalContent').innerHTML = html;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        }

        // Close modal when clicking escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTaskModal();
            }
        });
    </script>

</body>

</html>