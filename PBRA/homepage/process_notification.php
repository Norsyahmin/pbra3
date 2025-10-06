<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response

// Log all requests for debugging
error_log("Notification request: " . $_SERVER['REQUEST_METHOD'] . " " . ($_POST['mark_read'] ?? 'GET'));

require_once __DIR__ . '/../includes/auth.php';
require_once '../mypbra_connect.php';

if (!isset($_SESSION['id'])) {
    error_log("Notification error: User not logged in");
    echo json_encode(['error' => 'User not logged in', 'unreadCount' => 0, 'notifications' => []]);
    exit;
}

$user_id = $_SESSION['id'];
error_log("Processing notifications for user ID: " . $user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    try {
        // Mark notifications read; support both schema variants (status and is_read)
        $update = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND (is_read = 0 OR is_read IS NULL)");
        if ($update) {
            $update->bind_param("i", $user_id);
            $success = $update->execute();
            $update->close();
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['error' => 'Failed to prepare update statement']);
        }
    } catch (Exception $e) {
        error_log("Error marking notifications read: " . $e->getMessage());
        echo json_encode(['error' => 'Database error']);
    }
    exit;
}

try {
    // Get unread count
    $countStmt = $conn->prepare("SELECT COUNT(*) AS unreadCount FROM notifications WHERE user_id = ? AND (is_read = 0 OR is_read IS NULL)");
    if (!$countStmt) {
        throw new Exception("Failed to prepare count statement: " . $conn->error);
    }
    
    $countStmt->bind_param("i", $user_id);
    $countStmt->execute();
    $countResult = $countStmt->get_result()->fetch_assoc();
    $unreadCount = $countResult['unreadCount'] ?? 0;
    $countStmt->close();

    // Get last 10 notifications
    $dataStmt = $conn->prepare("SELECT message, created_at, COALESCE(url, '') AS url FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    if (!$dataStmt) {
        throw new Exception("Failed to prepare data statement: " . $conn->error);
    }
    
    $dataStmt->bind_param("i", $user_id);
    $dataStmt->execute();
    $result = $dataStmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'message' => htmlspecialchars($row['message']),
            'time' => date("M j, H:i", strtotime($row['created_at'])),
            'url' => $row['url']
        ];
    }
    $dataStmt->close();

    echo json_encode([
        'unreadCount' => $unreadCount,
        'notifications' => $notifications
    ]);
    
} catch (Exception $e) {
    error_log("Error in process_notification.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Database error',
        'unreadCount' => 0,
        'notifications' => []
    ]);
}
exit;
