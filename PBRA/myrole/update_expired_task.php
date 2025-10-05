<?php
if (!isset($conn)) {
    include '../mypbra_connect.php';
}

date_default_timezone_set('Asia/Brunei');
$currentDateTime = date('Y-m-d H:i:s');

// Helper: check if a column exists in the current database for a table
function columnExists($conn, $table, $column)
{
    $db = '';
    $res = $conn->query('SELECT DATABASE()');
    if ($res) {
        $db = $res->fetch_row()[0];
        $res->free();
    }

    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $cnt = 0;
    $stmt->bind_result($cnt);
    $stmt->fetch();
    $stmt->close();
    return ($cnt > 0);
}

// Determine which date column to use (prefer task_date, then due_date, then created_at)
$dateColumn = null;
foreach (['task_date', 'due_date', 'created_at'] as $col) {
    if (columnExists($conn, 'tasks', $col)) {
        $dateColumn = $col;
        break;
    }
}

if (!$dateColumn) {
    // Nothing to compare against — bail out quietly
    return;
}

// Check if there's a time column to allow datetime comparisons
$hasTime = columnExists($conn, 'tasks', 'task_time');

// Prepare parameter depending on whether we have time data
$paramValue = $hasTime ? $currentDateTime : date('Y-m-d');

// Build comparison expression
if ($hasTime) {
    $compareExpr = "CONCAT(`" . $dateColumn . "`, ' ', `task_time`)";
} else {
    // Compare date-only
    $compareExpr = "`" . $dateColumn . "`";
}

// Check how many pending tasks are overdue
$sql_check = "SELECT COUNT(*) FROM `tasks` WHERE `status` = 'pending' AND " . $compareExpr . " < ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $paramValue);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

// Only update if there are overdue tasks
if ($count > 0) {
    // Reuse the same comparison expression used for checking
    $updateCompareExpr = $compareExpr;
    // Build SET clause dynamically depending on available columns
    $setParts = [];

    // Determine an allowed enum value for 'status' column
    $targetStatus = null;
    if (columnExists($conn, 'tasks', 'status')) {
        // helper: get enum values for status
        $enumVals = [];
        $db = '';
        $res = $conn->query('SELECT DATABASE()');
        if ($res) {
            $db = $res->fetch_row()[0];
            $res->free();
        }
        $sqlEnum = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmtEnum = $conn->prepare($sqlEnum);
        $tableName = 'tasks';
        $colName = 'status';
        $stmtEnum->bind_param('sss', $db, $tableName, $colName);
        $stmtEnum->execute();
        $enumType = null;
        $stmtEnum->bind_result($enumType);
        if ($stmtEnum->fetch()) {
            // enumType looks like: enum('pending','completed','not_completed')
            if (preg_match("/^enum\((.*)\)$/i", $enumType, $m)) {
                $vals = $m[1];
                // split while handling quoted commas; provide explicit escape param to avoid deprecation
                $parts = str_getcsv($vals, ',', "'", "\\");
                foreach ($parts as $p) {
                    $enumVals[] = trim($p, "'\"");
                }
            }
        }
        $stmtEnum->close();

        // Prefer exact match 'not_completed'
        if (in_array('not_completed', $enumVals, true)) {
            $targetStatus = 'not_completed';
        } else {
            // Try some common variants
            $candidates = ['not completed', 'not-completed', 'not_complete', 'incomplete', 'late', 'overdue'];
            foreach ($candidates as $cand) {
                if (in_array($cand, $enumVals, true)) {
                    $targetStatus = $cand;
                    break;
                }
            }
        }

        // Fallback: pick the first enum value that is not 'pending'
        if ($targetStatus === null) {
            foreach ($enumVals as $v) {
                if ($v !== 'pending') {
                    $targetStatus = $v;
                    break;
                }
            }
        }
    }

    if ($targetStatus === null) {
        // No suitable status value found; skip updating to avoid truncation
        return;
    }

    // Use parameter binding for status to avoid SQL problems
    $setParts[] = "`status` = ?";

    // Only include reason if the column exists
    if (columnExists($conn, 'tasks', 'reason')) {
        // static message; escape to be safe if embedded
        $reasonText = $conn->real_escape_string('this person does not complete the task');
        $setParts[] = "`reason` = '" . $reasonText . "'";
    }

    // Only include last_updated if the column exists
    if (columnExists($conn, 'tasks', 'last_updated')) {
        $setParts[] = "`last_updated` = NOW()";
    }

    $setClause = implode(", ", $setParts);

    $sql_update = "UPDATE `tasks` SET " . $setClause . " WHERE `status` = 'pending' AND " . $updateCompareExpr . " < ?";
    $stmt2 = $conn->prepare($sql_update);
    // bind status and compare param
    $stmt2->bind_param("ss", $targetStatus, $paramValue);
    $stmt2->execute();
    $stmt2->close();
}
