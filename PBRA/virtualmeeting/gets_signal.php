<?php
include "../mypbra_connect.php";

$meeting_id = $_GET['meeting_id'];
$user_id    = $_GET['user_id'];

// Return signals that are:
// 1. Not from the current user
// 2. AND either meant for everyone (receiver_id = 0) OR specifically for this user
$sql = "SELECT * FROM signaling 
        WHERE meeting_id = ? 
        AND sender_id != ? 
        AND (receiver_id = 0 OR receiver_id = ?) 
        ORDER BY created_at ASC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $meeting_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$signals = [];
while ($row = $result->fetch_assoc()) {
    $signals[] = $row;
}

echo json_encode($signals);
