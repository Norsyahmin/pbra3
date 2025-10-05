<?php
// Start session and output buffering BEFORE any HTML/output so included components can send headers safely
require_once __DIR__ . '/../includes/auth.php';
ob_start();

// Access control: allow only super_admin to access this page. Others go to homepage.
if (!isset($_SESSION['id']) || (($_SESSION['user_type'] ?? '') !== 'super_admin')) {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/../mypbra_connect.php';

// Helper: check if a table exists
function tableExists($conn, $table)
{
    $table = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    return $res && $res->num_rows > 0;
}

// Helper: check if a specific column exists in a table
function columnExists($conn, $table, $column)
{
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $res && $res->num_rows > 0;
}

// Helper: safe count query
function safeCount($conn, $sql)
{
    $res = $conn->query($sql);
    if ($res) {
        $r = $res->fetch_row();
        return (int)$r[0];
    }
    return 0;
}

// Prepare analytics data structures
$totals = [];
$usersByRole = ['labels' => [], 'data' => []];
$tasksByStatus = ['labels' => [], 'data' => []];
$monthlyUsers = ['labels' => [], 'data' => []];
$monthlyTasks = ['labels' => [], 'data' => []];
// Roles per department
$rolesPerDept = ['labels' => [], 'data' => []];

// Total users
if (tableExists($conn, 'users')) {
    $totals['users'] = safeCount($conn, "SELECT COUNT(*) FROM users");

    // Users by role/user_type
    $sql = "SELECT COALESCE(user_type, 'Unknown') AS label, COUNT(*) AS value FROM users GROUP BY user_type ORDER BY value DESC";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $usersByRole['labels'][] = $row['label'];
            $usersByRole['data'][] = (int)$row['value'];
        }
    }

    // Monthly new users (last 12 months) using start_date (fallback to created_at)
    if (columnExists($conn, 'users', 'start_date')) {
        $dateCol = 'start_date';
    } elseif (columnExists($conn, 'users', 'created_at')) {
        $dateCol = 'created_at';
    } else {
        $dateCol = null;
    }

    if ($dateCol) {
        $sql = "SELECT DATE_FORMAT($dateCol, '%Y-%m') AS month, COUNT(*) AS value FROM users WHERE $dateCol >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH) GROUP BY month ORDER BY month";
        if ($res = $conn->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $monthlyUsers['labels'][] = $row['month'];
                $monthlyUsers['data'][] = (int)$row['value'];
            }
        }
    }
} else {
    $totals['users'] = 0;
}

// Tasks metrics
if (tableExists($conn, 'tasks')) {
    $totals['tasks'] = safeCount($conn, "SELECT COUNT(*) FROM tasks");

    $sql = "SELECT COALESCE(status, 'Unknown') AS label, COUNT(*) AS value FROM tasks GROUP BY status ORDER BY value DESC";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $tasksByStatus['labels'][] = $row['label'];
            $tasksByStatus['data'][] = (int)$row['value'];
        }
    }

    // Monthly tasks by a date column (try due_date, fallback to task_date, created_at)
    if (columnExists($conn, 'tasks', 'due_date')) {
        $taskDateCol = 'due_date';
    } elseif (columnExists($conn, 'tasks', 'task_date')) {
        $taskDateCol = 'task_date';
    } elseif (columnExists($conn, 'tasks', 'created_at')) {
        $taskDateCol = 'created_at';
    } else {
        $taskDateCol = null;
    }

    if ($taskDateCol) {
        $sql = "SELECT DATE_FORMAT($taskDateCol, '%Y-%m') AS month, COUNT(*) AS value FROM tasks WHERE $taskDateCol IS NOT NULL AND $taskDateCol >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH) GROUP BY month ORDER BY month";
        if ($res = $conn->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $monthlyTasks['labels'][] = $row['month'];
                $monthlyTasks['data'][] = (int)$row['value'];
            }
        }
    }
} else {
    $totals['tasks'] = 0;
}

// Mails / messages
if (tableExists($conn, 'mails')) {
    $totals['mails'] = safeCount($conn, "SELECT COUNT(*) FROM mails");
} elseif (tableExists($conn, 'mail')) {
    $totals['mails'] = safeCount($conn, "SELECT COUNT(*) FROM mail");
} else {
    $totals['mails'] = 0;
}

// Feedback
if (tableExists($conn, 'feedback')) {
    $totals['feedback'] = safeCount($conn, "SELECT COUNT(*) FROM feedback");
} else {
    $totals['feedback'] = 0;
}

// Events (try common names)
if (tableExists($conn, 'events')) {
    $totals['events'] = safeCount($conn, "SELECT COUNT(*) FROM events");
} elseif (tableExists($conn, 'event')) {
    $totals['events'] = safeCount($conn, "SELECT COUNT(*) FROM event");
} else {
    $totals['events'] = 0;
}

// Roles per department (top 20) - uses roles and departments tables if present
if (tableExists($conn, 'roles') && tableExists($conn, 'departments')) {
    $sql = "SELECT d.name AS label, COUNT(r.id) AS value FROM roles r JOIN departments d ON r.department_id = d.id GROUP BY d.id ORDER BY value DESC LIMIT 20";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $rolesPerDept['labels'][] = $row['label'];
            $rolesPerDept['data'][] = (int)$row['value'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Statistics & Analytics</title>
    <link rel="stylesheet" href="../page_title.css" />
    <link rel="stylesheet" href="../statistics/statistics.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Page Title -->
    <div class="page-title">
        <h1 style="font-size: 30px;">Analytics & Statistics</h1>
    </div>

    <?php include '../navbar/navbar.php'; ?>

    <div id="content" class="content">
        <h2>Overview</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <strong>Total users</strong>
                <div style="font-size:24px"><?php echo $totals['users']; ?></div>
            </div>
            <div class="stat-box">
                <strong>Total tasks</strong>
                <div style="font-size:24px"><?php echo $totals['tasks']; ?></div>
            </div>
            <div class="stat-box">
                <strong>Total mails</strong>
                <div style="font-size:24px"><?php echo $totals['mails']; ?></div>
            </div>
            <div class="stat-box">
                <strong>Total feedback</strong>
                <div style="font-size:24px"><?php echo $totals['feedback']; ?></div>
            </div>
            <div class="stat-box">
                <strong>Total events</strong>
                <div style="font-size:24px"><?php echo $totals['events']; ?></div>
            </div>
        </div>

        <h2>Charts</h2>

        <!-- Roles per Department: full width row (scrollable) -->
        <div class="charts roles-row">
            <div class="chart-card full-width">
                <h3>Roles per Department</h3>
                <div id="rolesPerDeptWrapper" class="chart-scroll">
                    <canvas id="rolesPerDeptChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Other charts (kept in grid) -->
        <div class="charts">
            <div class="chart-card">
                <h3>Users by Role</h3>
                <div class="chart-scroll" id="usersByRoleWrapper"><canvas id="usersByRoleChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Tasks by Status</h3>
                <div class="chart-scroll" id="tasksByStatusWrapper"><canvas id="tasksByStatusChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Monthly New Users</h3>
                <div class="chart-scroll" id="monthlyUsersWrapper"><canvas id="monthlyUsersChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Monthly Tasks (by due date)</h3>
                <div class="chart-scroll" id="monthlyTasksWrapper"><canvas id="monthlyTasksChart"></canvas></div>
            </div>
        </div>

    </div>

    <?php include '../scrolltop/scrolltop.php'; ?>
    <?php include '../footer/footer.php'; ?>
    <script src="../scrolltop/scrolltop.js"></script>

    <script>
        // Parse PHP-generated JSON
        const usersByRole = <?php echo json_encode($usersByRole); ?>;
        const tasksByStatus = <?php echo json_encode($tasksByStatus); ?>;
        const monthlyUsers = <?php echo json_encode($monthlyUsers); ?>;
        const monthlyTasks = <?php echo json_encode($monthlyTasks); ?>;

        function makeBar(id, labels, data, label, extraOptions = {}) {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            // Ensure the canvas has correct backing store for high-DPI displays
            canvas.style.width = '100%';
            const ctx = canvas.getContext('2d');
            const devicePixelRatio = window.devicePixelRatio || 1;
            // let Chart.js handle pixel ratio via options, but ensure CSS sizing doesn't force scaling
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: 'rgba(54,162,235,0.6)'
                    }]
                },
                options: Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    devicePixelRatio: devicePixelRatio,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }, extraOptions)
            });
        }

        function makePie(id, labels, data, extraOptions = {}) {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            canvas.style.width = '100%';
            const devicePixelRatio = window.devicePixelRatio || 1;
            new Chart(canvas, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f']
                    }]
                },
                options: Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    devicePixelRatio: devicePixelRatio
                }, extraOptions)
            });
        }

        // Helper: set canvas CSS height based on labels count to avoid uncontrolled expansion
        function adjustCanvasHeight(id, labelsCount, options = {}) {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            const minHeight = options.minHeight || 200;
            const perLabel = options.perLabel || 30; // px per label for y-axis charts
            const maxHeight = options.maxHeight || 1200;
            const height = Math.min(maxHeight, Math.max(minHeight, labelsCount * perLabel));
            canvas.style.height = height + 'px';
        }

        // Render charts with pre-sized canvases (prefer wrapper heights)
        const uWrapper = document.getElementById('usersByRoleWrapper');
        if (uWrapper) {
            const c = document.getElementById('usersByRoleChart');
            if (c) c.style.height = Math.max(180, Math.min(600, uWrapper.clientHeight)) + 'px';
        } else {
            adjustCanvasHeight('usersByRoleChart', (usersByRole.labels || []).length, {
                minHeight: 180,
                perLabel: 36,
                maxHeight: 600
            });
        }
        makeBar('usersByRoleChart', usersByRole.labels, usersByRole.data, 'Users');

        const tWrapper = document.getElementById('tasksByStatusWrapper');
        if (tWrapper) {
            const c = document.getElementById('tasksByStatusChart');
            if (c) c.style.height = Math.max(180, Math.min(400, tWrapper.clientHeight)) + 'px';
        } else {
            adjustCanvasHeight('tasksByStatusChart', (tasksByStatus.labels || []).length, {
                minHeight: 180,
                perLabel: 60,
                maxHeight: 400
            });
        }
        makePie('tasksByStatusChart', tasksByStatus.labels, tasksByStatus.data, {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        });

        const muWrapper = document.getElementById('monthlyUsersWrapper');
        if (muWrapper) {
            const c = document.getElementById('monthlyUsersChart');
            if (c) c.style.height = Math.max(180, Math.min(400, muWrapper.clientHeight)) + 'px';
        } else {
            adjustCanvasHeight('monthlyUsersChart', (monthlyUsers.labels || []).length, {
                minHeight: 200,
                perLabel: 48,
                maxHeight: 400
            });
        }
        makeBar('monthlyUsersChart', monthlyUsers.labels, monthlyUsers.data, 'New users');

        const mtWrapper = document.getElementById('monthlyTasksWrapper');
        if (mtWrapper) {
            const c = document.getElementById('monthlyTasksChart');
            if (c) c.style.height = Math.max(200, Math.min(600, mtWrapper.clientHeight)) + 'px';
        } else {
            adjustCanvasHeight('monthlyTasksChart', (monthlyTasks.labels || []).length, {
                minHeight: 200,
                perLabel: 48,
                maxHeight: 600
            });
        }
        makeBar('monthlyTasksChart', monthlyTasks.labels, monthlyTasks.data, 'Tasks');

        // Roles per Department - horizontal bar if data exists
        const rolesPerDept = <?php echo json_encode($rolesPerDept); ?>;
        if (rolesPerDept && rolesPerDept.labels && rolesPerDept.labels.length) {
            // If wrapper exists, size canvas to wrapper height so chart is scrollable inside
            const wrapper = document.getElementById('rolesPerDeptWrapper');
            const canvas = document.getElementById('rolesPerDeptChart');
            if (wrapper && canvas) {
                const h = Math.min(1400, Math.max(360, wrapper.clientHeight));
                canvas.style.height = h + 'px';
            } else {
                adjustCanvasHeight('rolesPerDeptChart', rolesPerDept.labels.length, {
                    minHeight: 360,
                    perLabel: 34,
                    maxHeight: 1400
                });
            }
            makeBar('rolesPerDeptChart', rolesPerDept.labels, rolesPerDept.data, 'Roles', {
                indexAxis: 'y'
            });
        }
    </script>

    <?php
    // Flush output buffer at end so headers from included files are sent properly
    ob_end_flush();
    ?>
</body>

</html>