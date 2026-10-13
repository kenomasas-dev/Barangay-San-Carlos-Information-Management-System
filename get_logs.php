<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barangay";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

// Try to fetch from admin_activity_logs first, fallback to activity_logs for backward compatibility
$sql = "SELECT * FROM admin_activity_logs ORDER BY created_at DESC LIMIT 100";
$result = $conn->query($sql);

$logs = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $logs[] = [
            'id' => $row['id'],
            'username' => $row['admin_name'],
            'action' => strtolower($row['action_type']),
            'action_type' => $row['action_type'],
            'action_description' => $row['action'],
            'details' => $row['details'],
            'target_user' => $row['target_user'],
            'log_time' => $row['created_at'],
            'formatted_time' => date('F j, Y - g:i A', strtotime($row['created_at']))
        ];
    }
} else {
    // Fallback to activity_logs if admin_activity_logs doesn't exist or is empty
    $sql = "SELECT * FROM activity_logs ORDER BY log_time DESC LIMIT 100";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $logs[] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'action' => $row['action'],
                'action_type' => ucfirst($row['action']),
                'action_description' => ucfirst($row['action']),
                'details' => null,
                'target_user' => null,
                'log_time' => $row['log_time'],
                'formatted_time' => date('F j, Y - g:i A', strtotime($row['log_time']))
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($logs);

$conn->close();
?>