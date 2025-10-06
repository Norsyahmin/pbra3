<?php
if (session_status() === PHP_SESSION_NONE) {
  require_once __DIR__ . '/auth.php';
}

// Include database connection with error handling
try {
  include '../mypbra_connect.php';
  if (!isset($conn) || $conn->connect_error) {
    throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Unknown error"));
  }
} catch (Exception $e) {
  error_log("Announcement page database error: " . $e->getMessage());
  die("Database connection error. Please try again later.");
}

$is_admin = false;
if (isset($_SESSION['id'])) {
  $user_id = $_SESSION['id'];
  $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($user_type);
  if ($stmt->fetch() && ($user_type === 'admin' || $user_type === 'super_admin')) {
    $is_admin = true;
  }
  $stmt->close();
}

// Handle announcement upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin && isset($_POST['submit_announcement'])) {
  $title = $_POST['title'] ?? '';
  $content = $_POST['content'] ?? '';
  $imagePath = null;

  if (!empty($_FILES['image']['name'])) {
    $uploadDir = '../uploads/announcements/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    $imageTmp = $_FILES['image']['tmp_name'];
    $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
    $fullPath = $uploadDir . $imageName;
    move_uploaded_file($imageTmp, $fullPath);
    $imagePath = 'uploads/announcements/' . $imageName;
  }

  if (!empty($title) && !empty($content)) {
    $stmt = $conn->prepare("INSERT INTO announcement (title, content, image_path, created_at) VALUES (?, ?, ?, NOW())");
    if ($stmt) {
      $stmt->bind_param("sss", $title, $content, $imagePath);
      $success = $stmt->execute();
      $stmt->close();
      
      if ($success) {
        // Use JavaScript redirect instead of header()
        echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
      } else {
        error_log("Failed to insert announcement: " . $conn->error);
      }
    } else {
      error_log("Failed to prepare announcement insert statement: " . $conn->error);
    }
  }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin && isset($_POST['delete_id'])) {
  $idToDelete = $_POST['delete_id'];

  // First, get the image path before deleting the record
  $stmt = $conn->prepare("SELECT image_path FROM announcement WHERE id = ?");
  if ($stmt) {
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();

    // Delete the physical image file if it exists
    if (!empty($imagePath)) {
      $fullImagePath = '../' . $imagePath;
      if (file_exists($fullImagePath)) {
        if (!unlink($fullImagePath)) {
          // Log error but continue with database deletion
          error_log("Failed to delete image file: " . $fullImagePath);
        }
      }
    }

    // Then delete the database record
    $stmt = $conn->prepare("DELETE FROM announcement WHERE id = ?");
    if ($stmt) {
      $stmt->bind_param("i", $idToDelete);
      $success = $stmt->execute();
      $stmt->close();
      
      if ($success) {
        // Use JavaScript redirect instead of header()
        echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
      } else {
        error_log("Failed to delete announcement: " . $conn->error);
      }
    } else {
      error_log("Failed to prepare delete statement: " . $conn->error);
    }
  } else {
    error_log("Failed to prepare select statement for image path: " . $conn->error);
  }
}

$announcements = [];
$result = $conn->query("SELECT * FROM announcement ORDER BY created_at DESC LIMIT 10");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
  }
} else {
  error_log("Failed to fetch announcements: " . $conn->error);
}
?>

<link rel="stylesheet" href="../includes/announcement.css">
<!-- Debug script - remove in production -->
<script src="../includes/announcement_debug.js"></script>

<div class="announcement-carousel">
  <div class="announcement-header">
    <h2>📢 Latest Announcements</h2>
    <?php if ($is_admin): ?>
      <button class="add-announcement-toggle" onclick="openModal()">+ Add Announcement</button>
    <?php endif; ?>
  </div>

  <div class="carousel-wrapper">
    <button class="carousel-btn left" onclick="moveSlide(-1)"></button>
    <div class="carousel-track">
      <?php if (empty($announcements)): ?>
        <div class="announcement-slide">
          <h3>No Announcements Available</h3>
          <div class="desc">There are currently no announcements to display.</div>
        </div>
      <?php else: ?>
        <?php foreach ($announcements as $a): ?>
          <div class="announcement-slide">
            <h3><?= htmlspecialchars($a['title']) ?></h3>
            <?php if (!empty($a['image_path'])): ?>
              <?php 
              // Ensure proper path resolution
              $imagePath = $a['image_path'];
              // Remove leading '../' if present to avoid double path issues
              $imagePath = ltrim($imagePath, './');
              // Add the correct relative path from the current location
              $fullImagePath = '../' . $imagePath;
              ?>
              <img src="<?= htmlspecialchars($fullImagePath) ?>" alt="announcement image" onerror="this.style.display='none'">
            <?php endif; ?>
            <div class="desc"><?= html_entity_decode($a['content']) ?></div>
            <?php if ($is_admin): ?>
              <form method="POST" class="delete-form" data-id="<?= $a['id'] ?>">
                <button type="button" class="delete-btn" onclick="openDeleteModal(this)">🗑 Delete</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <button class="carousel-btn right" onclick="moveSlide(1)"></button>
  </div>
  <div class="carousel-dots">
    <?php if (!empty($announcements)): ?>
      <?php foreach ($announcements as $index => $a): ?>
        <span class="dot <?= $index === 0 ? 'active' : '' ?>" onclick="jumpToSlide(<?= $index ?>)"></span>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display: none;">
  <div class="delete-modal-content">
    <span class="close-btn" onclick="closeDeleteModal()">&times;</span>
    <h3>Are you sure you want to delete this announcement?</h3>
    <form method="POST" id="confirmDeleteForm">
      <input type="hidden" name="delete_id" id="delete_id">
      <div class="modal-button-group">
        <button type="submit" class="confirm-delete-btn">Yes, Delete</button>
        <button type="button" class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Announcement Modal -->
<?php if ($is_admin): ?>
  <div id="announcementModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal()">&times;</span>
      <h3>Add New Announcement</h3>
      <form method="POST" enctype="multipart/form-data" class="announcement-form" onsubmit="prepareContent()">
        <input type="text" name="title" placeholder="Title" required><br>

        <!-- Formatting toolbar -->
        <div style="margin-bottom: 10px; text-align: center;">
          <button type="button" onclick="formatText('bold')"><b>B</b></button>
          <button type="button" onclick="formatText('italic')"><i>I</i></button>
          <button type="button" onclick="formatText('underline')"><u>U</u></button>
        </div>

        <!-- Rich text editor -->
        <div id="richContent" contenteditable="true" class="rich-text-box"
          style="border: 1px solid #ccc; padding: 12px; min-height: 100px; border-radius: 6px;">
        </div>

        <!-- Hidden field to store HTML -->
        <input type="hidden" name="content" id="hiddenContent" />

        <input type="file" name="image" accept="image/*"><br>
        <button type="submit" name="submit_announcement">Post</button>
      </form>
    </div>
  </div>
<?php endif; ?>


<script>
  let announcementCurrentIndex = 0;

  function moveSlide(dir) {
    const slides = document.querySelectorAll('.announcement-slide');
    if (slides.length === 0) return;
    
    announcementCurrentIndex += dir;
    if (announcementCurrentIndex < 0) announcementCurrentIndex = slides.length - 1;
    if (announcementCurrentIndex >= slides.length) announcementCurrentIndex = 0;
    
    const track = document.querySelector('.carousel-track');
    if (track) {
      track.style.transform = `translateX(-${announcementCurrentIndex * 100}%)`;
      updateAnnouncementDots();
    }
  }

  function openModal() {
    const modal = document.getElementById('announcementModal');
    if (modal) {
      modal.style.display = 'block';
    }
  }

  function closeModal() {
    const modal = document.getElementById('announcementModal');
    if (modal) {
      modal.style.display = 'none';
    }
  }

  function openDeleteModal(btn) {
    const form = btn.closest('.delete-form');
    const id = form.getAttribute('data-id');
    const deleteIdInput = document.getElementById('delete_id');
    const deleteModal = document.getElementById('deleteModal');
    
    if (deleteIdInput && deleteModal) {
      deleteIdInput.value = id;
      deleteModal.style.display = 'flex';
    }
  }

  function closeDeleteModal() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
      deleteModal.style.display = 'none';
    }
  }

  window.onclick = function(event) {
    const delModal = document.getElementById('deleteModal');
    const addModal = document.getElementById('announcementModal');
    if (delModal && event.target === delModal) closeDeleteModal();
    if (addModal && event.target === addModal) closeModal();
  }

  function formatText(command) {
    document.execCommand(command, false, null);
  }

  function prepareContent() {
    const richContent = document.getElementById('richContent');
    const hiddenContent = document.getElementById('hiddenContent');
    if (richContent && hiddenContent) {
      hiddenContent.value = richContent.innerHTML;
    }
  }

  function updateAnnouncementDots() {
    const dots = document.querySelectorAll('.carousel-dots .dot');
    dots.forEach((dot, i) => {
      dot.classList.toggle('active', i === announcementCurrentIndex);
    });
  }

  function jumpToSlide(index) {
    announcementCurrentIndex = index;
    const track = document.querySelector('.carousel-track');
    if (track) {
      track.style.transform = `translateX(-${announcementCurrentIndex * 100}%)`;
      updateAnnouncementDots();
    }
  }

  // Initialize carousel on page load
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing announcement carousel...');
    
    // Check if we have announcements
    const slides = document.querySelectorAll('.announcement-slide');
    console.log('Found', slides.length, 'announcement slides');
    
    if (slides.length > 0) {
      updateAnnouncementDots();
      
      // Set up auto-rotation if there are multiple slides
      if (slides.length > 1) {
        setInterval(function() {
          moveSlide(1);
        }, 5000); // Auto-advance every 5 seconds
      }
    }
    
    // Test image loading
    const images = document.querySelectorAll('.announcement-slide img');
    images.forEach((img, index) => {
      if (img.src) {
        const testImg = new Image();
        testImg.onload = function() {
          console.log(`Announcement image ${index + 1} loaded successfully`);
        };
        testImg.onerror = function() {
          console.error(`Announcement image ${index + 1} failed to load:`, img.src);
          img.style.display = 'none';
        };
        testImg.src = img.src;
      }
    });
  });
</script>