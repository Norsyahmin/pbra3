<?php

require_once __DIR__ . '/../mypbra_connect.php';
require_once __DIR__ . '/../includes/auth.php';

// Authorize: only logged-in users with allowed roles may access create page
$user_id = $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? null;
$allowed_roles = ['regular', 'admin', 'super_admin'];
if ($user_id === null || !in_array($user_role, $allowed_roles, true)) {
    header('Location: /login/login.php');
    exit;
}

$users = [];
$res = $conn->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
if ($res instanceof mysqli_result) {
    while ($r = $res->fetch_assoc()) {
        $users[] = $r;
    }
}

// Get existing tasks for dependencies
$existing_tasks = [];
$task_res = $conn->query("SELECT id, title, status FROM tasks WHERE status NOT IN ('completed', 'archived') ORDER BY title ASC");
if ($task_res instanceof mysqli_result) {
    while ($t = $task_res->fetch_assoc()) {
        $existing_tasks[] = $t;
    }
}

// Handle template pre-fill
$template_data = [];
if (isset($_GET['template'])) {
    $template_id = intval($_GET['template']);
    $tres = $conn->query("SELECT template_data FROM task_templates WHERE id = " . $template_id);
    if ($tres instanceof mysqli_result) {
        $templateRow = $tres->fetch_assoc();
        if ($templateRow && !empty($templateRow['template_data'])) {
            $template_data = json_decode($templateRow['template_data'], true) ?: [];
        }
    }
}

// Pre-fill from URL parameters (from templates)
$prefill = [
    'title' => $_GET['title'] ?? $template_data['title'] ?? '',
    'description' => $_GET['description'] ?? $template_data['description'] ?? '',
    'priority' => $_GET['priority'] ?? $template_data['priority'] ?? 'medium'
];

?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Create task</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/task_management/create_task.css">
    <!-- reuse registration form styles for consistent UI -->
    <link rel="stylesheet" href="/registration/registration.css">
    <!-- page title styling (same as registration page) -->
    <link rel="stylesheet" href="../page_title.css">
    <!-- Select2 for nicer multi-select UI (matches report.php) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <style>
        /* Select2 custom styling (copied from report.css) to match the report UI */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #D1D1D1 !important;
            border-radius: 4px !important;
            padding: 6px !important;
            min-height: 40px !important;
            max-height: 90px !important;
            overflow-y: auto !important;
            background-color: white;
            font-size: 0.95rem;
            box-sizing: border-box;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 0 !important;
        }
        .select2-container--default .select2-search--inline .select2-search__field {
            height: 28px !important;
            padding: 4px 6px !important;
            margin: 2px 0 !important;
            font-size: 0.9rem;
        }
        .select2-selection__choice {
            background-color: #174080 !important;
            border-color: #174080 !important;
            color: white !important;
            font-size: 0.85rem !important;
            padding: 4px 10px 4px 26px !important;
            margin: 3px 4px !important;
            border-radius: 4px !important;
            position: relative;
            display: inline-block;
            white-space: nowrap;
        }
        .select2-selection__choice__remove {
            position: absolute;
            left: 6px;
            transform: translateY(-50%);
            color: #ffffff !important;
            font-weight: bold;
            font-size: 1rem !important;
            cursor: pointer;
            transform: translateY(5px);
        }
        .select2-container--default .select2-selection--multiple .select2-search--inline {
            width: 100% !important;
            order: 1 !important;
        }

        .select2-selection--multiple {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            gap: 4px !important;
            min-height: 40px !important;
            overflow-y: auto !important;
        }

        /* Lower spacing after field */
        .report-form .select2-container {
            margin-bottom: 20px;
        }
        </style>
</head>

<body>

    <?php
    // Include the main navbar component (outputs topbar/sidebar HTML + needed CSS/JS link)
    include __DIR__ . '/../navbar/navbar.php';
    ?>

    <!-- Main content area expected by navbar.js / navbar.css -->
    <div id="content" class="content">
        <div class="content-body">
            <div class="container">
                <div class="page-title">
                    <h1 style="font-size:30px;">CREATE TASK</h1>
                </div>

                <?php if (isset($_SESSION['flash'])):
                    $f = $_SESSION['flash'];
                    unset($_SESSION['flash']); ?>
                    <div class="flash <?php echo ($f['type'] === 'success') ? 'flash-success' : 'flash-error'; ?>">
                        <?php echo htmlspecialchars($f['message']); ?>
                        <?php if (!empty($f['assigned']) && is_array($f['assigned'])): ?>
                            <div style="margin-top:8px">Assigned to:
                                <ul>
                                    <?php foreach ($f['assigned'] as $an): ?>
                                        <li><?php echo htmlspecialchars($an); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="card">

                    <!-- Use registration-style form structure -->
                    <form method="post" action="process_task.php" enctype="multipart/form-data" class="form">
                        <input type="hidden" name="action" value="create">
                        <div class="field">
                            <label>Title:</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($prefill['title']); ?>" required placeholder="Enter task title">
                        </div>

                        <div class="field">
                            <label>Task Type:</label>
                            <select id="task_type" name="task_type" onchange="toggleCustomTask()">
                                <option value="" disabled selected>Select Task Type</option>
                                <optgroup label="📚 Class-Related Tasks">
                                    <option value="Lesson Planning">Lesson Planning</option>
                                    <option value="Teaching a Class">Teaching a Class</option>
                                    <option value="Substituting a Teacher">Substituting a Teacher</option>
                                    <option value="Student Attendance Checking">Student Attendance Checking</option>
                                    <option value="Student Counseling">Student Counseling</option>
                                </optgroup>
                                <optgroup label="📝 Exam-Related Tasks">
                                    <option value="Exam Question Preparation">Exam Question Preparation</option>
                                    <option value="Exam Supervision">Exam Supervision</option>
                                    <option value="Paper Marking">Paper Marking</option>
                                    <option value="Grade Submission">Grade Submission</option>
                                </optgroup>
                                <optgroup label="📅 Meeting & Administration">
                                    <option value="Faculty Meeting">Faculty Meeting</option>
                                    <option value="Department Meeting">Department Meeting</option>
                                    <option value="Administrative Paperwork">Administrative Paperwork</option>
                                    <option value="Performance Review">Performance Review</option>
                                </optgroup>
                                <optgroup label="📖 Student Activities & Events">
                                    <option value="Student Mentoring">Student Mentoring</option>
                                    <option value="Club or Society Management">Club or Society Management</option>
                                    <option value="School Event Coordination">School Event Coordination</option>
                                    <option value="Parent-Teacher Meeting">Parent-Teacher Meeting</option>
                                </optgroup>
                                <optgroup label="🔬 Research & Development">
                                    <option value="Research Paper Review">Research Paper Review</option>
                                    <option value="Syllabus Development">Syllabus Development</option>
                                    <option value="Course Material Preparation">Course Material Preparation</option>
                                </optgroup>
                                <optgroup label="🏢 Administrative Tasks">
                                    <option value="Budget Planning">Budget Planning</option>
                                    <option value="Resource Management">Resource Management</option>
                                    <option value="Staff Training">Staff Training</option>
                                    <option value="Quality Assurance">Quality Assurance</option>
                                </optgroup>
                                <optgroup label="🎯 Special Projects">
                                    <option value="Curriculum Development">Curriculum Development</option>
                                    <option value="Technology Integration">Technology Integration</option>
                                    <option value="Community Outreach">Community Outreach</option>
                                    <option value="Assessment Design">Assessment Design</option>
                                </optgroup>
                                <option value="other">📝 Other (Specify Below)</option>
                            </select>
                            <input type="text" id="custom_task_type" name="custom_task_type" placeholder="Enter custom task type" style="display: none; margin-top: 10px;" />
                        </div>

                        <div>
                            <label>Description
                                <textarea name="description" rows="6" placeholder="Enter additional details about the task..."><?php echo htmlspecialchars($prefill['description']); ?></textarea>
                            </label>
                        </div>
                        <div class="form-row two-col">
                            <div class="form-group"><label>Start date<br><input type="date" name="start_date"></label></div>
                            <div class="form-group"><label>End date<br><input type="date" name="end_date"></label></div>
                        </div>

                        <div class="field">
                            <label>Priority:</label>
                            <select name="priority">
                                <option value="low" <?php echo $prefill['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo $prefill['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo $prefill['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Assign to (multiple allowed)</label>
                            <select id="assignees" name="assignees[]" multiple class="select2" style="width:100%">
                                <?php foreach ($users as $u): ?>
                                    <option value="<?php echo intval($u['id']); ?>"><?php echo htmlspecialchars($u['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Task Dependencies (optional)</label>
                            <select name="dependencies[]" multiple style="height:120px;width:100%">
                                <option value="">-- Select tasks this depends on --</option>
                                <?php foreach ($existing_tasks as $task): ?>
                                    <option value="<?php echo intval($task['id']); ?>">
                                        #<?php echo intval($task['id']); ?> - <?php echo htmlspecialchars($task['title']); ?>
                                        (<?php echo htmlspecialchars($task['status']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>This task will be blocked until the selected tasks are completed</small>
                        </div>

                        <div class="field">
                            <label><input type="checkbox" name="send_email_notification" value="1" checked> Send email notifications to assignees</label>
                        </div>

                        <div class="field" style="margin-top:6px;">
                            <label>Attach files (optional)</label>
                            <div class="attach-files" tabindex="0">
                                <i class="fa fa-paperclip" aria-hidden="true"></i>
                                <p>Click or drop files here to attach</p>
                                <input type="file" name="attachments[]" multiple>
                                <div id="file-info"></div>
                            </div>
                            <small>Allowed: common documents and images. Max total size per file will be enforced by the server.</small>
                        </div>

                        <div class="form-actions-right">
                            <button type="button" class="cancel-btn" onclick="window.location.href='/task_management/task_management.php'">Cancel</button>
                            <button class="confirm-btn" type="submit">Create task</button>
                        </div>
                    </form>

                    <script>
                        // Toggle custom task type input
                        function toggleCustomTask() {
                            const taskTypeDropdown = document.getElementById("task_type");
                            const customTaskInput = document.getElementById("custom_task_type");
                            if (!taskTypeDropdown || !customTaskInput) return;
                            if (taskTypeDropdown.value === "other") {
                                customTaskInput.style.display = "block";
                                customTaskInput.setAttribute("required", "true");
                                // remove highlight so it matches other inputs
                                customTaskInput.classList.add('no-highlight');
                            } else {
                                customTaskInput.style.display = "none";
                                customTaskInput.removeAttribute("required");
                                customTaskInput.classList.remove('no-highlight');
                            }
                        }

                        // Ensure input state is correct on page load (in case of prefill)
                        document.addEventListener('DOMContentLoaded', function() {
                            try {
                                toggleCustomTask();
                            } catch (e) {}

                            // Initialize Select2 for the assignees multi-select (match report.php behaviour)
+
                            try {
                                if (window.jQuery && jQuery().select2) {
                                    $('#assignees').select2({
                                        placeholder: 'Select one or more people',
                                        width: '100%'
                                    });
                                }
                            } catch (e) {
                                // fail silently if Select2 isn't available
                            }
                        });

                        // Small UX: show selected file names inside #file-info and allow clicking the attach box
                        (function() {
                            const attachBox = document.querySelector('.attach-files');
                            if (!attachBox) return;
                            const input = attachBox.querySelector('input[type=file]');
                            const info = document.getElementById('file-info');

                            attachBox.addEventListener('click', function(e) {
                                if (e.target.tagName !== 'INPUT') input.click();
                            });

                            attachBox.addEventListener('dragover', function(e) {
                                e.preventDefault();
                                attachBox.classList.add('drag-over');
                            });
                            attachBox.addEventListener('dragleave', function() {
                                attachBox.classList.remove('drag-over');
                            });
                            attachBox.addEventListener('drop', function(e) {
                                e.preventDefault();
                                attachBox.classList.remove('drag-over');
                                const dt = e.dataTransfer;
                                if (dt && dt.files && dt.files.length) {
                                    // populate input.files is non-trivial; show preview and assign via DataTransfer if supported
                                    if (window.DataTransfer) {
                                        const d = new DataTransfer();
                                        Array.from(dt.files).forEach(f => d.items.add(f));
                                        input.files = d.files;
                                    }
                                    showFiles(input.files);
                                }
                            });

                            input.addEventListener('change', function() {
                                showFiles(this.files);
                            });

                            function showFiles(files) {
                                if (!info) return;
                                info.innerHTML = '';
                                if (!files || !files.length) return;
                                const ul = document.createElement('ul');
                                Array.from(files).forEach(f => {
                                    const li = document.createElement('p');
                                    li.textContent = f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
                                    ul.appendChild(li);
                                });
                                info.appendChild(ul);
                            }
                        })();
                    </script>

                </div> <!-- /.card -->
            </div> <!-- /.container -->
        </div> <!-- /.content-body -->
    </div> <!-- /#content -->

    <?php
    // Add scroll-to-top component and footer
    include __DIR__ . '/../scrolltop/scrolltop.php';
    include __DIR__ . '/../footer/footer.php';
    ?>

    <!-- Ensure scrolltop logic is loaded (relative to this page) -->
    <script src="../scrolltop/scrolltop.js"></script>

        <script>
        // Robust Select2 init placed at the bottom to avoid conflicts with other includes
        (function() {
            function initAssigneesSelect() {
                try {
                    if (window.jQuery && jQuery().select2) {
                        var $ = jQuery;
                        var $sel = $('#assignees');
                        if (!$sel.length) return;
                        // destroy previous instance if present
                        if ($sel.data('select2')) {
                            try { $sel.select2('destroy'); } catch (e) {}
                        }
                        $sel.select2({
                            placeholder: 'Select one or more people',
                            width: '100%',
                            closeOnSelect: false,
                            dropdownAutoWidth: true,
                            // keep search input inline so it appears after tags
                            tags: false
                        });
                    }
                } catch (e) {
                    // silent
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAssigneesSelect);
            } else {
                initAssigneesSelect();
            }
        })();
        </script>

</body>

</html>