<?php
session_start();
include '../mypbra_connect.php';

// Remove on-page debug output and do not enable display_errors in production/download contexts.
// If you need debugging, write to the error log instead:
error_reporting(E_ALL);
ini_set('display_errors', '0'); // do not print errors to HTML output
// Example debug logging (optional):
// error_log("Feedback count: " . $conn->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count']);

// Check if user is admin
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit();
}

// Verify admin permissions
$user_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_type);
$stmt->fetch();
$stmt->close();

if ($user_type !== 'super_admin' && $user_type !== 'admin') {
  header("Location: ../homepage/homepage.php");
  exit();
}

// First, check if required columns exist in the feedback table
$tableStructure = $conn->query("DESCRIBE feedback");
$columns = [];
while ($row = $tableStructure->fetch_assoc()) {
  $columns[$row['Field']] = true;
}

// Helper: safely format a datetime-like value; returns '–' when not formatable
function formatDate($value, $format = 'M d, Y') {
  if (empty($value)) return '–';
  // If it's numeric (id fallback), return '–' to avoid nonsense dates
  if (is_numeric($value) && strlen((string)$value) < 11) return '–';
  $ts = strtotime($value);
  if ($ts === false) return '–';
  return date($format, $ts);
}

// Define default SELECT fragments for missing columns
$statusSelect = isset($columns['status']) ? 'f.status' : "IFNULL(NULL, 'new') as status";
$adminNotesSelect = isset($columns['admin_notes']) ? 'f.admin_notes' : "'' as admin_notes";
$assignedToSelect = isset($columns['assigned_to']) ? 'f.assigned_to' : "NULL as assigned_to";
// no direct $timeColumn here; we'll use $timeColumnName and alias as $timeColumnAlias below

// Process status update if submitted
if (isset($_POST['update_status'])) {
  // Check if the status column exists first
  if (isset($columns['status']) && isset($columns['admin_notes'])) {
    $feedback_id = $_POST['feedback_id'];
    $new_status = $_POST['new_status'];
    $admin_notes = $_POST['admin_notes'];
    
    // Get the previous status and user_id to check if it's changing to "resolved"
    $prev_status_query = $conn->prepare("SELECT status, user_id FROM feedback WHERE id = ?");
    $prev_status_query->bind_param("i", $feedback_id);
    $prev_status_query->execute();
    $prev_status_result = $prev_status_query->get_result();
    $prev_status_data = $prev_status_result->fetch_assoc();
    $prev_status = $prev_status_data['status'] ?? 'new';
    $feedback_user_id = $prev_status_data['user_id'];
    $prev_status_query->close();

    $update_stmt = $conn->prepare("UPDATE feedback SET status = ?, admin_notes = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $new_status, $admin_notes, $feedback_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // If the status is being updated to "resolved" and it wasn't already resolved
    if ($new_status === 'resolved' && $prev_status !== 'resolved') {
      // Create notification
      $message = "Your feedback has been marked as resolved. Thank you for your input!";
      $url = "../feedback/feedback.php"; // URL to the user's feedback page
      
      // Insert notification
      $notification_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, url, is_read, created_at) VALUES (?, ?, ?, FALSE, NOW())");
      $notification_stmt->bind_param("iss", $feedback_user_id, $message, $url);
      $notification_stmt->execute();
      $notification_stmt->close();
    }
  } else {
    echo "<script>alert('Status and admin_notes columns don\'t exist yet. Please run the ALTER TABLE command.');</script>";
  }
}

// Process assignment if submitted
if (isset($_POST['assign_to'])) {
  // Check if the assigned_to column exists first
  if (isset($columns['assigned_to'])) {
    $feedback_id = $_POST['feedback_id'];
    $assigned_to = $_POST['assigned_to'];

    $assign_stmt = $conn->prepare("UPDATE feedback SET assigned_to = ? WHERE id = ?");
    $assign_stmt->bind_param("ii", $assigned_to, $feedback_id);
    $assign_stmt->execute();
    $assign_stmt->close();
  } else {
    echo "<script>alert('assigned_to column doesn\'t exist yet. Please run the ALTER TABLE command.');</script>";
  }
}

// Fetch filters
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$rating_filter = isset($_GET['rating']) ? $_GET['rating'] : 'all';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Debug statement
// echo "<!-- Columns in table: " . implode(", ", array_keys($columns)) . " -->";

// Build query based on filters (safer, fully-qualified names, escape inputs)
// Determine a safe time column fallback and use a single alias 'time_column' throughout
$timeColumnName = isset($columns['submitted_at']) ? 'submitted_at' : (isset($columns['created_at']) ? 'created_at' : 'id');
$timeColumnAlias = 'time_column';

$where = "WHERE 1=1";
if ($category_filter !== 'all') {
  $cat = $conn->real_escape_string($category_filter);
  $where .= " AND LOWER(f.category) = LOWER('{$cat}')";
}

if ($status_filter !== 'all' && isset($columns['status'])) {
  $st = $conn->real_escape_string($status_filter);
  $where .= " AND f.status = '{$st}'";
}

if ($rating_filter !== 'all') {
  $rt = (int)$rating_filter;
  $where .= " AND f.rating = {$rt}";
}

if (!empty($search_query)) {
  $s = $conn->real_escape_string($search_query);
  $where .= " AND (f.message LIKE '%{$s}%' OR u.full_name LIKE '%{$s}%')";
}

$select = sprintf(
  "SELECT f.id, f.category, f.message, f.rating, f.attachment, %s, f.%s as %s, %s, %s, IFNULL(u.full_name, 'Unknown User') as full_name FROM feedback f LEFT JOIN users u ON f.user_id = u.id %s ORDER BY f.%s DESC",
  $statusSelect,
  $conn->real_escape_string($timeColumnName),
  $timeColumnAlias,
  $adminNotesSelect,
  $assignedToSelect,
  $where,
  $conn->real_escape_string($timeColumnName)
);

// Run main feedback query into a dedicated variable to avoid collisions with included files
$feedback_result = $conn->query($select);

// Add this debug section (kept as comments so it doesn't break layout)
// echo "<!-- DEBUG OUTPUT START -->";
// if ($result) {
//   echo "<!-- Query succeeded, returned " . $result->num_rows . " rows -->";
//   // Store the first few rows to check
//   $debug_rows = [];
//   $temp_result = $result;
//   while ($row = $temp_result->fetch_assoc()) {
//     $debug_rows[] = $row;
//     if (count($debug_rows) >= 3) break;
//   }
//   echo "<!-- Sample data: " . json_encode($debug_rows) . " -->";
//   // Reset the result pointer
//   $result->data_seek(0);
// } else {
//   echo "<!-- Query failed: " . $conn->error . " -->";
// }
// echo "<!-- DEBUG OUTPUT END -->";

// Get selected feedback details using a prepared statement and COALESCE fallback for timestamp
$selected_feedback = null;
if (isset($_GET['id'])) {
  $feedback_id = (int)$_GET['id'];
  $detail_sql = "SELECT f.*, COALESCE(f.submitted_at, f.created_at, f.id) as timestamp, u.full_name, u.email FROM feedback f LEFT JOIN users u ON f.user_id = u.id WHERE f.id = ?";
  $detail_stmt = $conn->prepare($detail_sql);
  if ($detail_stmt) {
    $detail_stmt->bind_param('i', $feedback_id);
    $detail_stmt->execute();
    $detail_result = $detail_stmt->get_result();
    if ($detail_result) {
      $selected_feedback = $detail_result->fetch_assoc();
    }
    $detail_stmt->close();
  } else {
    // optional: log error
    // error_log('Detail statement prepare failed: ' . $conn->error);
  }
}

// Fetch all admins for assignment
$admins_query = "SELECT id, full_name FROM users WHERE user_type IN ('admin', 'super_admin')";
$admins_result = $conn->query($admins_query);
$admins = [];
if ($admins_result) {
  while ($admin = $admins_result->fetch_assoc()) {
    $admins[] = $admin;
  }
}

// Attempt to fetch user's profile fields for the selected feedback (work_experience, education)
if ($selected_feedback && isset($selected_feedback['user_id'])) {
  $u_id = (int)$selected_feedback['user_id'];
  $prof_stmt = $conn->prepare("SELECT work_experience, education FROM users WHERE id = ? LIMIT 1");
  if ($prof_stmt) {
    $prof_stmt->bind_param('i', $u_id);
    $prof_stmt->execute();
    $prof_res = $prof_stmt->get_result();
    if ($prof_res) {
      $prof_row = $prof_res->fetch_assoc();
      if ($prof_row) {
        // Merge profile fields into selected_feedback for server-side render
        $selected_feedback['work_experience'] = $prof_row['work_experience'] ?? '';
        $selected_feedback['education'] = $prof_row['education'] ?? '';
      }
    }
    $prof_stmt->close();
  }
}

// Calculate analytics
// Calculate analytics using prepared statements where appropriate
$analytics = [
  'total' => 0,
  'avg_rating' => 0,
  'by_category' => []
];

// total
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM feedback");
if ($stmt) {
  $stmt->execute();
  $res = $stmt->get_result();
  $analytics['total'] = (int)($res->fetch_assoc()['count'] ?? 0);
  $stmt->close();
}

// avg rating
$stmt = $conn->prepare("SELECT AVG(rating) as avg FROM feedback WHERE rating IS NOT NULL");
if ($stmt) {
  $stmt->execute();
  $res = $stmt->get_result();
  $analytics['avg_rating'] = (float)($res->fetch_assoc()['avg'] ?? 0);
  $stmt->close();
}

// Analytics by category (prepared)
$categories = ['bug_report', 'feature_request', 'general_feedback', 'other'];
$cat_stmt = $conn->prepare("SELECT COUNT(*) as count FROM feedback WHERE category = ?");
foreach ($categories as $cat) {
  $count = 0;
  if ($cat_stmt) {
    $cat_stmt->bind_param('s', $cat);
    $cat_stmt->execute();
    $cres = $cat_stmt->get_result();
    $count = (int)($cres->fetch_assoc()['count'] ?? 0);
  }
  $analytics['by_category'][$cat] = $count;
}
if ($cat_stmt) $cat_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feedback Dashboard</title>
  <link rel="stylesheet" href="receiver_feedback.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <?php include '../navbar/navbar.php'; ?>
  <div class="dashboard">
    <header>
      <h1>Feedback Dashboard</h1>
      <form method="GET" action="" class="search-form">
        <input type="text" name="search" placeholder="Search..." class="search-bar" value="<?= htmlspecialchars($search_query) ?>">
        <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
      </form>
    </header>
    <!-- Rest of the form and filters -->
    <section class="filters">
      <form method="GET" action="" id="filter-form">
        <select name="category" onchange="this.form.submit()">
          <option value="all" <?= $category_filter === 'all' ? 'selected' : '' ?>>Category: All</option>
          <option value="bug_report" <?= $category_filter === 'bug_report' ? 'selected' : '' ?>>Bug Report</option>
          <option value="feature_request" <?= $category_filter === 'feature_request' ? 'selected' : '' ?>>Feature Request</option>
          <option value="general_feedback" <?= $category_filter === 'general_feedback' ? 'selected' : '' ?>>General Feedback</option>
          <option value="other" <?= $category_filter === 'other' ? 'selected' : '' ?>>Other</option>
        </select>

        <select name="status" onchange="this.form.submit()" <?= !isset($columns['status']) ? 'disabled' : '' ?>>
          <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Status: All</option>
          <option value="new" <?= $status_filter === 'new' ? 'selected' : '' ?>>New</option>
          <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
          <option value="resolved" <?= $status_filter === 'resolved' ? 'selected' : '' ?>>Resolved</option>
        </select>

        <select name="rating" onchange="this.form.submit()">
          <option value="all" <?= $rating_filter === 'all' ? 'selected' : '' ?>>Rating: All</option>
          <option value="1" <?= $rating_filter === '1' ? 'selected' : '' ?>>⭐</option>
          <option value="2" <?= $rating_filter === '2' ? 'selected' : '' ?>>⭐⭐</option>
          <option value="3" <?= $rating_filter === '3' ? 'selected' : '' ?>>⭐⭐⭐</option>
          <option value="4" <?= $rating_filter === '4' ? 'selected' : '' ?>>⭐⭐⭐⭐</option>
          <option value="5" <?= $rating_filter === '5' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐</option>
        </select>

        <!-- If ID was previously selected, maintain it -->
        <?php if (isset($_GET['id'])): ?>
          <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
        <?php endif; ?>
      </form>
    </section>

    <section class="main">
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <!-- Removed ID column -->
              <th>Submitted By</th>
              <th>Category</th>
              <th>Rating</th>
              <th>Feedback Preview</th>
              <th>Attachment</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($feedback_result && $feedback_result->num_rows > 0): ?>
              <?php while ($row = $feedback_result->fetch_assoc()): ?>
                <tr class="feedback-row <?= isset($_GET['id']) && $_GET['id'] == $row['id'] ? 'selected' : '' ?>"
                  data-id="<?= $row['id'] ?>" onclick="viewFeedback(<?= $row['id'] ?>)">
                  <!-- Removed ID cell -->
                  <td><?= htmlspecialchars($row['full_name']) ?></td>
                  <td><?= str_replace('_', ' ', ucfirst($row['category'])) ?></td>
                  <td><?= $row['rating'] ? str_repeat('⭐', $row['rating']) : '–' ?></td>
                  <td><?= htmlspecialchars(mb_substr($row['message'], 0, 30)) . (mb_strlen($row['message']) > 30 ? '...' : '') ?></td>
                  <td>
                    <?php if ($row['attachment']): ?>
                      <button class="attachment-btn" onclick="event.stopPropagation(); window.open('<?= $row['attachment'] ?>', '_blank')">
                        <i class="fas fa-paperclip"></i>
                      </button>
                    <?php else: ?>
                      –
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="status-badge <?= $row['status'] ?? 'new' ?>">
                      <?= ucfirst(str_replace('_', ' ', $row['status'] ?? 'new')) ?>
                    </span>
                  </td>
                  <td><?= isset($row['time_column']) ? date('M d, Y', strtotime($row['time_column'])) : '–' ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="no-results">No feedback found matching your criteria<?= $feedback_result ? '' : ' (SQL Error: ' . $conn->error . ')' ?></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <aside class="details" id="feedback-details">
        <?php if ($selected_feedback): ?>
          <h3>Feedback Details</h3>
          <p><strong>From:</strong> <?= htmlspecialchars($selected_feedback['full_name']) ?> (<?= htmlspecialchars($selected_feedback['email']) ?>)</p>
          <p><strong>Category:</strong> <?= str_replace('_', ' ', ucfirst($selected_feedback['category'])) ?></p>
          <p><strong>Rating:</strong> <?= $selected_feedback['rating'] ? str_repeat('⭐', $selected_feedback['rating']) : 'Not rated' ?></p>
          <p><strong>Submitted:</strong> <?= htmlspecialchars(formatDate($selected_feedback['timestamp'] ?? $selected_feedback['submitted_at'], 'F d, Y \a\t h:i A')) ?></p>

          <div class="feedback-content">
            <h4>Feedback:</h4>
            <p><?= nl2br(htmlspecialchars($selected_feedback['message'])) ?></p>
          </div>

          <?php if (!empty($selected_feedback['work_experience']) || !empty($selected_feedback['education'])): ?>
            <div class="user-profile">
              <h4>User Profile</h4>
              <?php if (!empty($selected_feedback['work_experience'])): ?>
                <p><strong>Work Experience:</strong> <?= nl2br(htmlspecialchars($selected_feedback['work_experience'])) ?></p>
              <?php endif; ?>
              <?php if (!empty($selected_feedback['education'])): ?>
                <p><strong>Education:</strong> <?= nl2br(htmlspecialchars($selected_feedback['education'])) ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if ($selected_feedback['attachment']): ?>
            <p><strong>Attachment:</strong>
              <a href="<?= $selected_feedback['attachment'] ?>" target="_blank" class="attachment-link">
                <i class="fas fa-file-download"></i> Download
              </a>
              <?php if (in_array(pathinfo($selected_feedback['attachment'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <a href="#" onclick="previewImage('<?= $selected_feedback['attachment'] ?>'); return false;" class="preview-link">
                  <i class="fas fa-eye"></i> Preview
                </a>
              <?php endif; ?>
            </p>
          <?php endif; ?>

          <?php if (isset($columns['status']) && isset($columns['admin_notes'])): ?>
            <form method="POST" action="" class="admin-actions">
              <input type="hidden" name="feedback_id" value="<?= $selected_feedback['id'] ?>">

              <label for="admin_notes">Admin Notes:</label>
              <textarea name="admin_notes" id="admin_notes"><?= htmlspecialchars($selected_feedback['admin_notes'] ?? '') ?></textarea>

              <div class="action-row">
                <div class="action-group">
                  <label for="new_status">Status:</label>
                  <select name="new_status" id="new_status">
                    <option value="new" <?= (($selected_feedback['status'] ?? '') === 'new' || !($selected_feedback['status'] ?? false)) ? 'selected' : '' ?>>New</option>
                    <option value="in_progress" <?= ($selected_feedback['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="resolved" <?= ($selected_feedback['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                  </select>
                  <button type="submit" name="update_status" class="status-btn">Update Status</button>
                </div>

                <?php if (isset($columns['assigned_to'])): ?>
                  <div class="action-group">
                    <label for="assigned_to">Assign To:</label>
                    <select name="assigned_to" id="assigned_to">
                      <option value="">-- Select Admin --</option>
                      <?php foreach ($admins as $admin): ?>
                        <option value="<?= $admin['id'] ?>" <?= ($selected_feedback['assigned_to'] ?? '') == $admin['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($admin['full_name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" name="assign_to" class="assign-btn">Assign</button>
                  </div>
                <?php endif; ?>
              </div>
            </form>
          <?php else: ?>
            <div class="missing-columns-note">
              <p>To enable status updates and admin notes, please add the required columns to the database.</p>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <div class="no-selection">
            <i class="fas fa-inbox fa-3x"></i>
            <p>Select feedback from the list to view details</p>
          </div>
        <?php endif; ?>
      </aside>
    </section>

    <section class="analytics">
      <div class="card">
        <h4>Average Rating</h4>
        <p><?= number_format($analytics['avg_rating'], 1) ?> / 5</p>
        <div class="rating-stars">
          <?php
          $fullStars = floor($analytics['avg_rating']);
          $halfStar = ($analytics['avg_rating'] - $fullStars) >= 0.5;

          for ($i = 1; $i <= 5; $i++) {
            if ($i <= $fullStars) {
              echo '<i class="fas fa-star"></i>';
            } else if ($i == $fullStars + 1 && $halfStar) {
              echo '<i class="fas fa-star-half-alt"></i>';
            } else {
              echo '<i class="far fa-star"></i>';
            }
          }
          ?>
        </div>
      </div>

      <div class="card">
        <h4>Feedback by Category</h4>
        <div class="category-stats">
          <?php
          foreach ($analytics['by_category'] as $category => $count) {
            $percentage = $analytics['total'] > 0 ? round(($count / $analytics['total']) * 100) : 0;
            $categoryName = str_replace('_', ' ', ucfirst($category));
            echo "<div class='category-bar'>";
            echo "<span class='category-name'>$categoryName</span>";
            echo "<div class='bar-container'>";
            echo "<div class='bar' style='width: $percentage%'></div>";
            echo "</div>";
            echo "<span class='percentage'>$percentage%</span>";
            echo "</div>";
          }
          ?>
        </div>
      </div>

      <div class="card">
        <h4>Status Overview</h4>
        <div class="status-chart">
          <?php
          $statuses = ['new', 'in_progress', 'resolved'];
          $colors = ['#f39c12', '#3498db', '#2ecc71'];
          $i = 0;

          foreach ($statuses as $status) {
            // Check if status column exists
            $count = 0;
            $statusCheckQuery = "SHOW COLUMNS FROM feedback LIKE 'status'";
            $statusColumnExists = $conn->query($statusCheckQuery)->num_rows > 0;

            if ($statusColumnExists) {
              $count = $conn->query("SELECT COUNT(*) as count FROM feedback WHERE status = '$status' OR (status IS NULL AND '$status' = 'new')")->fetch_assoc()['count'];
            } else if ($status == 'new') {
              // If status column doesn't exist, all feedback is considered "new"
              $count = $analytics['total'];
            }

            $percentage = $analytics['total'] > 0 ? round(($count / $analytics['total']) * 100) : 0;

            echo "<div class='status-item'>";
            echo "<div class='status-color' style='background-color: $colors[$i]'></div>";
            echo "<span class='status-name'>" . str_replace('_', ' ', ucfirst($status)) . "</span>";
            echo "<span class='status-count'>$count</span>";
            echo "</div>";

            $i++;
          }
          ?>
        </div>
      </div>

      <div class="card">
        <h4>Total Feedback</h4>
        <p class="total-feedback"><?= $analytics['total'] ?></p>
        <a href="feedback_export.php" class="export-btn"><i class="fas fa-file-export"></i> Export Data</a>
      </div>
    </section>
  </div>
  <!-- Image Preview Modal -->
  <div id="imageModal" class="modal">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <img id="modalImage" class="modal-content">
  </div>

<?php
// Pre-render admin options HTML for use in client-side JS (JSON-encoded)
$adminOptionsHtml = '';
if (!empty($admins) && is_array($admins)) {
  foreach ($admins as $a) {
    $adminOptionsHtml .= '<option value="' . htmlspecialchars($a['id']) . '">' . htmlspecialchars($a['full_name']) . '</option>';
  }
}
?>
  <script>
    // View feedback details via AJAX and render into the details pane
    function viewFeedback(id) {
      // Highlight selected row
      document.querySelectorAll('.feedback-row').forEach(r => r.classList.remove('selected'));
      const row = document.querySelector(`tr[data-id="${id}"]`);
      if (row) {
        row.classList.add('selected');
        // Scroll to the selected row
        row.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      }

  // If the page was opened with ?debug=1, forward that to the endpoint so we can bypass role checks for testing
  const debugFlag = window.location.search.indexOf('debug=1') !== -1 ? '&debug=1' : '';
  fetch('get_feedback_detail.php?id=' + encodeURIComponent(id) + debugFlag, { credentials: 'same-origin' })
        .then(r => {
          // If HTTP error, read text and throw so catch() receives it
          if (!r.ok) {
            return r.text().then(text => { throw new Error('HTTP ' + r.status + ': ' + text); });
          }
          const ct = r.headers.get('content-type') || '';
          if (ct.indexOf('application/json') === -1) {
            return r.text().then(text => { throw new Error('Non-JSON response: ' + text); });
          }
          return r.json();
        })
        .then(resp => {
          if (!resp || !resp.success) {
            const msg = resp && resp.error ? resp.error : 'Unknown error from server';
            alert('Failed to load feedback details: ' + msg);
            return;
          }

          const d = resp.data;
          const details = document.getElementById('feedback-details');

          // Build stars for rating
          const stars = (() => {
            if (!d.rating) return 'Not rated';
            let out = '';
            for (let i = 1; i <= 5; i++) {
              if (i <= d.rating) out += '<i class="fas fa-star"></i>';
              else out += '<i class="far fa-star"></i>';
            }
            return out;
          })();

          // Build attachment links
          let attachmentHtml = '–';
          if (d.attachment) {
            const ext = (d.attachment.split('.').pop() || '').toLowerCase();
            attachmentHtml = `<a href="${d.attachment}" target="_blank" class="attachment-link"><i class="fas fa-file-download"></i> Download</a>`;
            if (['jpg','jpeg','png','gif'].includes(ext)) {
              attachmentHtml += ` <a href="#" onclick="previewImage('${d.attachment}'); return false;" class="preview-link"><i class="fas fa-eye"></i> Preview</a>`;
            }
          }

          // Render HTML into details pane
          details.innerHTML = `
            <h3>Feedback Details</h3>
            <p><strong>From:</strong> ${escapeHtml(d.full_name)} ${d.email ? '('+escapeHtml(d.email)+')' : ''}</p>
            <p><strong>Category:</strong> ${escapeHtml(d.category ? d.category.replace(/_/g,' ') : '')}</p>
            <p><strong>Rating:</strong> <span class="rating-stars">${stars}</span></p>
            <p><strong>Submitted:</strong> ${escapeHtml(formatDateClient(d.timestamp))}</p>
            <div class="feedback-content"><h4>Feedback:</h4><p>${nl2br(escapeHtml(d.message))}</p></div>
            <p><strong>Attachment:</strong> ${attachmentHtml}</p>
            ${d.work_experience ? `<div class="user-profile"><h4>User Profile</h4><p><strong>Work Experience:</strong> ${nl2br(escapeHtml(d.work_experience))}</p>${d.education ? `<p><strong>Education:</strong> ${nl2br(escapeHtml(d.education))}</p>` : ''}</div>` : (d.education ? `<div class="user-profile"><h4>User Profile</h4><p><strong>Education:</strong> ${nl2br(escapeHtml(d.education))}</p></div>` : '')}
          `;

          // If admin fields are present, append admin action form
          if (typeof d.admin_notes !== 'undefined') {
            // Use server-side pre-rendered admin options (JSON-encoded into a JS string)
            const adminOptions = <?= json_encode($adminOptionsHtml) ?>;
            // Append admin form (non-submitting; will submit via normal POST when used)
            const adminForm = document.createElement('div');
            adminForm.innerHTML = `<form method="POST" action="" class="admin-actions">` +
              `<input type="hidden" name="feedback_id" value="${d.id}">` +
              `<label for="admin_notes">Admin Notes:</label>` +
              `<textarea name="admin_notes" id="admin_notes">${escapeHtml(d.admin_notes || '')}</textarea>` +
              `<div class="action-row"><div class="action-group">` +
              `<label for="new_status">Status:</label>` +
              `<select name="new_status" id="new_status">` +
                `<option value="new" ${d.status === 'new' ? 'selected' : ''}>New</option>` +
                `<option value="in_progress" ${d.status === 'in_progress' ? 'selected' : ''}>In Progress</option>` +
                `<option value="resolved" ${d.status === 'resolved' ? 'selected' : ''}>Resolved</option>` +
              `</select>` +
              `<button type="submit" name="update_status" class="status-btn">Update Status</button>` +
              `</div>` +
              `${d.assigned_to !== null ? '<div class="action-group"><label for="assigned_to">Assign To:</label><select name="assigned_to" id="assigned_to"><option value="">-- Select Admin --</option>' + adminOptions + '</select><button type="submit" name="assign_to" class="assign-btn">Assign</button></div>' : ''}` +
              `</div></form>`;
            details.appendChild(adminForm);
          }
        })
        .catch(err => {
          console.error(err);
          alert('Error loading details: ' + (err && err.message ? err.message : String(err)));
        });
    }

    // Utility: escape for HTML
    function escapeHtml(str) {
      if (!str && str !== 0) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    // Utility: convert newlines to <br>
    function nl2br(str) {
      return str.replace(/\n/g, '<br>');
    }

    // Format timestamp (server returns raw timestamp string)
    function formatDateClient(v) {
      if (!v) return '–';
      const t = Date.parse(v);
      if (isNaN(t)) return '–';
      const d = new Date(t);
      return d.toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit' });
    }

    // Preview image attachment
    function previewImage(src) {
      const modal = document.getElementById('imageModal');
      const modalImg = document.getElementById('modalImage');
      modal.style.display = "block";
      modalImg.src = src;
    }

    // Close modal
    function closeModal() {
      document.getElementById('imageModal').style.display = "none";
    }

    // Highlight selected row
    document.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const id = urlParams.get('id');

      if (id) {
        // Automatically fetch and display details if ID is in URL
        viewFeedback(id);
      }
    });
  </script>

<?php
// Pre-render admin options HTML for use in client-side JS (JSON-encoded)
$adminOptionsHtml = '';
if (!empty($admins) && is_array($admins)) {
  foreach ($admins as $a) {
    $adminOptionsHtml .= '<option value="' . htmlspecialchars($a['id']) . '">' . htmlspecialchars($a['full_name']) . '</option>';
  }
}
?>

  <style>
    /* Added styles */
    .alert-box {
      background-color: #fff3cd;
      border: 1px solid #ffeeba;
      color: #856404;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
    }

    .alert-box code {
      display: block;
      background: #f8f9fa;
      padding: 10px;
      margin-top: 10px;
      border-radius: 4px;
      white-space: pre-wrap;
      font-family: monospace;
    }

    .missing-columns-note {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
      padding: 15px;
      margin-top: 20px;
      border-radius: 4px;
    }

    /* Enhanced styles for table and details panel alignment */
    .main {
      display: grid;
      /* widen the details column so the panel has more room */
      grid-template-columns: 1fr 480px;
      gap: 20px;
      align-items: start;
    }

    /* Keep the table scrollable but use the viewport height so it fits nicel y
       and doesn't force the details panel to be small. */
    .table-container {
      height: calc(100vh - 220px);
      overflow-y: auto;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      background: white;
    }

    /* Make the details panel expand naturally (no internal scroll). If the
       content is taller than the viewport the whole page will scroll instead
       of showing a nested scrollbar. Keep it sticky so it remains visible. */
    .details {
      height: auto;
      overflow: visible;
      position: sticky;
      top: 20px;
    }

    /* Consistent scrollbar styling only for the table container (details now
       won't show an internal scrollbar). */
    .table-container::-webkit-scrollbar {
      width: 8px;
    }

    .table-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }

    .table-container::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 4px;
      border: 2px solid #f1f1f1;
    }

    .table-container::-webkit-scrollbar-thumb:hover {
      background: #a1a1a1;
    }

    /* Responsive: stack columns on narrow viewports */
    @media (max-width: 900px) {
      .main {
        grid-template-columns: 1fr;
      }
      .details {
        position: relative;
        top: auto;
      }
      .table-container {
        height: auto;
      }
    }
  </style>
  <?php include '../scrolltop/scrolltop.php'; ?>
  <?php include '../footer/footer.php'; ?>
  <script src="../scrolltop/scrolltop.js"></script>
</body>

</html>
