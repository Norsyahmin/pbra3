<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

$page_name = $page_name ?? 'Appoint Role Department';
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

// Get user type and appointment type
$user_type = $_SESSION['user_type'] ?? 'regular';
$appointment_type = $_GET['type'] ?? 'admin'; // admin or super_admin

// Check permissions
if ($user_type === 'regular') {
    header("Location: ../roles/roles.php");
    exit();
}

// If user is admin and trying to access super_admin features
if ($user_type === 'admin' && $appointment_type === 'super_admin') {
    header("Location: ../appoint_roles/approle.php?type=admin");
    exit();
}

// Fetch roles with department names
$sql = "
    SELECT r.id, r.name AS role_name, d.name AS dept_name
    FROM roles r
    LEFT JOIN departments d ON r.department_id = d.id
    ORDER BY d.name, r.name
";
$result = $conn->query($sql);

// Group roles by department
$roles_by_dept = [];

while ($row = $result->fetch_assoc()) {
    $dept = $row['dept_name'] ?? 'No Department';
    $roles_by_dept[$dept][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Appoint Role Department</title>
    <link rel="stylesheet" href="approle.css">
</head>

<body onload="fetchNotifications(); showSuccessMessage();">
    <header>
        <?php include '../navbar/navbar.php'; ?>
    </header>

    <div id="content">

        <div class="page-title">
            <h1><?= $appointment_type === 'super_admin' ? 'Choose Department (Super Admin)' : 'Choose Department (Admin)' ?></h1>
            <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
                Add to Favorite
            </button>
        </div>

        <!-- Breadcrumbs -->
        <div class="breadcrumb">
            <ul>
                <li><a href="../dashboard_template/dashboard.php">Dashboard</a></li>
                <li><a href="../roles/roles.php">Roles</a></li>
                <li><?= $appointment_type === 'super_admin' ? 'Appoint Roles (super_admin)' : 'Appoint Roles (admin)' ?></li>
            </ul>
        </div>

        <div class="feature-description">
            <h3>How does this work?</h3>
            <p>
                Below is a list of all departments available in Politeknik Brunei.
                <?php if ($appointment_type === 'super_admin'): ?>
                    As a <strong>Super Admin</strong>, you can appoint roles to both regular users and admin users. You can also:
            <ul style="padding-left: 20px; margin-top: 10px;">
                <li>Appoint multiple roles to users</li>
                <li>Give feedback about role assignment requests</li>
                <li>Review and approve/deny role appeals</li>
                <li>Override admin role assignments</li>
            </ul>
        <?php else: ?>
            As an <strong>Admin</strong>, you can appoint roles to regular users (not super_admin). You can also:
            <ul style="padding-left: 20px; margin-top: 10px;">
                <li>Appoint multiple roles to regular users</li>
                <li>Review role assignment requests</li>
                <li>Handle role appeals from regular users</li>
            </ul>
        <?php endif; ?>
        </p>
        <ol style="padding-left: 20px; margin-top: 10px;">
            <li>Click on a department name to reveal the roles available under that department. Then, click on a role to proceed.</li>
            <li>You will be shown a requirements page that outlines the qualifications needed to hold that role, along with a list of current role holders.</li>
            <li>To appoint a candidate, simply click the search button to browse and select from available staff.</li>
        </ol>
        </div>


        <?php foreach ($roles_by_dept as $dept => $roles): ?>
            <div class="department-section">
                <div class="department-header collapsed" onclick="toggleDropdown(this)">
                    <?= htmlspecialchars((string)$dept) ?>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="role-list">
                    <?php foreach ($roles as $role): ?>
                        <li>
                            <a href="appoint.php?role_id=<?= $role['id'] ?>&type=<?= urlencode($appointment_type) ?>">
                                <div class="container">
                                    <div class="folder-icon">
                                        <i class="fas fa-user-tag"></i>
                                    </div>
                                    <div class="text">
                                        <h1><?= htmlspecialchars((string)$role['role_name']) ?></h1>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <!-- Include Font Awesome -->
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
        <script>
            function toggleDropdown(header) {
                const list = header.nextElementSibling;
                const isCollapsed = header.classList.contains('collapsed');

                // Toggle icon and visibility
                header.classList.toggle('collapsed');
                list.style.display = isCollapsed ? 'block' : 'none';
            }

            //favorites

            const pageName = "<?php echo $page_name; ?>";
            const pageUrl = "<?php echo $page_url; ?>";
            const button = document.getElementById('favoriteButton');

            // Check if already favorited when page loads
            document.addEventListener('DOMContentLoaded', function() {
                const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
                const exists = favorites.find(fav => fav.pageName === pageName);
                if (exists) {
                    button.classList.add('favorited');
                    button.textContent = 'Favorited';
                }
            });

            //favorites
            function toggleFavorite() {
                let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');

                const index = favorites.findIndex(fav => fav.pageName === pageName);

                if (index === -1) {
                    // Not favorited yet, add it
                    favorites.push({
                        pageName: pageName,
                        pageUrl: pageUrl
                    });
                    button.classList.add('favorited');
                    button.textContent = 'Favorited';
                } else {
                    // Already favorited, remove it
                    favorites.splice(index, 1);
                    button.classList.remove('favorited');
                    button.textContent = 'Add to Favorite';
                }

                localStorage.setItem('favorites', JSON.stringify(favorites));
            }
        </script>
    </div> <!-- /#content -->

    <!-- Footer -->
    <?php include '../footer/footer.php'; ?>

    <!-- Scroll-to-top button -->
    <?php include '../scrolltop/scrolltop.php'; ?>
    <script src="../scrolltop/scrolltop.js" defer></script>
</body>

</html>