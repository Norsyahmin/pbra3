<?php
require_once __DIR__ . '/../includes/auth.php';
include '../mypbra_connect.php';

if (!isset($_SESSION['id']) || ($_SESSION['user_type'] !== 'regular')) {
    header('Location: ../login/login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['id'];
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = 'Submitted';
    $file_path = '';

    // Handle file upload
    if (!empty($_FILES['evidence']['name'])) {
        $target_dir = '../uploads/reports/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = basename($_FILES['evidence']['name']);
        $target_file = $target_dir . time() . '_' . $file_name;
        if (move_uploaded_file($_FILES['evidence']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        } else {
            $error = 'Failed to upload evidence file.';
        }
    }

    if (!$error) {
        $stmt = $conn->prepare('INSERT INTO reports (user_id, category, description, evidence_path, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->bind_param('issss', $user_id, $category, $description, $file_path, $status);
        if ($stmt->execute()) {
            $success = 'Report submitted successfully!';
        } else {
            $error = 'Error submitting report.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Report</title>
    <link rel="stylesheet" href="../dashboard_template/style.css">
</head>

<body>
    <?php include '../dashboard_template/navbar/navbar.php'; ?>
    <div class="form-container">
        <h2>Submit a Report</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="">Select Category</option>
                <option value="Technical">Technical</option>
                <option value="Facility">Facility</option>
                <option value="Safety">Safety</option>
                <option value="Other">Other</option>
            </select>
            <br>
            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="4" required></textarea>
            <br>
            <label for="evidence">Attach Evidence (optional):</label>
            <input type="file" name="evidence" id="evidence" accept="image/*,application/pdf">
            <br>
            <button type="submit">Submit Report</button>
        </form>
    </div>
    <?php include '../footer/footer.php'; ?>
</body>

</html>