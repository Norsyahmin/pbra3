<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

// Ensure session is started and CSRF token exists for forms
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        // fallback
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

$page_name = $page_name ?? 'Profile'; // or whatever you want
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$user_id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : $_SESSION['id'];

$sql = "SELECT full_name, email, start_date, work_experience, education, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Error: No user found.";
    exit();
}

$roles = [];
$role_query = "SELECT r.name AS role_name, d.name AS department_name
               FROM userroles ur
               JOIN roles r ON ur.role_id = r.id
               JOIN departments d ON r.department_id = d.id
               WHERE ur.user_id = ?";
$role_stmt = $conn->prepare($role_query);
$role_stmt->bind_param("i", $user_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();

while ($row = $role_result->fetch_assoc()) {
    $roles[] = $row['role_name'] . " (" . $row['department_name'] . ")";
}
$role_stmt->close();

$profile_pic_val = $user['profile_pic'] ?? '';
$profile_pic = (!empty($profile_pic_val) && file_exists('../' . $profile_pic_val))
    ? '../' . htmlspecialchars($profile_pic_val)
    : '../profile/images/default-profile.jpg';

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="profile.css" />
    <link rel="stylesheet" href="navbar.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <title>Profile</title>
</head>

<header>
    <?php include '../navbar/navbar.php'; ?>
</header>

<body onload="fetchNotifications()">

    <div id="content" class="content">

    <div class="page-title">
        <h1 style="font-size: 30px;">PROFILE</h1>
    </div>

    <?php if (!empty($_SESSION['change_pass_success'])): ?>
        <div id="page-flash" class="success-message" style="margin:12px 20px;">
            <?php echo htmlspecialchars($_SESSION['change_pass_success']); unset($_SESSION['change_pass_success']); ?>
        </div>
    <?php elseif (!empty($_SESSION['change_pass_error'])): ?>
        <div id="page-flash" class="error-message" style="margin:12px 20px;">
            <?php echo htmlspecialchars($_SESSION['change_pass_error']); unset($_SESSION['change_pass_error']); ?>
        </div>
    <?php else: ?>
        <div id="page-flash" style="display:none; margin:12px 20px;"></div>
    <?php endif; ?>

    <!-- Breadcrumbs removed as per request -->

    <div class="profile-container">
        <div class="user-profile">
            <img src="<?php echo (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic']))
                            ? '../' . htmlspecialchars($user['profile_pic'])
                            : '../profile/images/default-profile.jpg'; ?>"
                alt="Profile Picture" />

        </div>

        <div class="user-details">
            <span id="name"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></span>
            <span id="email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
            <span id="role"><?php echo !empty($roles) ? implode(', ', $roles) : 'No roles assigned'; ?></span>
            <span id="date-start"><?php echo htmlspecialchars($user['start_date'] ?? ''); ?></span>
        </div>

        <div class="buttons">
            <?php if ($_SESSION['id'] == $user_id): ?>
                <button id="edit-btn">Edit Profile</button>
                <!-- Make Change Password visually consistent with other action buttons -->
                <button id="change-pass-btn" class="submit-btn" style="background: #ffffff; color: #333; border: 1px solid #ccc;">Change Password</button>
            <?php endif; ?>

            <button type="button" class="view-log-btn" onclick="window.location.href='../myrole/myrole.php?id=<?php echo $user_id; ?>';">View Activity Log</button>

            <?php if ($_SESSION['id'] == $user_id): ?>
                <button class="logout-btn" onclick="confirmLogout()">Logout</button>
            <?php endif; ?>
        </div>

        <!-- Logout Popup -->
        <div id="logoutConfirmBox" class="logout-popup" style="display: none;">
            <div class="logout-popup-content">
                <h3>Are you sure you want to logout?</h3>
                <div class="popup-actions">
                    <button onclick="proceedLogout()" class="logout-btn">Yes</button>
                    <button onclick="closeLogoutPopup()" class="cancel-btn">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <?php if ($_SESSION['id'] == $user_id): ?>
            <div id="editProfileModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h3>Edit Profile</h3>
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <label>Full Name:</label><br>
                        <!-- Full name is read-only as requested -->
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly><br><br>
                        <!-- Email edit disabled: show but do not allow editing -->
                        <label>Email:</label><br>
                        <input type="email" name="email_display" value="<?php echo htmlspecialchars($user['email']); ?>" disabled><br><br>
                        <label>Profile Picture:</label><br>
                        <input type="file" name="profile_pic" class="file-input"><br><br>
                        <label>Work Experience:</label><br>
                        <textarea name="work_experience" rows="4"><?php echo str_replace("\n", "&#10;", htmlspecialchars($user['work_experience'])); ?></textarea><br><br>
                        <label>Education:</label><br>
                        <textarea name="education" rows="4"><?php echo str_replace("\n", "&#10;", htmlspecialchars($user['education'])); ?></textarea><br><br>
                        <button type="submit" class="submit-btn">Save Changes</button>
                    </form>
                    <form id="deleteProfilePicForm" action="delete_profile_pic.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <button type="submit" class="delete-btn">Remove Profile Picture</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Change Password Modal -->
        <?php if ($_SESSION['id'] == $user_id): ?>
            <div id="changePasswordModal" class="modal change-pass-modal" style="display:none;">
                <div class="modal-content">
                    <button class="close" id="closeChangePass" aria-label="Close modal">&times;</button>
                    <h3>Change Password</h3>
                    <div class="modal-flash" id="change-pass-flash"></div>
                    <form id="changePassForm">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <div class="field-group">
                            <label for="current_password">Current Password</label>
                            <input id="current_password" type="password" name="current_password" placeholder="Enter current password" required>
                        </div>

                        <div class="field-group">
                            <label for="new_password">New Password</label>
                            <input id="new_password" type="password" name="new_password" placeholder="Minimum 8 characters" required>
                            <div id="password-strength" class="password-strength"></div>
                        </div>

                        <div class="field-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input id="confirm_password" type="password" name="confirm_password" placeholder="Repeat new password" required>
                        </div>

                        <div class="modal-actions">
                            <button type="submit" class="submit-btn">Save</button>
                            <button type="button" class="cancel-btn" onclick="changePassModal.style.display='none';">Cancel</button>
                        </div>
                        <p class="helper-text">Tip: Use a strong password with a mix of uppercase, numbers and symbols.</p>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="background">
        <div class="work-experience">
            <h2>Work Experience</h2>
            <ul>
                    <?php $work_exp = $user['work_experience'] ?? ''; foreach (explode("\n", $work_exp) as $experience): ?>
                        <?php if (!empty(trim($experience))) echo '<li>' . htmlspecialchars($experience) . '</li><br>'; ?>
                    <?php endforeach; ?>
            </ul>
        </div>

        <div class="education">
            <h2>Education</h2>
            <ul>
                <?php $edu = $user['education'] ?? ''; foreach (explode("\n", $edu) as $education): ?>
                    <?php if (!empty(trim($education))) echo '<li>' . htmlspecialchars($education) . '</li>'; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Delete Profile Picture Modal -->
    <div id="deletePicModal" class="modal" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:white; padding:30px 20px; border-radius:10px; max-width:400px; width:90%; text-align:center;">
            <h3>Remove Profile Picture?</h3>
            <p>Are you sure you want to remove your profile picture?</p>
            <div style="margin-top:20px;">
                <button id="confirmDeletePic" style="padding:10px 20px; background-color:#d9534f; color:white; border:none; border-radius:5px; margin-right:10px;">Yes</button>
                <button onclick="closeDeletePicModal()" style="padding:10px 20px; background-color:#ccc; border:none; border-radius:5px;">Cancel</button>
            </div>
        </div>
    </div>


    <script>
        // Open the delete profile picture modal
        document.querySelector('.delete-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('deletePicModal').style.display = 'flex';
        });

        // Close modal function
        function closeDeletePicModal() {
            document.getElementById('deletePicModal').style.display = 'none';
        }

        // Confirm delete
        document.getElementById('confirmDeletePic')?.addEventListener('click', function() {
            document.getElementById('deleteProfilePicForm').submit();
        });

        document.getElementById("edit-btn")?.addEventListener("click", () => {
            document.getElementById("editProfileModal").style.display = "flex";
        });

        function closeModal() {
            document.getElementById("editProfileModal").style.display = "none";
        }

        function confirmLogout() {
            document.getElementById("logoutConfirmBox").style.display = "flex";
        }

        function closeLogoutPopup() {
            document.getElementById("logoutConfirmBox").style.display = "none";
        }

        function proceedLogout() {
            window.location.href = "logout.php";
        }

        function confirmProfilePicDeletion() {
            return confirm("Are you sure you want to remove your profile picture?");
        }

        // Breadcrumbs removed.

        // Wire modal open/close for edit and change password
        document.getElementById("edit-btn")?.addEventListener("click", () => {
            document.getElementById("editProfileModal").style.display = "flex";
        });

        // Open change-password modal instead of navigating away
        const changePassBtn = document.getElementById("change-pass-btn");
        const changePassModal = document.getElementById("changePasswordModal");
        const closeChangePass = document.getElementById('closeChangePass');
        const changePassForm = document.getElementById('changePassForm');
        const changePassFlash = document.getElementById('change-pass-flash');
        const pageFlash = document.getElementById('page-flash');

        changePassBtn?.addEventListener('click', () => {
            if (changePassModal) changePassModal.style.display = 'flex';
        });

        closeChangePass?.addEventListener('click', () => {
            if (changePassModal) changePassModal.style.display = 'none';
            changePassFlash.innerHTML = '';
        });

        // Password strength helper
        function passwordStrength(password) {
            let score = 0;
            if (!password) return { score, text: 'Too short' };
            if (password.length >= 8) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            let text = ['Very weak','Weak','Fair','Good','Strong'][score];
            return { score, text };
        }

        const newPasswordInput = document.getElementById('new_password');
        const strengthEl = document.getElementById('password-strength');
        newPasswordInput?.addEventListener('input', (e) => {
            const val = e.target.value;
            const res = passwordStrength(val);
            strengthEl.textContent = `Strength: ${res.text}`;
            strengthEl.style.color = ['#d9534f','#f0ad4e','#f0ad4e','#5bc0de','#5cb85c'][res.score];
        });

        // Submit password change via fetch (AJAX)
        changePassForm?.addEventListener('submit', async function(e) {
            e.preventDefault();
            changePassFlash.innerHTML = '';

            // client-side validation
            const current = changePassForm.querySelector('[name="current_password"]').value.trim();
            const newp = changePassForm.querySelector('[name="new_password"]').value.trim();
            const conf = changePassForm.querySelector('[name="confirm_password"]').value.trim();
            const csrf = changePassForm.querySelector('[name="csrf_token"]').value;

            if (!current || !newp || !conf) {
                changePassFlash.innerHTML = '<div class="error-message">All fields are required.</div>';
                return;
            }

            if (newp !== conf) {
                changePassFlash.innerHTML = '<div class="error-message">New password and confirmation do not match.</div>';
                return;
            }

            const strength = passwordStrength(newp);
            if (strength.score < 3) {
                changePassFlash.innerHTML = '<div class="error-message">Password is too weak. Use at least 8 chars, mix upper/lower, numbers and symbols.</div>';
                return;
            }

            const formData = new FormData(changePassForm);
            formData.set('csrf_token', csrf);

            try {
                const res = await fetch('change_password.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();

                if (data.success) {
                    // show success in modal and page flash
                    changePassFlash.innerHTML = '<div class="success-message">' + (data.message || 'Password changed') + '</div>';
                    pageFlash.style.display = 'block';
                    pageFlash.className = 'success-message';
                    pageFlash.textContent = data.message || 'Password changed successfully.';

                    // close modal after short delay
                    setTimeout(() => {
                        changePassModal.style.display = 'none';
                        changePassFlash.innerHTML = '';
                    }, 1200);
                } else {
                    changePassFlash.innerHTML = '<div class="error-message">' + (data.message || 'Failed to change password') + '</div>';
                }
            } catch (err) {
                changePassFlash.innerHTML = '<div class="error-message">Unexpected error. Try again.</div>';
                console.error(err);
            }
        });

        // favorite feature removed from profile page
    </script>

    </div> <!-- end #content -->

    <?php include '../footer/footer.php'; ?>
    <?php include '../scrolltop/scrolltop.php'; ?>
    <script src="../scrolltop/scrolltop.js" defer></script>

</body>

</html>