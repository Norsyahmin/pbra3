<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

$page_name = $page_name ?? 'Roles'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

// Fetch user_type
$user_id = $_SESSION['id'];
$user_type = 'regular'; // default fallback

$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_type_result);
$stmt->fetch();
$user_type = $user_type_result ?? 'regular';
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="roles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <title>Roles</title>
</head>

<header>
  <?php include '../navbar/navbar.php'; ?>
</header>

<body onload="fetchNotifications()">
  <div class="page-title">
    <h1 style="font-size: 30px;">ROLES</h1>
  </div>

  <div class="content">
    <ul>
      <li>
        <div class="container" onclick="window.location.href='../myrole/myrole.php';" style="cursor: pointer;">
          <a href="#">
            <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
            <div class="text">
              <h1>My Role</h1>
              <p>This page enables you to monitor your recent activities and provides a brief overview of your role.</p>
            </div>
          </a>
        </div>
      </li>

      <?php if ($user_type === 'admin'): ?>
      <?php endif; ?>

      <?php if ($user_type === 'super_admin'): ?>
      <?php endif; ?>

      <li>
        <div class="container" onclick="window.location.href='../roles/role_appeals.php';" style="cursor: pointer;">
          <a href="#">
            <div class="folder-icon"><i class="fas fa-file-alt"></i></div>
            <div class="text">
              <h1>Role Appeals</h1>
              <p>Request for role changes, removals, or object to assignments. Review and manage appeals.</p>
            </div>
          </a>
        </div>
      </li>

      <li>
        <div class="container" onclick="window.location.href='../rolehistory/role_history.php';" style="cursor: pointer;">
          <a href="#">
            <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
            <div class="text">
              <h1>Role History</h1>
              <p>Show all your past role in one place</p>
            </div>
          </a>
        </div>
      </li>

      <?php if ($user_type !== 'regular'): ?>
        <li>
          <div class="container" onclick="window.location.href='../roles/role_export_import.php';" style="cursor: pointer;">
            <a href="#">
              <div class="folder-icon"><i class="fas fa-download"></i></div>
              <div class="text">
                <h1>Data Export/Import</h1>
                <p>Ability to export role data for reporting or import tasks from external sources.</p>
              </div>
            </a>
          </div>
        </li>
      <?php endif; ?>

      <li>
        <div class="container" onclick="window.location.href='<?= $user_type === 'admin' ? '../resourcescenter/admin_role_list.php' : '../resourcescenter/role_resources.php' ?>';" style="cursor: pointer;">
          <a href="#">
            <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
            <div class="text">
              <h1>Role Resources</h1>
              <p><?= $user_type === 'admin'
                    ? 'View all roles and manage teaching resources by department.'
                    : 'Where you can find all your role training resources' ?>
              </p>
            </div>
          </a>
        </div>
      </li>

    </ul>
  </div>

  <?php include '../footer/footer.php'; ?>

</body>

</html>
