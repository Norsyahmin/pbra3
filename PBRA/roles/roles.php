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
    <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">
      Add to Favorite
    </button>
  </div>

  <div class="breadcrumb">
    <ul id="breadcrumb-list"></ul>
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
        <li>
          <div class="container" onclick="window.location.href='../appoint_roles/approle.php?type=admin';" style="cursor: pointer;">
            <a href="#">
              <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
              <div class="text">
                <h1>Appoint Roles (admin)</h1>
                <p>Admin can appoint roles to regular users not super_admin. Admin can also appoint multiple roles to the users.</p>
              </div>
            </a>
          </div>
        </li>

        <li>
          <div class="container" onclick="window.location.href='../distributetask/distributetask.php';" style="cursor: pointer;">
            <a href="#">
              <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
              <div class="text">
                <h1>Distribute Task</h1>
                <p>Capable of assigning any task to others and automatically updating it in their calendars.</p>
              </div>
            </a>
          </div>
        </li>
      <?php endif; ?>

      <?php if ($user_type === 'super_admin'): ?>
        <li>
          <div class="container" onclick="window.location.href='../appoint_roles/approle.php?type=admin';" style="cursor: pointer;">
            <a href="#">
              <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
              <div class="text">
                <h1>Appoint Roles (admin)</h1>
                <p>Admin can appoint roles to regular users not super_admin. Admin can also appoint multiple roles to the users.</p>
              </div>
            </a>
          </div>
        </li>

        <li>
          <div class="container" onclick="window.location.href='../appoint_roles/approle.php?type=super_admin';" style="cursor: pointer;">
            <a href="#">
              <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
              <div class="text">
                <h1>Appoint Roles (super_admin)</h1>
                <p>Super_admin can appoint roles to regular/admin users. Can appoint multiple roles to the users. Give feedback about the request approval.</p>
              </div>
            </a>
          </div>
        </li>

        <li>
          <div class="container" onclick="window.location.href='../distributetask/distributetask.php';" style="cursor: pointer;">
            <a href="#">
              <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
              <div class="text">
                <h1>Distribute Task</h1>
                <p>Capable of assigning any task to others and automatically updating it in their calendars.</p>
              </div>
            </a>
          </div>
        </li>
      <?php endif; ?>

      <li>
        <div class="container" onclick="window.location.href='../kpi/kpi.php';" style="cursor: pointer;">
          <a href="#">
            <div class="folder-icon"><i class="fas fa-chart-bar"></i></div>
            <div class="text">
              <h1>Individual Role-Level KPI</h1>
              <p>Track and measure your performance against key performance indicators specific to your assigned roles.</p>
            </div>
          </a>
        </div>
      </li>

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

  <script>
    // Set breadcrumb
    document.getElementById('breadcrumb-list').innerHTML = '<li><a href="../dashboard_template/dashboard.php">Dashboard</a></li><li>Roles</li>';

    function toggleFavorite() {
      const button = document.getElementById('favoriteButton');
      let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');

      const currentPage = {
        name: '<?= $page_name ?>',
        url: '<?= $page_url ?>'
      };

      const index = favorites.findIndex(fav => fav.url === currentPage.url);

      if (index === -1) {
        // Not favorited yet, add it
        favorites.push(currentPage);

        // Show success message
        showSuccessMessage('Added to favorites!');

        localStorage.setItem('favorites', JSON.stringify(favorites));

        // Optional: Send to server
        fetch('../includes/favorite.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(currentPage)
        }).catch(error => {
          console.warn('Failed to save favorite to server:', error);
        });
        button.classList.add('favorited');
        button.textContent = 'Favorited';
      } else {
        // Already favorited, remove it
        favorites.splice(index, 1);
        showSuccessMessage('Removed from favorites!');
        localStorage.setItem('favorites', JSON.stringify(favorites));
        button.classList.remove('favorited');
        button.textContent = 'Add to Favorite';
      }
    }

    function showSuccessMessage(message) {
      // Create a temporary notification
      const notification = document.createElement('div');
      notification.className = 'success-notification';
      notification.textContent = message;
      notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      `;

      document.body.appendChild(notification);

      // Remove after 3 seconds
      setTimeout(() => {
        notification.remove();
      }, 3000);
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

      localStorage.setItem('favorites', JSON.stringify(favorites));
    });
  </script>

</body>

</html>