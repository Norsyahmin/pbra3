<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../mypbra_connect.php';

$user_id = $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? null;

$table_exists = false; // Initialize $table_exists to prevent undefined variable warnings

$allowed_roles = ['admin', 'super_admin'];
if ($user_id === null || !in_array($user_role, $allowed_roles, true)) {
    header('Location: /login/login.php');
    exit;
}

// Task Statistics
$stats = [];

// Basic counts (guard query results)
$tmp = $conn->query("SELECT COUNT(*) as count FROM tasks");
$stats['total'] = ($tmp instanceof mysqli_result) ? (int)($tmp->fetch_assoc()['count'] ?? 0) : 0;
$tmp = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'pending'");
$stats['pending'] = ($tmp instanceof mysqli_result) ? (int)($tmp->fetch_assoc()['count'] ?? 0) : 0;
$tmp = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'in_progress'");
$stats['in_progress'] = ($tmp instanceof mysqli_result) ? (int)($tmp->fetch_assoc()['count'] ?? 0) : 0;
$tmp = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'completed'");
$stats['completed'] = ($tmp instanceof mysqli_result) ? (int)($tmp->fetch_assoc()['count'] ?? 0) : 0;
$tmp = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE end_date < CURDATE() AND status NOT IN ('completed', 'archived')");
$stats['overdue'] = ($tmp instanceof mysqli_result) ? (int)($tmp->fetch_assoc()['count'] ?? 0) : 0;

$user_performance = $conn->query("\n        SELECT u.full_name, \n               COUNT(ta.task_id) as assigned_tasks,\n               COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_tasks,\n               AVG(CASE \n                   WHEN t.status = 'completed' THEN \n                       TIMESTAMPDIFF(DAY, t.created_at, \n                           COALESCE(\n                               (SELECT th.created_at FROM task_history th \n                                WHERE th.task_id = t.id \n                                AND th.action IN ('completion_approved', 'completed') \n                                ORDER BY th.created_at DESC LIMIT 1),\n                               t.created_at\n                           )\n                       )\n                   END) as avg_completion_days,\n               0 as total_time_minutes\n        FROM users u\n        LEFT JOIN task_assignments ta ON ta.user_id = u.id\n        LEFT JOIN tasks t ON t.id = ta.task_id\n        WHERE u.user_type IN ('regular', 'admin')\n        GROUP BY u.id, u.full_name\n        HAVING assigned_tasks > 0\n        ORDER BY completed_tasks DESC, assigned_tasks DESC\n    ");

// Task distribution by priority
$priority_dist = $conn->query("
    SELECT priority, COUNT(*) as count 
    FROM tasks 
    WHERE status NOT IN ('archived') 
    GROUP BY priority
");

// Recent activity
$recent_activity = $conn->query("
    SELECT th.*, t.title as task_title, u.full_name as actor_name
    FROM task_history th
    JOIN tasks t ON t.id = th.task_id
    LEFT JOIN users u ON u.id = th.actor_id
    ORDER BY th.created_at DESC
    LIMIT 20
");

// Monthly completion trend (last 6 months)
$monthly_trend = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
           COUNT(*) as created,
           COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
    FROM tasks
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Task Analytics Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../page_title.css">
    <link rel="stylesheet" href="/task_management/task_management.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #255498;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        .overdue {
            color: #e74c3c;
        }

        .completed {
            color: #27ae60;
        }

        .chart-container {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .performance-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .performance-table th,
        .performance-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .performance-table th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .activity-list {
            max-height: 400px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
        }

        .activity-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 0.9em;
        }

        .activity-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../navbar/navbar.php'; ?>

    <div id="content" class="content">
        <div class="wrap">
            <div style="margin-bottom: 20px;">
                <button onclick="location.href='task_management.php'" type="button" style="background-color: #255498; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; text-decoration: none;">← Back to Tasks</button>
            </div>

            <div class="page-title">
                <h1>Task Analytics Dashboard</h1>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['in_progress']; ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number completed"><?php echo $stats['completed']; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number overdue"><?php echo $stats['overdue']; ?></div>
                    <div class="stat-label">Overdue</div>
                </div>
            </div>

            <!-- Charts -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="chart-container">
                    <h3>Task Distribution by Priority</h3>
                    <canvas id="priorityChart" width="400" height="300"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Monthly Completion Trend</h3>
                    <canvas id="trendChart" width="400" height="300"></canvas>
                </div>
            </div>

            <!-- User Performance Table -->
            <div style="margin: 30px 0;">
                <h3>👥 User Performance</h3>
                <table class="performance-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Assigned Tasks</th>
                            <th>Completed Tasks</th>
                            <th>Completion Rate</th>
                            <th>Avg. Completion Time</th>
                            <?php if ($table_exists): ?>
                                <th>Total Time Logged</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($user_performance instanceof mysqli_result && $user_performance->num_rows > 0): ?>
                            <?php while ($perf = $user_performance->fetch_assoc()): ?>
                                <?php
                                $completion_rate = $perf['assigned_tasks'] > 0 ? round(($perf['completed_tasks'] / $perf['assigned_tasks']) * 100, 1) : 0;
                                $avg_days = $perf['avg_completion_days'] ? round($perf['avg_completion_days'], 1) : 'N/A';
                                $total_hours = $perf['total_time_minutes'] ? round($perf['total_time_minutes'] / 60, 1) : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($perf['full_name']); ?></td>
                                    <td><?php echo $perf['assigned_tasks']; ?></td>
                                    <td><?php echo $perf['completed_tasks']; ?></td>
                                    <td><?php echo $completion_rate; ?>%</td>
                                    <td><?php echo $avg_days; ?> days</td>
                                    <?php if ($table_exists): ?>
                                        <td><?php echo $total_hours; ?>h</td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $table_exists ? '6' : '5'; ?>">No performance data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Activity -->
            <div style="margin: 30px 0;">
                <h3>📝 Recent Activity</h3>
                <div class="activity-list">
                    <?php if ($recent_activity instanceof mysqli_result && $recent_activity->num_rows > 0): ?>
                        <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                            <div class="activity-item">
                                <strong><?php echo htmlspecialchars($activity['actor_name'] ?? 'System'); ?></strong>
                                <?php echo htmlspecialchars($activity['action']); ?>
                                task "<strong><?php echo htmlspecialchars($activity['task_title']); ?></strong>"
                                <span style="color: #666; float: right;">
                                    <?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?>
                                </span>
                                <?php if ($activity['notes']): ?>
                                    <div style="color: #666; font-style: italic; margin-top: 3px;">
                                        <?php echo htmlspecialchars($activity['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../scrolltop/scrolltop.php'; ?>
    <?php include __DIR__ . '/../footer/footer.php'; ?>

    <script src="../scrolltop/scrolltop.js"></script>
    <script>
        // Priority Distribution Chart
        const priorityCtx = document.getElementById('priorityChart').getContext('2d');
        const priorityData = {
            <?php
            $priority_labels = [];
            $priority_counts = [];
            if ($priority_dist instanceof mysqli_result && $priority_dist->num_rows > 0) {
                while ($p = $priority_dist->fetch_assoc()) {
                    $priority_labels[] = "'" . $p['priority'] . "'";
                    $priority_counts[] = $p['count'];
                }
            }
            ?>
            labels: [<?php echo implode(',', $priority_labels); ?>],
            datasets: [{
                data: [<?php echo implode(',', $priority_counts); ?>],
                backgroundColor: ['#e74c3c', '#f39c12', '#27ae60'],
                borderWidth: 1
            }]
        };

        new Chart(priorityCtx, {
            type: 'doughnut',
            data: priorityData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendData = {
            <?php
            $trend_labels = [];
            $trend_created = [];
            $trend_completed = [];
            if ($monthly_trend instanceof mysqli_result && $monthly_trend->num_rows > 0) {
                while ($t = $monthly_trend->fetch_assoc()) {
                    $trend_labels[] = "'" . $t['month'] . "'";
                    $trend_created[] = $t['created'];
                    $trend_completed[] = $t['completed'];
                }
            }
            ?>
            labels: [<?php echo implode(',', $trend_labels); ?>],
            datasets: [{
                label: 'Created',
                data: [<?php echo implode(',', $trend_created); ?>],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.1
            }, {
                label: 'Completed',
                data: [<?php echo implode(',', $trend_completed); ?>],
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                tension: 0.1
            }]
        };

        new Chart(trendCtx, {
            type: 'line',
            data: trendData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
