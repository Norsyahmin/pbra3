<?php
require_once __DIR__ . '/../includes/auth.php';
include 'mypbra_connect.php'; // No "../" needed if in the same folder

if (!isset($_GET['q'])) {
    echo json_encode(["error" => "No search query provided."]);
    exit();
}

$query = trim($_GET['q']);
$query = "%" . $query . "%";

$sql = "SELECT full_name, profile_pic FROM users WHERE full_name LIKE ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $search_results = [];

    if ($result !== null) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = [
                "full_name" => $row['full_name'],
                "profile_pic" => $row['profile_pic'] ?? "default-profile.jpg" // Use default if no picture
            ];
        }
    }

    $stmt->close();
} else {
    $search_results = [];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($search_results);
exit();
