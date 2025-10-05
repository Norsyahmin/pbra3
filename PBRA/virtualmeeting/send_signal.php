<?php
include "../mypbra_connect.php";

$meeting_id = $_POST['meeting_id'];
$sender_id  = $_POST['sender_id'];
$type       = $_POST['type'];
$sdp        = $_POST['sdp'] ?? null;
$candidate  = $_POST['candidate'] ?? null;
$receiver_id = $_POST['receiver_id'] ?? 0;  // Get receiver_id from POST or default to 0

$stmt = $conn->prepare("INSERT INTO signaling (meeting_id, sender_id, receiver_id, type, sdp, candidate) 
                        VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiisss", $meeting_id, $sender_id, $receiver_id, $type, $sdp, $candidate);
$stmt->execute();

echo json_encode(["success" => true]);
