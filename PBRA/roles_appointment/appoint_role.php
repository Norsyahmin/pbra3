<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

$prefill_to = isset($_GET['to']) ? $_GET['to'] : '';

$page_name = $page_name ?? 'Appoint Role';
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1; // Default to user_id 1 for testing
$appointment_type = $_GET['type'] ?? 'admin';
$current_session_user_type = $_SESSION['user_type'] ?? 'regular';

if (!$selected_user_id) {
    die('No user selected');
}

// Basic permission checks
if ($current_session_user_type === 'regular' || ($current_session_user_type === 'admin' && $appointment_type === 'super_admin')) {
    header('Location: ../roles/roles.php');
    exit();
}

$current_session_user_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;

// Fetch all users for the selection dropdown
$allUsers = [];
$usersQuery = "SELECT id, full_name, email FROM users ORDER BY full_name ASC";
$usersResult = $conn->query($usersQuery);
if ($usersResult) {
    while ($row = $usersResult->fetch_assoc()) {
        $allUsers[] = $row;
    }
}

// Fetch details of the selected user
$userStmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE id = ?");
$userStmt->bind_param('i', $selected_user_id);
$userStmt->execute();
$selected_user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$selected_user) {
    die('User not found.');
}

// Fetch full user profile for candidate matching
$userProfileStmt = $conn->prepare("SELECT id, full_name, email, education, work_experience, profile_pic FROM users WHERE id = ?");
$userProfileStmt->bind_param('i', $selected_user_id);
$userProfileStmt->execute();
$selected_user_full_profile = $userProfileStmt->get_result()->fetch_assoc();
$userProfileStmt->close();

// Fetch all department requirements
$allDepartmentRequirements = [];
$reqQuery = "SELECT department_id, requirement_type, keyword, description FROM department_requirements";
$reqResult = $conn->query($reqQuery);
if ($reqResult) {
    while ($row = $reqResult->fetch_assoc()) {
        $allDepartmentRequirements[$row['department_id']][] = $row;
    }
}

// Matching function (re-added)
function isCandidate($user, $requirements)
{
    $educationLevels = ['certificate', 'diploma', 'degree', 'bachelor', 'honours', 'master', 'phd'];
    $matchesEducation = false;
    $matchesExperience = false;

    foreach ($requirements as $req) {
        $field = strtolower($req['requirement_type'] === 'education' ? ($user['education'] ?? '') : ($user['work_experience'] ?? ''));
        $keyword = strtolower($req['keyword'] ?? '');

        // Standardize quotes and hyphens for comparison
        $field = str_replace(['â€™', '’', '‘', '–', '—'], ["'", "'", "'", '-', '-'], $field);
        $keyword = str_replace(['â€™', '’', '‘', '–', '—'], ["'", "'", "'", '-', '-'], $keyword);

        if ($req['requirement_type'] === 'education') {
            $userLevel = $requiredLevel = -1;
            foreach ($educationLevels as $i => $level) {
                if (stripos($field, $level) !== false) $userLevel = $i;
                if (stripos($keyword, $level) !== false) $requiredLevel = $i;
            }
            if ($userLevel >= $requiredLevel && $userLevel !== -1 && $requiredLevel !== -1) {
                $matchesEducation = true;
            }
        } else { // 'experience'
            if ($keyword !== '' && stripos($field, $keyword) !== false) {
                $matchesExperience = true;
            }
        }
    }
    return $matchesEducation || $matchesExperience;
}

// Fetch all roles, categorized by department, and also their department_id
$allRolesWithDeptId = [];
$rolesQuery = "
    SELECT r.id, r.name AS role_name, r.department_id, d.name AS dept_name
    FROM roles r
    LEFT JOIN departments d ON r.department_id = d.id
    ORDER BY d.name, r.name
";
$rolesResult = $conn->query($rolesQuery);
if ($rolesResult) {
    while ($row = $rolesResult->fetch_assoc()) {
        $dept = $row['dept_name'] ?? 'No Department';
        $allRolesWithDeptId[$dept][] = $row;
    }
}

// Determine suggested roles (roles the user is a candidate for)
$suggestedRoleIds = [];
if ($selected_user_full_profile) {
    foreach ($allRolesWithDeptId as $deptName => $rolesInDept) {
        foreach ($rolesInDept as $role) {
            $role_dept_id = $role['department_id'];
            if (isset($allDepartmentRequirements[$role_dept_id]) && !empty($allDepartmentRequirements[$role_dept_id])) {
                if (isCandidate($selected_user_full_profile, $allDepartmentRequirements[$role_dept_id])) {
                    $suggestedRoleIds[] = $role['id'];
                }
            }
        }
    }
}
$allRoles = $allRolesWithDeptId; // Reassign to $allRoles for consistent iteration later

// Fetch roles currently held by the selected user
$userCurrentRoles = [];
$userRolesStmt = $conn->prepare("SELECT role_id FROM userroles WHERE user_id = ?");
$userRolesStmt->bind_param('i', $selected_user_id);
$userRolesStmt->execute();
$userRolesResult = $userRolesStmt->get_result();
while ($row = $userRolesResult->fetch_assoc()) {
    $userCurrentRoles[] = $row['role_id'];
}
$userRolesStmt->close();

$modal_message = '';

// Handle role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_roles'])) {
    // $selected_user_id is already defined at the top of the script
    $new_role_ids = isset($_POST['roles']) ? array_map('intval', $_POST['roles']) : [];

    // Fetch current roles for the user (already done in $userCurrentRoles)
    $existing_role_ids = $userCurrentRoles;

    $roles_to_add = array_diff($new_role_ids, $existing_role_ids);
    $roles_to_remove = array_diff($existing_role_ids, $new_role_ids);

    $appointed_by = $current_session_user_id;
    $modal_messages = [];

    // Add new roles
    if (!empty($roles_to_add)) {
        $insertStmt = $conn->prepare('INSERT INTO userroles (user_id, role_id, appointed_at, appointed_by) VALUES (?, ?, NOW(), ?)');
        $historyInsertStmt = $conn->prepare('INSERT INTO role_history (user_id, role_id, assigned_at) VALUES (?, ?, NOW())');
        $notificationStmt = $conn->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');

        foreach ($roles_to_add as $role_id_to_add) {
            $insertStmt->bind_param('iii', $selected_user_id, $role_id_to_add, $appointed_by);
            if ($insertStmt->execute()) {
                $historyInsertStmt->bind_param('ii', $selected_user_id, $role_id_to_add);
                $historyInsertStmt->execute();

                // Get role name for notification
                $roleNameStmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
                $roleNameStmt->bind_param('i', $role_id_to_add);
                $roleNameStmt->execute();
                $roleName = $roleNameStmt->get_result()->fetch_assoc()['name'] ?? 'a role';
                $roleNameStmt->close();

                $notifyMessage = "You have been appointed to the role of " . $roleName;
                $notificationStmt->bind_param('is', $selected_user_id, $notifyMessage);
                $notificationStmt->execute();
                $modal_messages[] = "User appointed to role ID {$role_id_to_add}.";
            } else {
                $modal_messages[] = "Error appointing user to role ID {$role_id_to_add}.";
            }
        }
        $insertStmt->close();
        $historyInsertStmt->close();
        $notificationStmt->close();
    }

    // Remove old roles
    if (!empty($roles_to_remove)) {
        $deleteStmt = $conn->prepare('DELETE FROM userroles WHERE user_id = ? AND role_id = ?');
        $historyUpdateStmt = $conn->prepare('UPDATE role_history SET removed_at = NOW() WHERE user_id = ? AND role_id = ? AND removed_at IS NULL');
        $notificationStmt = $conn->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');

        foreach ($roles_to_remove as $role_id_to_remove) {
            $deleteStmt->bind_param('ii', $selected_user_id, $role_id_to_remove);
            if ($deleteStmt->execute()) {
                $historyUpdateStmt->bind_param('ii', $selected_user_id, $role_id_to_remove);
                $historyUpdateStmt->execute();

                // Get role name for notification
                $roleNameStmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
                $roleNameStmt->bind_param('i', $role_id_to_remove);
                $roleNameStmt->execute();
                $roleName = $roleNameStmt->get_result()->fetch_assoc()['name'] ?? 'a role';
                $roleNameStmt->close();

                $notifyMessage = "You have been dismissed from the role of " . $roleName;
                $notificationStmt->bind_param('is', $selected_user_id, $notifyMessage);
                $notificationStmt->execute();
                $modal_messages[] = "User dismissed from role ID {$role_id_to_remove}.";
            } else {
                $modal_messages[] = "Error dismissing user from role ID {$role_id_to_remove}.";
            }
        }
        $deleteStmt->close();
        $historyUpdateStmt->close();
        $notificationStmt->close();
    }

    $modal_message = implode('<br>', $modal_messages); // Combine all messages
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Roles for <?= htmlspecialchars($selected_user['full_name']) ?></title>
    <link rel="stylesheet" href="appoint_role.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0pBOK5pQh/M6A3fJ5S/X5uE7J2pM8z0Y6K6p6jLsQf4i5P5b5pP5pP5b5pP5pP5pP5p..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <header>
        <?php include '../navbar/navbar.php'; ?>
    </header>

    <main class="app-container">
        <div class="toprow">
            <h1 class="title">Manage Roles for <?= htmlspecialchars($selected_user['full_name']) ?></h1>
            <div class="actions">
                <button type="button" id="favoriteButton" class="favorite-button" onclick="toggleFavorite()">Add to Favorite</button>
            </div>
        </div>

        <section class="card select-user-card">
            <h2 class="section-title">Select Employee</h2>
            <select id="userSelect" class="form-select">
                <?php foreach ($allUsers as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= ($user['id'] == $selected_user_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </section>

        <section class="card user-info-card">
            <h2 class="section-title">User Details</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($selected_user['full_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($selected_user['email']) ?></p>
        </section>

        <section class="card roles-selection-card">
            <h2 class="section-title">Assign Roles</h2>
            <form method="POST">
                <input type="hidden" name="selected_user_id" value="<?= $selected_user['id'] ?>">
                <?php foreach ($allRoles as $dept => $roles): ?>
                    <div class="department-roles">
                        <h3><?= htmlspecialchars($dept) ?></h3>
                        <div class="role-checkbox-group">
                            <?php foreach ($roles as $role): ?>
                                <label class="role-checkbox-item">
                                    <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>"
                                        <?= in_array($role['id'], $userCurrentRoles) ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($role['role_name']) ?>
                                    <?php if (in_array($role['id'], $suggestedRoleIds)): ?>
                                        <i class="fas fa-star suggested-role-icon" title="Suggested Role"></i>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="update_roles" class="btn primary assign-roles-btn">Update Roles</button>
            </form>
        </section>

        <!-- Modals -->
        <div id="successModal" class="modal" aria-hidden="true">
            <div class="modal-panel">
                <h3 id="successText"></h3>
                <div class="modal-actions"><button onclick="closeSuccessModal()" class="btn primary">OK</button></div>
            </div>
        </div>

        <?php if (!empty($modal_message)): ?>
            <script>document.addEventListener('DOMContentLoaded', function(){ openSuccessModal(<?= json_encode($modal_message) ?>); });</script>
        <?php endif; ?>
    </main>

    <?php include '../footer/footer.php'; ?>
    <?php include '../scrolltop/scrolltop.php'; ?>
    <script src="../scrolltop/scrolltop.js" defer></script>

    <script>
        function openSuccessModal(message){ document.getElementById('successText').innerText = message; document.getElementById('successModal').setAttribute('aria-hidden','false'); }
        function closeSuccessModal(){ document.getElementById('successModal').setAttribute('aria-hidden','true'); location.reload(); } // Reload page to reflect changes

        // favorites (existing code, keeping it for now)
        const pageName = 'Manage Roles for <?= htmlspecialchars(addslashes($selected_user['full_name'])) ?>'; // Updated pageName
        const pageUrl = "<?= htmlspecialchars(addslashes($page_url)) ?>";
        const favBtn = document.getElementById('favoriteButton');
        document.addEventListener('DOMContentLoaded', function(){ try{ const favorites = JSON.parse(localStorage.getItem('favorites')||'[]'); const exists = favorites.find(f=>f.pageName===pageName); if(exists){ favBtn.classList.add('favorited'); favBtn.textContent='Favorited'; } }catch(e){} });
        function toggleFavorite(){ let favorites = JSON.parse(localStorage.getItem('favorites')||'[]'); const idx = favorites.findIndex(f => f.pageName===pageName); if(idx===-1){ favorites.push({pageName:pageName,pageUrl:pageUrl}); favBtn.classList.add('favorited'); favBtn.textContent='Favorited'; } else { favorites.splice(idx,1); favBtn.classList.remove('favorited'); favBtn.textContent='Add to Favorite'; } localStorage.setItem('favorites', JSON.stringify(favorites)); }

        document.addEventListener('DOMContentLoaded', function() {
            const userSelect = document.getElementById('userSelect');
            if (userSelect) {
                userSelect.addEventListener('change', function() {
                    const selectedUserId = this.value;
                    window.location.href = `appoint_role.php?user_id=${selectedUserId}`;
                });
            }
        });
    </script>
</body>
</html>
