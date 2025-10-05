<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

$page_name = $page_name ?? 'Role Data Export/Import';
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

// Check user permissions
$user_id = $_SESSION['id'];
$user_type = $_SESSION['user_type'] ?? 'regular';

if ($user_type === 'regular') {
    header("Location: ../roles/roles.php");
    exit();
}

// Handle export
if (isset($_POST['export_roles'])) {
    $export_type = $_POST['export_type'];

    switch ($export_type) {
        case 'current_assignments':
            $sql = "SELECT u.full_name, u.email, r.name as role_name, d.name as department_name, 
                          ur.appointed_at, ur.appointed_by
                   FROM userroles ur
                   JOIN users u ON ur.user_id = u.id
                   JOIN roles r ON ur.role_id = r.id
                   LEFT JOIN departments d ON r.department_id = d.id
                   ORDER BY d.name, r.name, u.full_name";
            $filename = 'current_role_assignments_' . date('Y-m-d_H-i-s') . '.csv';
            break;

        case 'role_history':
            $sql = "SELECT u.full_name, u.email, r.name as role_name, d.name as department_name,
                          rh.assigned_at, rh.removed_at
                   FROM role_history rh
                   JOIN users u ON rh.user_id = u.id
                   JOIN roles r ON rh.role_id = r.id
                   LEFT JOIN departments d ON r.department_id = d.id
                   ORDER BY rh.assigned_at DESC";
            $filename = 'role_history_' . date('Y-m-d_H-i-s') . '.csv';
            break;

        case 'role_appeals':
            $sql = "SELECT u.full_name, u.email, r.name as role_name, ra.appeal_type,
                          ra.reason, ra.status, ra.created_at, ra.reviewed_at
                   FROM role_appeals ra
                   JOIN users u ON ra.user_id = u.id
                   JOIN roles r ON ra.role_id = r.id
                   ORDER BY ra.created_at DESC";
            $filename = 'role_appeals_' . date('Y-m-d_H-i-s') . '.csv';
            break;
    }

    $result = $conn->query($sql);

    if ($result) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Write headers
        if ($result->num_rows > 0) {
            $first_row = $result->fetch_assoc();
            fputcsv($output, array_keys($first_row));
            fputcsv($output, $first_row);

            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit();
    }
}

// Handle import
$import_message = '';
if (isset($_POST['import_roles']) && isset($_FILES['import_file'])) {
    $upload_file = $_FILES['import_file'];

    if ($upload_file['error'] === UPLOAD_ERR_OK) {
        $file_path = $upload_file['tmp_name'];

        if (($handle = fopen($file_path, "r")) !== FALSE) {
            $headers = fgetcsv($handle);
            $imported_count = 0;
            $errors = [];

            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) >= 4) {
                    $email = trim($data[1]);
                    $role_name = trim($data[2]);
                    $department_name = trim($data[3]);

                    // Find user
                    $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    if ($user_stmt) {
                        $user_stmt->bind_param("s", $email);
                        $user_stmt->execute();
                        $user_result = $user_stmt->get_result();

                        if ($user_result !== null && $user_result->num_rows > 0) {
                            $user_id_import = $user_result->fetch_assoc()['id'];
                        } else {
                            $user_id_import = null;
                        }
                        $user_stmt->close();
                    } else {
                        $user_id_import = null;
                    }

                    if ($user_id_import !== null) {
                        // Find role
                        $role_id_import = null;
                        $role_stmt = $conn->prepare("SELECT r.id FROM roles r 
                                                   LEFT JOIN departments d ON r.department_id = d.id
                                                   WHERE r.name = ? AND (d.name = ? OR ? = '')");
                        if ($role_stmt) {
                            $role_stmt->bind_param("sss", $role_name, $department_name, $department_name);
                            $role_stmt->execute();
                            $role_result = $role_stmt->get_result();
                            if ($role_result !== null && $role_result->num_rows > 0) {
                                $role_id_import = $role_result->fetch_assoc()['id'];
                            }
                            $role_stmt->close();
                        }

                        if ($role_id_import !== null) {
                            // Check if assignment already exists
                            $check_stmt = $conn->prepare("SELECT id FROM userroles WHERE user_id = ? AND role_id = ?");
                            if ($check_stmt) {
                                $check_stmt->bind_param("ii", $user_id_import, $role_id_import);
                                $check_stmt->execute();
                                $check_res = $check_stmt->get_result();

                                if ($check_res === null || $check_res->num_rows === 0) {
                                    // Insert new role assignment
                                    $insert_stmt = $conn->prepare("INSERT INTO userroles (user_id, role_id, appointed_at, appointed_by) VALUES (?, ?, NOW(), ?)");
                                    if ($insert_stmt) {
                                        $insert_stmt->bind_param("iii", $user_id_import, $role_id_import, $user_id);
                                        if ($insert_stmt->execute()) {
                                            $imported_count++;
                                            // Add to role history
                                            $history_stmt = $conn->prepare("INSERT INTO role_history (user_id, role_id, assigned_at) VALUES (?, ?, NOW())");
                                            if ($history_stmt) {
                                                $history_stmt->bind_param("ii", $user_id_import, $role_id_import);
                                                $history_stmt->execute();
                                                $history_stmt->close();
                                            }
                                        }
                                        $insert_stmt->close();
                                    }
                                }
                                $check_stmt->close();
                            }
                        } else {
                            $errors[] = "Role not found: $role_name in $department_name";
                        }
                    } else {
                        $errors[] = "User not found: $email";
                    }
                }
            }

            fclose($handle);

            $import_message = "Successfully imported $imported_count role assignments.";
            if (!empty($errors)) {
                $import_message .= " Errors: " . implode(", ", array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $import_message .= " and " . (count($errors) - 5) . " more.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Data Export/Import</title>
    <link rel="stylesheet" href="../page_title.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .section {
            background: white;
            margin: 20px 0;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .section h2 {
            color: #174080;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group select,
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            background: #174080;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #0f2d5c;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-info {
            background: #cce7ff;
            border: 1px solid #80bdff;
            color: #004085;
        }

        .export-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .export-option {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            cursor: pointer;
            transition: all 0.3s;
        }

        .export-option:hover {
            border-color: #174080;
            transform: translateY(-2px);
        }

        .export-option input[type="radio"] {
            margin-right: 10px;
        }

        .export-option h4 {
            margin: 0 0 10px 0;
            color: #174080;
        }

        .export-option p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body onload="fetchNotifications()">
    <header>
        <?php include '../navbar/navbar.php'; ?>
    </header>

    <div class="page-title">
        <h1 style="font-size: 30px;">ROLE DATA EXPORT/IMPORT</h1>
        <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
            Add to Favorite
        </button>
    </div>

    <div class="breadcrumb">
        <ul>
            <li><a href="../dashboard_template/dashboard.php">Dashboard</a></li>
            <li><a href="../roles/roles.php">Roles</a></li>
            <li>Data Export/Import</li>
        </ul>
    </div>

    <div class="content">
        <?php if ($import_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($import_message) ?>
            </div>
        <?php endif; ?>

        <!-- Export Section -->
        <div class="section">
            <h2><i class="fas fa-download"></i> Export Role Data</h2>
            <p>Select the type of data you want to export and download as CSV file.</p>

            <form method="POST">
                <div class="export-options">
                    <div class="export-option" onclick="selectExportType('current_assignments')">
                        <input type="radio" name="export_type" value="current_assignments" id="current_assignments">
                        <h4>Current Role Assignments</h4>
                        <p>Export all currently active role assignments with user details and appointment information.</p>
                    </div>

                    <div class="export-option" onclick="selectExportType('role_history')">
                        <input type="radio" name="export_type" value="role_history" id="role_history">
                        <h4>Role History</h4>
                        <p>Export complete history of role assignments including past assignments and removal dates.</p>
                    </div>

                    <div class="export-option" onclick="selectExportType('role_appeals')">
                        <input type="radio" name="export_type" value="role_appeals" id="role_appeals">
                        <h4>Role Appeals</h4>
                        <p>Export all role appeals with status, reasons, and review information.</p>
                    </div>
                </div>

                <button type="submit" name="export_roles" class="btn">
                    <i class="fas fa-download"></i> Export Selected Data
                </button>
            </form>
        </div>

        <!-- Import Section -->
        <div class="section">
            <h2><i class="fas fa-upload"></i> Import Role Data</h2>
            <p>Upload a CSV file to import role assignments. The file should contain columns: Full Name, Email, Role Name, Department Name.</p>

            <div class="alert alert-info">
                <strong>CSV Format:</strong> The file should have headers: Full Name, Email, Role Name, Department Name<br>
                <strong>Note:</strong> Users and roles must already exist in the system. Duplicate assignments will be skipped.
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="import_file">Select CSV File:</label>
                    <input type="file" name="import_file" id="import_file" accept=".csv" required>
                </div>

                <button type="submit" name="import_roles" class="btn">
                    <i class="fas fa-upload"></i> Import Role Data
                </button>

                <a href="../roles/sample_import.csv" class="btn btn-secondary" style="margin-left: 10px; text-decoration: none;">
                    <i class="fas fa-download"></i> Download Sample CSV
                </a>
            </form>
        </div>
    </div>

    <?php include '../footer/footer.php'; ?>

    <script>
        function selectExportType(type) {
            document.getElementById(type).checked = true;
        }

        function toggleFavorite() {
            const button = document.getElementById('favoriteButton');
            let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');

            const currentPage = {
                name: '<?= $page_name ?>',
                url: '<?= $page_url ?>'
            };

            const index = favorites.findIndex(fav => fav.url === currentPage.url);

            if (index === -1) {
                favorites.push(currentPage);
                localStorage.setItem('favorites', JSON.stringify(favorites));
                button.classList.add('favorited');
                button.textContent = 'Favorited';
            } else {
                favorites.splice(index, 1);
                localStorage.setItem('favorites', JSON.stringify(favorites));
                button.classList.remove('favorited');
                button.textContent = 'Add to Favorite';
            }
        }

        // Check if current page is favorited on load
        document.addEventListener('DOMContentLoaded', function() {
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            const currentUrl = '<?= $page_url ?>';

            if (favorites.some(fav => fav.url === currentUrl)) {
                const button = document.getElementById('favoriteButton');
                button.classList.add('favorited');
                button.textContent = 'Favorited';
            }
        });
    </script>
</body>

</html>