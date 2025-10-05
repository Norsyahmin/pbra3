<?php
session_start();
include '../mypbra_connect.php';

// Get a simple dataset
$query = "SELECT id, category, message, rating FROM feedback LIMIT 5";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CSS Test</title>
  
  <!-- Remove this comment to test with receiver_feedback.css -->
  <!-- <link rel="stylesheet" href="receiver_feedback.css"> -->
  
  <style>
    /* Minimal styling for visibility */
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    .test-with-css { margin-top: 30px; border-top: 2px solid #333; padding-top: 20px; }
  </style>
</head>
<body>
  <h1>CSS Test - Raw Data</h1>
  
  <!-- Display with minimal styling -->
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Category</th>
        <th>Rating</th>
        <th>Message</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['category'] ?></td>
            <td><?= $row['rating'] ?></td>
            <td><?= substr($row['message'], 0, 50) ?>...</td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="4">No data found</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  
  <!-- Instructions -->
  <p>
    <strong>Test Instructions:</strong><br>
    1. If you see data above, the database connection and query are working.<br>
    2. To test with your CSS, uncomment the link tag for receiver_feedback.css in the head section.<br>
    3. If the table disappears after adding the CSS, you've found the issue.
  </p>
</body>
</html>
