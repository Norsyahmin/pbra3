<?php
// Start session if possible. We try to start a session but do NOT redirect here.
// Navbar is included by both public and protected pages; auth enforcement should
// happen at the page level (via includes/auth.php). The navbar will behave
// gracefully when no session or no logged-in user is present.
if (session_status() === PHP_SESSION_NONE) {
    // Suppress warnings if headers already sent; session may already be started by the caller.
    @session_start();
}

// Do not redirect from the navbar. Instead expose a local variable with the
// logged-in user id if available; otherwise keep it null. Pages that need to
// enforce authentication should include `includes/auth.php` before rendering.
$logged_in_user_id = $_SESSION['id'] ?? null;

// Determine profile picture path
$profile_pic = (!empty($_SESSION['profile_pic']) && file_exists('../' . $_SESSION['profile_pic']))
    ? '../' . htmlspecialchars($_SESSION['profile_pic'])
    : '../profile/images/default-profile.jpg';
include '../mypbra_connect.php'; // Fixed path for DB connection

// Get logged-in user information
$logged_in_user = $_SESSION['full_name'] ?? 'Unknown User';

// Fetch unread notifications with improved error handling
$notifications = [];
$unread_count = 0;

if ($logged_in_user_id) {
    try {
        $sql = "SELECT message, url FROM notifications WHERE user_id=? AND (is_read=FALSE OR is_read=0) ORDER BY created_at DESC LIMIT 10";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $logged_in_user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Store notifications in an array
            while ($row = $result->fetch_assoc()) {
                $notifications[] = [
                    'message' => htmlspecialchars($row['message']),
                    'url' => htmlspecialchars($row['url'] ?? '#')
                ];
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare notification query: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Error fetching notifications: " . $e->getMessage());
    }
}

// Count unread notifications
$unread_count = count($notifications);
?>

<!-- Start of the main page -->
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<link rel="stylesheet" href="../navbar/navbar.css" />

<!-- Navigation Bar Component -->
<div id="navBarComponent">
    <!-- Top bar -->
    <div id="topbar" class="topbar">
        <div class="topbar-inner">
            <!-- Left: Hamburger -->
            <div class="topbar-left">
                <div class="hamburger-container" onclick="navBar.toggleSidebar(event)">
                    <span class="hamburger">&#9776;</span>
                </div>
            </div>
            <!-- Center: Search -->
            <div class="topbar-center">
                <input type="text" id="topSearch" class="search-bar" placeholder="Search..." onfocus="navBar.openSearch()" />
            </div>
            <!-- Right: Notification, Mail, Profile -->
            <div class="topbar-right" style="position:relative;">
                <button class="notification-btn" onclick="toggleNotifications()" aria-haspopup="true" aria-expanded="false" aria-label="Notifications">
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    <?php if ($unread_count > 0) { ?>
                        <span class="notification-dot" id="notification-dot"><?php echo $unread_count; ?></span>
                    <?php } else { ?>
                        <span class="notification-dot" id="notification-dot" style="display:none;"></span>
                    <?php } ?>
                </button>
                <!-- Notification Dropdown -->
                <div class="notification-container" id="notification-container" style="display:none;">
                    <div class="notification-header">Notifications</div>
                    <ul class="notification-list" id="notification-list">
                        <?php if (!empty($notifications)) {
                            foreach ($notifications as $note) { ?>
                                <li>
                                    <a href="<?= htmlspecialchars($note['url']) ?>" style="text-decoration:none; color:black;">
                                        <?= $note['message'] ?>
                                    </a>
                                </li>
                            <?php }
                        } else { ?>
                            <li>No new notifications</li>
                        <?php } ?>
                    </ul>

                    <!-- Notification Dropdown -->
                </div>
                <div class="mail-button-container">
                    <button class="mail-button" onclick="window.location.href='../mails/mail_page.php';" style="cursor: pointer;" aria-label="Mail">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                    </button>
                </div>
                <!-- Chat button -->
                <div class="chat-button-container">
                    <button class="chat-button" onclick="window.location.href='../chat/chat.php';" style="cursor: pointer;" aria-label="Chat" title="Chat">
                        <i class="fas fa-comments" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- moved user-info inside topbar-right so icons appear directly left of profile -->
                <div class="user-info">
                    <div id="userMenu" class="user-menu" onclick="navBar.toggleUserMenu(event)" tabindex="0" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($profile_pic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Picture" id="profile-pic">
                        <ul class="user-dropdown" role="menu" aria-label="User menu">
                            <li role="menuitem"><a href="../profile/profile.php">View Profile</a></li>
                            <li role="menuitem"><a href="../login/login.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Component -->
        <div id="sidebar" class="sidebar">
            <span class="closebtn" onclick="navBar.closeSidebar()">&times;</span>
            <a href="../homepage/homepage.php">Home</a>
            <a href="../myrole/myrole.php">Activity Logs</a>
            <a href="../task_management/task_management.php">Task Management</a>
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'super_admin') { ?>
                <a href="../statistics/statistics.php">Statistics</a>
            <?php } ?>
            <?php if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'super_admin')) { ?>
                <a href="../registration/registration.php">Register User</a>
            <?php } ?>
            <a href="../virtualmeeting/virtualmeeting.php">Virtual Meeting</a>
            <?php if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'super_admin')) { ?>
                <a href="../appoint_roles/approle.php">Appoint Roles</a>
            <?php } ?>
            <a href="../dashboard_template/template.php">Template</a>
            <?php if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'super_admin')) { ?>
                <a href="../roles_appointment/roles_appointment.php">Roles Appointment</a>
            <?php } ?>
               <?php if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'super_admin')) { ?>
                <a href="../feedback/receiver_feedback.php">Receiver Feedback</a>
            <?php } ?>
            
        </div>

        <!-- Overlay background -->
        <div id="sidebarOverlay" class="overlay" onclick="navBar.closeSidebar()"></div>

        <!-- Search overlay -->
        <div id="searchOverlay" class="search-overlay" onclick="navBar.closeSearch(event)">
            <div class="search-popup" onclick="event.stopPropagation()">
                <input type="text" id="popupSearch" placeholder="Type to search..." />
                <span class="search-close" onclick="navBar.closeSearch(event)">&times;</span>
            </div>
        </div>

    </div>

    <script src="../navbar/navbar.js" defer></script>