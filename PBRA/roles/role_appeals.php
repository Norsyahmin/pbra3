<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

$page_name = $page_name ?? 'Role Appeals';
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$user_id = $_SESSION['id'];
$user_type = $_SESSION['user_type'] ?? 'regular';

// Handle appeal submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_appeal'])) {
    $role_id = intval($_POST['role_id']);
    $appeal_type = $_POST['appeal_type'];
    $reason = trim($_POST['reason']);
    $attachment = '';

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/role_appeals/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $filename = 'appeal_' . $user_id . '_' . $role_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
            $attachment = 'uploads/role_appeals/' . $filename;
        }
    }

    // Insert appeal
    $stmt = $conn->prepare("INSERT INTO role_appeals (user_id, role_id, appeal_type, reason, attachment, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("iisss", $user_id, $role_id, $appeal_type, $reason, $attachment);

    if ($stmt->execute()) {
        $success_message = "Your appeal has been submitted successfully and is pending review.";
    } else {
        $error_message = "Failed to submit appeal. Please try again.";
    }
}

// Handle appeal review (admin/super_admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_appeal']) && $user_type !== 'regular') {
    $appeal_id = intval($_POST['appeal_id']);
    $decision = $_POST['decision'];
    $review_notes = trim($_POST['review_notes']);

    $stmt = $conn->prepare("UPDATE role_appeals SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_notes = ? WHERE id = ?");
    $stmt->bind_param("sisi", $decision, $user_id, $review_notes, $appeal_id);

    if ($stmt->execute()) {
        // If approved, handle the role change
        if ($decision === 'approved') {
            $appeal_stmt = $conn->prepare("SELECT user_id, role_id, appeal_type FROM role_appeals WHERE id = ?");
            if ($appeal_stmt) {
                $appeal_stmt->bind_param("i", $appeal_id);
                $appeal_stmt->execute();
                $appeal_res = $appeal_stmt->get_result();
                $appeal_data = $appeal_res !== null ? $appeal_res->fetch_assoc() : null;
                $appeal_stmt->close();
            } else {
                $appeal_data = null;
            }

            if ($appeal_data['appeal_type'] === 'removal') {
                // Remove role
                $remove_stmt = $conn->prepare("DELETE FROM userroles WHERE user_id = ? AND role_id = ?");
                $remove_stmt->bind_param("ii", $appeal_data['user_id'], $appeal_data['role_id']);
                $remove_stmt->execute();

                // Update role history
                $history_stmt = $conn->prepare("UPDATE role_history SET removed_at = NOW() WHERE user_id = ? AND role_id = ? AND removed_at IS NULL");
                $history_stmt->bind_param("ii", $appeal_data['user_id'], $appeal_data['role_id']);
                $history_stmt->execute();
            }
        }

        $success_message = "Appeal has been reviewed successfully.";
    } else {
        $error_message = "Failed to review appeal. Please try again.";
    }
}

// Fetch user's roles for appeal
$user_roles = [];
if ($user_type === 'regular') {
    $roles_stmt = $conn->prepare("\n        SELECT r.id, r.name, d.name as department_name \n        FROM userroles ur \n        JOIN roles r ON ur.role_id = r.id \n        LEFT JOIN departments d ON r.department_id = d.id \n        WHERE ur.user_id = ?\n    ");
    if ($roles_stmt) {
        $roles_stmt->bind_param("i", $user_id);
        $roles_stmt->execute();
        $roles_res = $roles_stmt->get_result();
        $user_roles = $roles_res !== null ? $roles_res->fetch_all(MYSQLI_ASSOC) : [];
        $roles_stmt->close();
    } else {
        $user_roles = [];
    }
}

// Fetch appeals based on user type
// Fetch appeals based on user type
if ($user_type === 'regular') {
    // Show user's own appeals
    $appeals_stmt = $conn->prepare("
        SELECT ra.*, r.name as role_name, d.name as department_name,
               reviewer.full_name as reviewer_name
        FROM role_appeals ra
        JOIN roles r ON ra.role_id = r.id
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN users reviewer ON ra.reviewed_by = reviewer.id
        WHERE ra.user_id = ?
        ORDER BY ra.created_at DESC
    ");
    $appeals_stmt->bind_param("i", $user_id);
} else {
    // Show all appeals for admin/super_admin
    $appeals_stmt = $conn->prepare("
        SELECT ra.*, r.name as role_name, d.name as department_name,
               u.full_name as user_name, u.email as user_email,
               reviewer.full_name as reviewer_name
        FROM role_appeals ra
        JOIN roles r ON ra.role_id = r.id
        LEFT JOIN departments d ON r.department_id = d.id
        JOIN users u ON ra.user_id = u.id
        LEFT JOIN users reviewer ON ra.reviewed_by = reviewer.id
        ORDER BY ra.created_at DESC
    ");
}

$appeals_stmt->execute();
$appeals_res = $appeals_stmt->get_result();
$appeals = $appeals_res !== null ? $appeals_res->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Appeals</title>
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
        .form-group textarea,
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
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

        .btn-success {
            background: #28a745;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger:hover {
            background: #c82333;
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

        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .appeal-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .appeal-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }

        .appeal-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-denied {
            background: #f8d7da;
            color: #721c24;
        }

        .review-form {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
        }
    </style>
</head>

<body onload="fetchNotifications()">
    <header>
        <?php include '../navbar/navbar.php'; ?>
    </header>

    <div class="page-title">
        <h1 style="font-size: 30px;">ROLE APPEALS</h1>
        <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
            Add to Favorite
        </button>
    </div>

    <div class="breadcrumb">
        <ul>
            <li><a href="../dashboard_template/dashboard.php">Dashboard</a></li>
            <li><a href="../roles/roles.php">Roles</a></li>
            <li>Role Appeals</li>
        </ul>
    </div>

    <div class="content">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($user_type === 'regular'): ?>
            <!-- Submit Appeal Section -->
            <div class="section">
                <h2><i class="fas fa-file-alt"></i> Submit Role Appeal</h2>
                <p>You can request to be removed from a role or change your role assignment by submitting an appeal with proper justification.</p>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="role_id">Select Role to Appeal:</label>
                        <select name="role_id" id="role_id" required>
                            <option value="">Select a role...</option>
                            <?php foreach ($user_roles as $role): ?>
                                <option value="<?= $role['id'] ?>">
                                    <?= htmlspecialchars($role['name']) ?>
                                    (<?= htmlspecialchars($role['department_name'] ?? 'No Department') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="appeal_type">Appeal Type:</label>
                        <select name="appeal_type" id="appeal_type" required>
                            <option value="">Select appeal type...</option>
                            <option value="removal">Request Role Removal</option>
                            <option value="change">Request Role Change</option>
                            <option value="objection">Object to Role Assignment</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason for Appeal:</label>
                        <textarea name="reason" id="reason" placeholder="Provide detailed explanation for your appeal..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Attach Supporting Document (Optional):</label>
                        <input type="file" name="attachment" id="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small>Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max: 5MB)</small>
                    </div>

                    <button type="submit" name="submit_appeal" class="btn">
                        <i class="fas fa-paper-plane"></i> Submit Appeal
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Appeals List -->
        <div class="section">
            <h2><i class="fas fa-list"></i>
                <?= $user_type === 'regular' ? 'My Appeals' : 'All Role Appeals' ?>
            </h2>

            <?php if (empty($appeals)): ?>
                <p>No appeals found.</p>
            <?php else: ?>
                <?php foreach ($appeals as $appeal): ?>
                    <div class="appeal-card">
                        <div class="appeal-header">
                            <div>
                                <h4><?= htmlspecialchars($appeal['role_name']) ?></h4>
                                <?php if ($user_type !== 'regular'): ?>
                                    <p><strong>User:</strong> <?= htmlspecialchars($appeal['user_name']) ?> (<?= htmlspecialchars($appeal['user_email']) ?>)</p>
                                <?php endif; ?>
                                <p><strong>Department:</strong> <?= htmlspecialchars($appeal['department_name'] ?? 'No Department') ?></p>
                                <p><strong>Appeal Type:</strong> <?= htmlspecialchars(ucfirst($appeal['appeal_type'])) ?></p>
                                <p><strong>Submitted:</strong> <?= date('d M Y H:i', strtotime($appeal['created_at'])) ?></p>
                            </div>
                            <span class="appeal-status status-<?= $appeal['status'] ?>">
                                <?= htmlspecialchars(ucfirst($appeal['status'])) ?>
                            </span>
                        </div>

                        <div>
                            <strong>Reason:</strong>
                            <p><?= nl2br(htmlspecialchars($appeal['reason'])) ?></p>
                        </div>

                        <?php if ($appeal['attachment']): ?>
                            <div>
                                <strong>Attachment:</strong>
                                <a href="../<?= htmlspecialchars($appeal['attachment']) ?>" target="_blank">
                                    <i class="fas fa-paperclip"></i> View Document
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($appeal['status'] !== 'pending' && $appeal['review_notes']): ?>
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                                <strong>Review Notes:</strong>
                                <p><?= nl2br(htmlspecialchars($appeal['review_notes'])) ?></p>
                                <?php if ($appeal['reviewer_name']): ?>
                                    <small>Reviewed by: <?= htmlspecialchars($appeal['reviewer_name']) ?>
                                        on <?= date('d M Y H:i', strtotime($appeal['reviewed_at'])) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($user_type !== 'regular' && $appeal['status'] === 'pending'): ?>
                            <div class="review-form">
                                <h5>Review Appeal</h5>
                                <form method="POST">
                                    <input type="hidden" name="appeal_id" value="<?= $appeal['id'] ?>">

                                    <div class="form-group">
                                        <label>Decision:</label>
                                        <select name="decision" required>
                                            <option value="">Select decision...</option>
                                            <option value="approved">Approve</option>
                                            <option value="denied">Deny</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Review Notes:</label>
                                        <textarea name="review_notes" placeholder="Provide feedback on your decision..." required></textarea>
                                    </div>

                                    <button type="submit" name="review_appeal" class="btn btn-success">
                                        <i class="fas fa-check"></i> Submit Review
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../footer/footer.php'; ?>

    <script>
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