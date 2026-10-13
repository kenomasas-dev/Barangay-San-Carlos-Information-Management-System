<?php


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barangay";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameters
$filterType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterDate = isset($_GET['date']) ? $_GET['date'] : '';
$filterAdmin = isset($_GET['admin']) ? $_GET['admin'] : '';

// Build WHERE clause
$whereConditions = [];
if ($filterType !== 'all') {
    $whereConditions[] = "action_type = '" . $conn->real_escape_string($filterType) . "'";
}
if ($filterDate) {
    $whereConditions[] = "DATE(created_at) = '" . $conn->real_escape_string($filterDate) . "'";
}
if ($filterAdmin) {
    $whereConditions[] = "admin_username LIKE '%" . $conn->real_escape_string($filterAdmin) . "%'";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Fetch admin logs with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 50;
$offset = ($page - 1) * $recordsPerPage;

$sql = "SELECT * FROM admin_logs $whereClause ORDER BY created_at DESC LIMIT $recordsPerPage OFFSET $offset";
$result = $conn->query($sql);

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM admin_logs $whereClause";
$countResult = $conn->query($countSql);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get unique admin usernames for filter
$adminsSql = "SELECT DISTINCT admin_username FROM admin_logs ORDER BY admin_username";
$adminsResult = $conn->query($adminsSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - Admin Activity History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .container-main {
            margin-left: 250px;
            padding: 30px;
        }

        .header-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .header-section h2 {
            margin: 0;
            color: #667eea;
            font-weight: 700;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-item {
            flex: 1;
            min-width: 200px;
        }

        .filter-item label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .filter-item select,
        .filter-item input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: border-color 0.3s;
        }

        .filter-item select:focus,
        .filter-item input:focus {
            outline: none;
            border-color: #667eea;
        }

        .filter-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
        }

        .reset-btn {
            background: #6c757d;
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .reset-btn:hover {
            transform: translateY(-2px);
            background: #5a6268;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .stat-card.login i {
            color: #28a745;
        }

        .stat-card.logout i {
            color: #dc3545;
        }

        .stat-card.action i {
            color: #667eea;
        }

        .stat-card h3 {
            font-size: 2rem;
            margin: 10px 0 5px 0;
            font-weight: 700;
        }

        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .logs-table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logs-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .logs-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .logs-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .logs-table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge-login {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-logout {
            background: #dc3545;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-action {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: #667eea;
            background: white;
            border: 2px solid #667eea;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
        }

        .pagination .active {
            background: #667eea;
            color: white;
        }

        .no-data {
            text-align: center;
            padding: 50px;
            color: #999;
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 235px;
            background-color: #31363F;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar h2 {
            color: white;
            margin-bottom: 30px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
        }

        .sidebar a i {
            margin-right: 10px;
        }

        .back-btn {
            background: #6c757d;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            background: #5a6268;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <h2>BSCIMS</h2>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="Administration.php"><i class="fas fa-users-cog"></i> Administration</a>
        <a href="system_logs.php" style="background: rgba(255,255,255,0.2);"><i class="fas fa-clipboard-list"></i> System Logs</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>

    <div class="container-main">
        <!-- Header -->
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-clipboard-list me-2"></i>System Logs - Admin Activity History</h2>
                    <p class="mb-0 text-muted">Track all admin actions and login history</p>
                </div>
                <a href="Administration.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <?php
        $loginCount = $conn->query("SELECT COUNT(*) as count FROM admin_logs WHERE action_type = 'login'")->fetch_assoc()['count'];
        $logoutCount = $conn->query("SELECT COUNT(*) as count FROM admin_logs WHERE action_type = 'logout'")->fetch_assoc()['count'];
        $actionCount = $conn->query("SELECT COUNT(*) as count FROM admin_logs WHERE action_type NOT IN ('login', 'logout')")->fetch_assoc()['count'];
        ?>
        <div class="stats-cards">
            <div class="stat-card login">
                <i class="fas fa-sign-in-alt"></i>
                <h3><?php echo number_format($loginCount); ?></h3>
                <p>Total Logins</p>
            </div>
            <div class="stat-card logout">
                <i class="fas fa-sign-out-alt"></i>
                <h3><?php echo number_format($logoutCount); ?></h3>
                <p>Total Logouts</p>
            </div>
            <div class="stat-card action">
                <i class="fas fa-tasks"></i>
                <h3><?php echo number_format($actionCount); ?></h3>
                <p>Total Actions</p>
            </div>
            <div class="stat-card action">
                <i class="fas fa-chart-line"></i>
                <h3><?php echo number_format($totalRecords); ?></h3>
                <p>Total Logs</p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Logs</h5>
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-item">
                        <label>Action Type</label>
                        <select name="type">
                            <option value="all" <?php echo $filterType === 'all' ? 'selected' : ''; ?>>All Types</option>
                            <option value="login" <?php echo $filterType === 'login' ? 'selected' : ''; ?>>Login</option>
                            <option value="logout" <?php echo $filterType === 'logout' ? 'selected' : ''; ?>>Logout</option>
                            <option value="create" <?php echo $filterType === 'create' ? 'selected' : ''; ?>>Create</option>
                            <option value="update" <?php echo $filterType === 'update' ? 'selected' : ''; ?>>Update</option>
                            <option value="delete" <?php echo $filterType === 'delete' ? 'selected' : ''; ?>>Delete</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>Date</label>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>">
                    </div>
                    <div class="filter-item">
                        <label>Admin Username</label>
                        <select name="admin">
                            <option value="">All Admins</option>
                            <?php while($admin = $adminsResult->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($admin['admin_username']); ?>" 
                                    <?php echo $filterAdmin === $admin['admin_username'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($admin['admin_username']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                    </div>
                    <div>
                        <a href="system_logs.php" class="reset-btn" style="text-decoration: none; display: inline-block;">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="logs-table-container">
            <h5 class="mb-3"><i class="fas fa-list me-2"></i>Activity Logs (Page <?php echo $page; ?> of <?php echo $totalPages; ?>)</h5>
            <?php if ($result && $result->num_rows > 0): ?>
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Admin</th>
                            <th>Action Type</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($log = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($log['admin_username']); ?></strong></td>
                                <td>
                                    <?php
                                    $actionType = strtolower($log['action_type']);
                                    if ($actionType === 'login') {
                                        echo '<span class="badge-login">Login</span>';
                                    } elseif ($actionType === 'logout') {
                                        echo '<span class="badge-logout">Logout</span>';
                                    } else {
                                        echo '<span class="badge-action">' . htmlspecialchars(ucfirst($log['action_type'])) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($log['action_description']); ?></td>
                                <td><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y h:i:s A', strtotime($log['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>&type=<?php echo $filterType; ?>&date=<?php echo $filterDate; ?>&admin=<?php echo $filterAdmin; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&type=<?php echo $filterType; ?>&date=<?php echo $filterDate; ?>&admin=<?php echo $filterAdmin; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>&type=<?php echo $filterType; ?>&date=<?php echo $filterDate; ?>&admin=<?php echo $filterAdmin; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>No logs found matching your filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>

