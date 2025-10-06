<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

$page_name = $page_name ?? 'Your Role Resources'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$user_id = $_SESSION['id'];
$is_admin = false; // ✅ Fix: define variable before use

// Fetch user roles
$roles = [];
$stmt = $conn->prepare("
    SELECT roles.id, roles.name AS role_name, COALESCE(departments.name, 'No Department') AS dept_name
    FROM userroles
    INNER JOIN roles ON userroles.role_id = roles.id
    LEFT JOIN departments ON roles.department_id = departments.id
    WHERE userroles.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Role Resources List</title>
    <link rel="stylesheet" href="user_role_list.css">
</head>

<body>

    <header>
        <?php include '../navbar/navbar.php'; ?>
    </header>


    <div class="page-title" style="padding: 20px 5%;">
        <h1>Your Role Resources</h1>
    </div>
        <!-- Favorite button removed -->
    </div>

    <!-- Breadcrumb removed -->

    <div class="feature-description">
        <p>To view the resources you will have to choose which one you have to access to. Each role has different and unique resources</p>
    </div>


    <div class="content-body" style="padding: 0 5%;">
        <?php if (empty($roles)): ?>
            <p>You have no assigned roles. Please contact the administrator.</p>
        <?php else: ?>
            <?php foreach ($roles as $row): ?>
                <div class="role-box">
                    <h3><?= htmlspecialchars($row['role_name']) ?></h3>
                    <p>Department: <?= htmlspecialchars($row['dept_name']) ?></p>
                    <form action="role_resources.php" method="get" style="margin-top: 10px;">
                        <input type="hidden" name="role_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <button type="submit" class="role-btn">🔍 View Resources</button>
                    </form>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Breadcrumb and favorite scripts removed -->

</body>

</html>