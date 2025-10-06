<?php
session_start();
include "../mypbra_connect.php";

// Check login
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

// Get online users (active in the last 30 seconds)
$onlineThreshold = date('Y-m-d H:i:s', strtotime('-30 seconds'));
$stmt = $conn->prepare("SELECT id FROM users WHERE last_login > ?");
$stmt->bind_param("s", $onlineThreshold);
$stmt->execute();
$result = $stmt->get_result();

$online_users = [];
while ($row = $result->fetch_assoc()) {
    $online_users[] = (int)$row['id'];
}

echo json_encode([
    "success" => true,
    "online_users" => $online_users
]);
