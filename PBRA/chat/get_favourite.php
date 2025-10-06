<?php
session_start();
include "../mypbra_connect.php";

// Check login
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['id'];
$action = isset($_GET['action']) ? $_GET['action'] : "";

if ($action === "toggle") {
    if (!isset($_POST['contact_id'])) {
        echo json_encode(["error" => "Missing contact_id"]);
        exit();
    }

    $contact_id = intval($_POST['contact_id']);
    
    // Check if record exists
    $stmt = $conn->prepare("SELECT id, is_favorite FROM chat_user_contacts WHERE user_id = ? AND contact_id = ?");
    $stmt->bind_param("ii", $user_id, $contact_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Record exists, update is_favorite status
        $row = $result->fetch_assoc();
        $new_status = $row['is_favorite'] ? 0 : 1; // Toggle
        
        $stmt = $conn->prepare("UPDATE chat_user_contacts SET is_favorite = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $row['id']);
        $stmt->execute();
        
        echo json_encode(["success" => true, "is_favorite" => (bool)$new_status]);
    } else {
        // Record doesn't exist, create new with is_favorite = true
        $stmt = $conn->prepare("INSERT INTO chat_user_contacts (user_id, contact_id, is_favorite) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $user_id, $contact_id);
        $stmt->execute();
        
        echo json_encode(["success" => true, "is_favorite" => true]);
    }
    exit();
}

if ($action === "status") {
    if (!isset($_POST['contact_id'])) {
        echo json_encode(["error" => "Missing contact_id"]);
        exit();
    }

    $contact_id = intval($_POST['contact_id']);
    
    $stmt = $conn->prepare("SELECT is_favorite FROM chat_user_contacts WHERE user_id = ? AND contact_id = ?");
    $stmt->bind_param("ii", $user_id, $contact_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $is_favorite = false;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $is_favorite = (bool)$row['is_favorite'];
    }
    
    echo json_encode([
        "success" => true, 
        "is_favorite" => $is_favorite
    ]);
    exit();
}

if ($action === "list") {
    // Get all favorite contacts
    $stmt = $conn->prepare("SELECT contact_id FROM chat_user_contacts WHERE user_id = ? AND is_favorite = 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $favorites = [];
    while ($row = $result->fetch_assoc()) {
        $favorites[] = intval($row['contact_id']);
    }
    
    echo json_encode([
        "success" => true,
        "favorites" => $favorites
    ]);
    exit();
}

// Invalid action
echo json_encode(["error" => "Invalid action"]);
exit();
?>
