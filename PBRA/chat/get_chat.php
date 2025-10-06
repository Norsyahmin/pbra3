<?php
session_start();
include "../mypbra_connect.php";

// Check login
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$sender_id = $_SESSION['id'];

// Helper: get user info including profile pic
function getUserInfo($conn, $userId) {
    $stmt = $conn->prepare("SELECT full_name, profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $names = explode(" ", $row['full_name']);
        $initials = "";
        foreach ($names as $n) {
            $initials .= strtoupper($n[0]);
        }
        
        // Set profile pic path or default or null
        $profile_pic = (!empty($row['profile_pic']) && file_exists('../' . $row['profile_pic'])) 
            ? '../' . $row['profile_pic']
            : null;
            
        return [
            'initials' => $initials,
            'full_name' => $row['full_name'],
            'profile_pic' => $profile_pic
        ];
    }
    return [
        'initials' => "?",
        'full_name' => "Unknown User",
        'profile_pic' => '../profile/images/default-profile.jpg'
    ];
}

// Helper: get file info with size and type
function getFileInfo($filePath) {
    $fullPath = '../' . $filePath;
    if (!file_exists($fullPath)) return null;
    
    $fileSize = filesize($fullPath);
    $fileName = basename($filePath);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Format file size
    if ($fileSize < 1024) {
        $formattedSize = $fileSize . " B";
    } elseif ($fileSize < 1048576) {
        $formattedSize = round($fileSize / 1024, 1) . " KB";
    } else {
        $formattedSize = round($fileSize / 1048576, 1) . " MB";
    }
    
    return [
        'name' => $fileName,
        'size' => $formattedSize,
        'ext' => $ext,
        'path' => $filePath
    ];
}

// Determine action
$action = isset($_GET['action']) ? $_GET['action'] : "";

if ($action === "send") {
    if (!isset($_POST['receiver_id'])) {
        echo json_encode(["error" => "Missing parameters"]);
        exit();
    }

    $message = isset($_POST['message']) ? trim($_POST['message']) : "";
    $receiver_id = intval($_POST['receiver_id']);
    $attachments = [];

    // Handle multiple file uploads
    if (!empty($_FILES['attachments']['name'][0])) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['attachments']['name'] as $index => $fileName) {
            $fileTmp = $_FILES['attachments']['tmp_name'][$index];
            // Remove timestamp prefix and use a more private naming approach
            $originalName = $fileName;
            $cleanName = preg_replace('/^\d+_/', '', $originalName); // Remove timestamp prefix if exists
            $uniqueId = uniqid();
            $safeName = $uniqueId . "_" . $cleanName;
            $targetPath = $uploadDir . $safeName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $attachments[] = "uploads/" . $safeName;
            }
        }
    }

    // Store as JSON string in DB
    $attachmentsJson = !empty($attachments) ? json_encode($attachments) : null;

    $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message, attachment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $attachmentsJson);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit();
}

// Add a new action to get unread message counts
if ($action === "unread") {
    $stmt = $conn->prepare("
        SELECT sender_id, COUNT(*) as count 
        FROM chat_messages 
        WHERE receiver_id = ? AND status = 'sent'
        GROUP BY sender_id
    ");
    $stmt->bind_param("i", $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $unread = [];
    while ($row = $result->fetch_assoc()) {
        $unread[$row['sender_id']] = (int)$row['count'];
    }
    
    echo json_encode([
        "success" => true,
        "unread" => $unread
    ]);
    exit();
}

// Update the load action to mark messages as read
if ($action === "load") {
    if (!isset($_GET['receiver_id'])) {
        echo json_encode(["error" => "Missing receiver_id"]);
        exit();
    }

    $receiver_id = intval($_GET['receiver_id']);

    // Mark all messages from this sender as read
    $stmt = $conn->prepare("UPDATE chat_messages SET status = 'read' WHERE sender_id = ? AND receiver_id = ?");
    $stmt->bind_param("ii", $receiver_id, $sender_id);
    $stmt->execute();
    
    $stmt = $conn->prepare("
        SELECT * FROM chat_messages
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    ");
    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $isSender = $row['sender_id'] == $sender_id;
        $userInfo = getUserInfo($conn, $isSender ? $sender_id : $receiver_id);
        
        // Process attachments if any
        $attachments = [];
        if (!empty($row['attachment'])) {
            $attachmentPaths = json_decode($row['attachment'], true);
            foreach ($attachmentPaths as $path) {
                $fileInfo = getFileInfo($path);
                if ($fileInfo) $attachments[] = $fileInfo;
            }
        }
        
        $messages[] = [
            "sender" => $isSender ? "me" : "them",
            "message" => $row['message'],
            "time" => $row['created_at'],
            "initials" => $userInfo['initials'],
            "profile_pic" => $userInfo['profile_pic'],
            "attachments" => $attachments
        ];
    }

    echo json_encode($messages);
    exit();
}

// Invalid action
echo json_encode(["error" => "Invalid action"]);
exit();
