<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

$page_name = $page_name ?? 'Roles Appointment';
$page_url = $page_url ?? $_SERVER['REQUEST_URI'];

$user_type = $_SESSION['user_type'] ?? 'regular';
$appointment_type = $_GET['type'] ?? 'admin'; // admin or super_admin

// Basic permission checks (mirror appoint_roles behavior)
if ($user_type === 'regular') {
	header("Location: ../roles/roles.php");
	exit();
}

if ($user_type === 'admin' && $appointment_type === 'super_admin') {
	header("Location: roles_appointment.php?type=admin");
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

$roles_by_dept = [];
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$dept = $row['dept_name'] ?? 'No Department';
		$roles_by_dept[$dept][] = $row;
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title><?= htmlspecialchars($page_name) ?></title>
	<link rel="stylesheet" href="roles_appointment.css">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<!-- Include Font Awesome -->
	<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body onload="fetchNotifications && fetchNotifications();">
	<header>
		<?php include '../navbar/navbar.php'; ?>
	</header>

	<main>
		<!-- Page Title -->
		<div class="page-title">
			<h1 class="title"><?= $appointment_type === 'super_admin' ? 'Roles Appointment (Super Admin)' : 'Roles Appointment (Admin)' ?></h1>
			<div class="actions"></div>
		</div>

		<!-- BEGIN: Main Content (added) -->
		<div id="content" class="app-container">
			<div class="toolbar">
				<input id="roleSearch" class="search-input" type="search" placeholder="Search roles, departments or keywords..." aria-label="Search roles">
				<select id="deptFilter" class="select-filter" aria-label="Filter by department">
					<option value="">All departments</option>
					<?php foreach (array_keys($roles_by_dept) as $d): ?>
						<option value="<?= htmlspecialchars((string)$d) ?>"><?= htmlspecialchars((string)$d) ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<p class="lead">Browse departments and roles. Use search or department filter to narrow results.</p>

			<?php if (empty($roles_by_dept)): ?>
				<p class="empty">No roles found.</p>
			<?php endif; ?>

			<section class="departments">
				<?php foreach ($roles_by_dept as $dept => $roles): ?>
					<article class="department-section">
						<header class="department-header collapsed" onclick="toggleDropdown(this)">
							<div class="dept-title"><?= htmlspecialchars((string)$dept) ?></div>
							<div class="dept-toggle"><i class="fas fa-chevron-down"></i></div>
						</header>
						<ul class="role-list">
							<?php foreach ($roles as $role): ?>
								<li class="role-item" data-role="<?= htmlspecialchars((string)$role['role_name']) ?>" data-dept="<?= htmlspecialchars((string)$dept) ?>">
									<a class="role-link" href="appoint_role.php?role_id=<?= $role['id'] ?>&type=<?= urlencode($appointment_type) ?>">
										<div class="role-item-inner">
											<div class="folder-icon"><i class="fas fa-user-tag" aria-hidden="true"></i></div>
											<div class="text">
												<h3 class="role-name"><?= htmlspecialchars((string)$role['role_name']) ?></h3>
												<small class="role-dept">Department: <?= htmlspecialchars((string)$dept) ?></small>
											</div>
										</div>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</article>
				<?php endforeach; ?>
			</section>

			<script>
				// Collapse/expand with ARIA and smooth transitions
				function toggleDropdown(header) {
					const list = header.nextElementSibling;
					const wasCollapsed = header.classList.contains('collapsed');
					// toggle class
					header.classList.toggle('collapsed');
					const nowHidden = !wasCollapsed;
					if (!nowHidden) {
						// expanding: make visible immediately for transition
						list.style.display = '';
						list.setAttribute('aria-hidden', 'false');
					} else {
						// collapsing: set aria-hidden then hide after CSS transition
						list.setAttribute('aria-hidden', 'true');
						setTimeout(function(){ if (list.getAttribute('aria-hidden') === 'true') list.style.display = 'none'; }, 320);
					}
				}

				// Initialize lists as hidden
				document.addEventListener('DOMContentLoaded', function() {
					document.querySelectorAll('.department-section').forEach(function(section){
						const header = section.querySelector('.department-header');
						const list = section.querySelector('.role-list');
						if (header && list) {
							// start collapsed by default
							header.classList.add('collapsed');
							list.setAttribute('aria-hidden', 'true');
						}
					});

					// Search and filter
					const search = document.getElementById('roleSearch');
					const filter = document.getElementById('deptFilter');

					function applyFilters(){
						const q = (search.value || '').toLowerCase().trim();
						const selectedDept = (filter.value || '').toLowerCase();

						document.querySelectorAll('.department-section').forEach(function(section){
							const deptName = (section.querySelector('.department-header .dept-title').textContent || '').toLowerCase();
							const header = section.querySelector('.department-header');
							const list = section.querySelector('.role-list');
							let departmentHasVisibleRoles = false;

							// Hide/show individual role items within this department
							section.querySelectorAll('.role-item').forEach(function(item){
								const roleName = (item.dataset.role || '').toLowerCase();
								const itemDept = (item.dataset.dept || '').toLowerCase();

								const roleMatchesSearch = (q === '' || roleName.includes(q) || itemDept.includes(q));
								const roleMatchesDeptFilter = (selectedDept === '' || itemDept === selectedDept);

								const roleIsVisible = roleMatchesSearch && roleMatchesDeptFilter;
								item.style.display = roleIsVisible ? '' : 'none';

								if (roleIsVisible) {
									departmentHasVisibleRoles = true;
								}
							});

							// Now determine if the entire department section should be visible
							const departmentMatchesFilter = (selectedDept === '' || deptName === selectedDept);
							const departmentVisibleBasedOnRoles = departmentHasVisibleRoles && departmentMatchesFilter;

							if (departmentVisibleBasedOnRoles) {
								section.style.display = ''; // Show the department section
								header.classList.remove('collapsed'); // Expand it if roles are visible
								list.setAttribute('aria-hidden', 'false');
								if (list.style.display === 'none') list.style.display = ''; // Ensure display is not 'none' for transition
							} else {
								section.style.display = 'none'; // Hide the entire department section
								header.classList.add('collapsed'); // Collapse it
								list.setAttribute('aria-hidden', 'true');
								list.style.display = 'none'; // Force hide immediately since the whole section is hidden
							}
						});
					}

					search.addEventListener('input', applyFilters);
					filter.addEventListener('change', applyFilters);
				});
			</script>
		</div>
		<!-- END: Main Content -->
	</main>

	<!-- Footer -->
	<?php include '../footer/footer.php'; ?>
	<?php include '../scrolltop/scrolltop.php'; ?>
	<script src="../scrolltop/scrolltop.js" defer></script>
</body>

</html>
