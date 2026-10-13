<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barangay";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get filter parameters
$filterType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterDate = isset($_GET['date']) ? $_GET['date'] : '';
$filterAdmin = isset($_GET['admin']) ? $_GET['admin'] : '';

// Build WHERE clause
$whereConditions = [];
if ($filterType !== 'all' && !empty($filterType)) {
    $whereConditions[] = "action_type = '" . $conn->real_escape_string($filterType) . "'";
}
if (!empty($filterDate)) {
    $whereConditions[] = "DATE(created_at) = '" . $conn->real_escape_string($filterDate) . "'";
}
if (!empty($filterAdmin)) {
    $whereConditions[] = "admin_username LIKE '%" . $conn->real_escape_string($filterAdmin) . "%'";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get statistics
$loginCount = $conn->query("SELECT COUNT(*) as count FROM admin_logs WHERE action_type = 'login'")->fetch_assoc()['count'];
$logoutCount = $conn->query("SELECT COUNT(*) as count FROM admin_logs WHERE action_type = 'logout'")->fetch_assoc()['count'];
$actionCount = $conn->query("SELECT COUNT(*) as count FROM admin_logs WHERE action_type NOT IN ('login', 'logout')")->fetch_assoc()['count'];
$totalCount = $conn->query("SELECT COUNT(*) as count FROM admin_logs $whereClause")->fetch_assoc()['count'];

// Fetch logs (limit to 100 most recent)
$sql = "SELECT * FROM admin_logs $whereClause ORDER BY created_at DESC LIMIT 100";
$result = $conn->query($sql);

$logs = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

// Get unique admin usernames
$adminsSql = "SELECT DISTINCT admin_username FROM admin_logs ORDER BY admin_username";
$adminsResult = $conn->query($adminsSql);
$admins = [];
if ($adminsResult && $adminsResult->num_rows > 0) {
    while ($row = $adminsResult->fetch_assoc()) {
        $admins[] = $row['admin_username'];
    }
}

// Return JSON response
echo json_encode([
    'success' => true,
    'stats' => [
        'logins' => (int)$loginCount,
        'logouts' => (int)$logoutCount,
        'actions' => (int)$actionCount,
        'total' => (int)$totalCount
    ],
    'logs' => $logs,
    'admins' => $admins
]);

$conn->close();
?>

