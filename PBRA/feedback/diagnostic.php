<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();
include '../mypbra_connect.php';

// Output as plain text
header('Content-Type: text/plain');

echo "=== FEEDBACK SYSTEM DIAGNOSTIC ===\n\n";

// 1. Check database connection
echo "1. DATABASE CONNECTION\n";
if ($conn && !$conn->connect_error) {
    echo "✅ Database connection successful\n";
} else {
    echo "❌ Database connection failed: " . ($conn ? $conn->connect_error : "Connection object not created") . "\n";
    exit;
}

// 2. Check user session and permissions
echo "\n2. USER SESSION\n";
if (isset($_SESSION['id'])) {
    echo "✅ User is logged in (ID: {$_SESSION['id']})\n";
    
    // Check user type
    $stmt = $conn->prepare("SELECT user_type, full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($user_type, $full_name);
    $stmt->fetch();
    $stmt->close();
    
    echo "   User name: $full_name\n";
    echo "   User type: $user_type\n";
    
    if ($user_type == 'admin') {
        echo "✅ User has admin permissions\n";
    } else {
        echo "❌ User does not have admin permissions\n";
    }
} else {
    echo "❌ No user logged in\n";
}

// 3. Check feedback table structure
echo "\n3. FEEDBACK TABLE STRUCTURE\n";
$tableExists = $conn->query("SHOW TABLES LIKE 'feedback'")->num_rows > 0;
if ($tableExists) {
    echo "✅ Feedback table exists\n";
    
    $tableStructure = $conn->query("DESCRIBE feedback");
    echo "   Columns in feedback table:\n";
    $columns = [];
    while ($row = $tableStructure->fetch_assoc()) {
        $columns[] = $row['Field'] . " (" . $row['Type'] . ")";
    }
    echo "   " . implode(", ", $columns) . "\n";
    
    // Check for required columns
    $requiredColumns = ['id', 'user_id', 'category', 'message', 'submitted_at', 'attachment', 'rating'];
    $missingColumns = [];
    
    foreach ($requiredColumns as $col) {
        if (!in_array($col, array_map(function($c) { 
            return explode(" ", $c)[0]; 
        }, $columns))) {
            $missingColumns[] = $col;
        }
    }
    
    if (empty($missingColumns)) {
        echo "✅ All required columns present\n";
    } else {
        echo "❌ Missing required columns: " . implode(", ", $missingColumns) . "\n";
    }
} else {
    echo "❌ Feedback table does not exist\n";
}

// 4. Check feedback data
echo "\n4. FEEDBACK DATA\n";
$count = $conn->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'];
echo "   Total feedback records: $count\n";

if ($count > 0) {
    echo "✅ Feedback data exists\n";
    
    // Get sample data
    echo "\n   Sample feedback entries:\n";
    $result = $conn->query("SELECT id, user_id, category, LEFT(message, 30) as message_preview, 
                            rating, IFNULL(submitted_at, 'NULL') as date 
                            FROM feedback LIMIT 3");
    
    while ($row = $result->fetch_assoc()) {
        echo "   ID: {$row['id']}, User: {$row['user_id']}, Category: {$row['category']}, ";
        echo "Preview: \"{$row['message_preview']}\", Rating: {$row['rating']}, Date: {$row['date']}\n";
    }
    
    // Check JOIN with users table
    echo "\n5. JOIN WITH USERS TABLE\n";
    $joinQuery = "SELECT f.id, f.category, u.full_name 
                  FROM feedback f 
                  LEFT JOIN users u ON f.user_id = u.id 
                  LIMIT 3";
    $joinResult = $conn->query($joinQuery);
    
    if ($joinResult) {
        echo "✅ JOIN query successful\n";
        while ($row = $joinResult->fetch_assoc()) {
            echo "   Feedback #{$row['id']}: Category: {$row['category']}, User: " . 
                 ($row['full_name'] ?? "NULL") . "\n";
        }
    } else {
        echo "❌ JOIN query failed: " . $conn->error . "\n";
    }
} else {
    echo "❌ No feedback data in the table\n";
}

// 6. Check receiver_feedback.php main query
echo "\n6. TESTING MAIN QUERY FROM receiver_feedback.php\n";
// Build simplified version of the main query
$query = "SELECT 
          f.id, f.category, f.message, f.rating, f.attachment, 
          IFNULL(u.full_name, 'Unknown User') as full_name 
          FROM feedback f 
          LEFT JOIN users u ON f.user_id = u.id 
          ORDER BY f.submitted_at DESC 
          LIMIT 5";

$result = $conn->query($query);

if ($result) {
    echo "✅ Main query successful\n";
    echo "   Number of rows returned: " . $result->num_rows . "\n";
    
    if ($result->num_rows > 0) {
        echo "   First few results:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   #{$row['id']}: {$row['category']} by {$row['full_name']}\n";
        }
    } else {
        echo "   Query returned no results\n";
    }
} else {
    echo "❌ Main query failed: " . $conn->error . "\n";
}

echo "\n=== END OF DIAGNOSTIC ===\n";
?>
