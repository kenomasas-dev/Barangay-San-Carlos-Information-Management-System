<?php
session_start();



// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barangay";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if table structure needs updating
$check_columns = $conn->query("SHOW COLUMNS FROM admin_login LIKE 'status'");
if ($check_columns->num_rows == 0) {
    // Add missing columns
    $conn->query("ALTER TABLE `admin_login` ADD COLUMN `full_name` VARCHAR(255) NULL AFTER `password`");
    $conn->query("ALTER TABLE `admin_login` ADD COLUMN `email` VARCHAR(255) NULL AFTER `full_name`");
    $conn->query("ALTER TABLE `admin_login` ADD COLUMN `status` ENUM('active', 'inactive') DEFAULT 'active' AFTER `email`");
    $conn->query("ALTER TABLE `admin_login` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `status`");
    
    // Update existing records
    $conn->query("UPDATE `admin_login` SET `status` = 'active', `full_name` = 'Administrator', `email` = CONCAT(username, '@barangaysancarlos.com') WHERE `status` IS NULL");
    
    $table_updated = true;
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add_user') {
            $new_username = trim($_POST['new_username']);
            $new_password = trim($_POST['new_password']);
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            
            // Check if username already exists
            $check_stmt = $conn->prepare("SELECT username FROM admin_login WHERE username = ?");
            $check_stmt->bind_param("s", $new_username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_msg = "Username already exists!";
            } else {
                $stmt = $conn->prepare("INSERT INTO admin_login (username, password, full_name, email, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                $stmt->bind_param("ssss", $new_username, $new_password, $full_name, $email);
                
                if ($stmt->execute()) {
                    $success_msg = "User added successfully!";
                } else {
                    $error_msg = "Error adding user: " . $conn->error;
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
        
        if ($action === 'toggle_status') {
            $user_id = $_POST['user_id'];
            $new_status = $_POST['new_status'];
            
            $stmt = $conn->prepare("UPDATE admin_login SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $user_id);
            
            if ($stmt->execute()) {
                $success_msg = "User status updated successfully!";
            } else {
                $error_msg = "Error updating status: " . $conn->error;
            }
            $stmt->close();
        }
        
        if ($action === 'delete_user') {
            $user_id = $_POST['user_id'];
            
            $stmt = $conn->prepare("DELETE FROM admin_login WHERE id = ? AND username != 'superadmin'");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success_msg = "User deleted successfully!";
                } else {
                    $error_msg = "Cannot delete superadmin account!";
                }
            } else {
                $error_msg = "Error deleting user: " . $conn->error;
            }
            $stmt->close();
        }
        
        if ($action === 'edit_user') {
            $user_id = $_POST['user_id'];
            $edit_username = trim($_POST['edit_username']);
            $edit_password = trim($_POST['edit_password']);
            $edit_full_name = trim($_POST['edit_full_name']);
            $edit_email = trim($_POST['edit_email']);
            
            if (!empty($edit_password)) {
                $stmt = $conn->prepare("UPDATE admin_login SET username = ?, password = ?, full_name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $edit_username, $edit_password, $edit_full_name, $edit_email, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE admin_login SET username = ?, full_name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("sssi", $edit_username, $edit_full_name, $edit_email, $user_id);
            }
            
            if ($stmt->execute()) {
                $success_msg = "User updated successfully!";
            } else {
                $error_msg = "Error updating user: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all users
$users_query = "SELECT id, username, password, full_name, email, status, created_at FROM admin_login ORDER BY created_at DESC";
$users_result = $conn->query($users_query);

// Get statistics
$total_users = $users_result->num_rows;
$active_users_result = $conn->query("SELECT COUNT(*) as count FROM admin_login WHERE status = 'active'");
$active_users = $active_users_result->fetch_assoc()['count'];
$inactive_users_result = $conn->query("SELECT COUNT(*) as count FROM admin_login WHERE status = 'inactive'");
$inactive_users = $inactive_users_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
     <link rel="icon" href="favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - Barangay San Carlos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-left h1 {
            font-size: 32px;
            margin-bottom: 5px;
        }

        .header-left p {
            opacity: 0.9;
            font-size: 14px;
        }

        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-info {
            text-align: right;
        }

        .user-info .username {
            font-weight: bold;
            font-size: 18px;
        }

        .user-info .role {
            font-size: 12px;
            opacity: 0.8;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            padding: 10px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background: white;
            color: #1e3c72;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            color: white;
        }

        .stat-icon.red {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
        }

        .stat-info h3 {
            font-size: 32px;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 14px;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .users-table {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background 0.3s;
        }

        tbody tr:hover {
            background: #f5f5f5;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-toggle {
            background: #ffc107;
            color: #333;
        }

        .btn-toggle:hover {
            background: #ffb300;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .btn-edit {
            background: #17a2b8;
            color: white;
        }

        .btn-edit:hover {
            background: #138496;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideIn 0.3s;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideUp 0.3s;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #000;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-buttons button {
            flex: 1;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header-right {
                flex-direction: column;
            }

            .user-info {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h1><i class="fas fa-shield-alt"></i> Super Admin Panel</h1>
                <p>Barangay San Carlos Information Management System</p>
            </div>
            
                <a href="super_admin_logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <?php if (isset($table_updated)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Database table updated successfully! All required columns have been added.
            </div>
        <?php endif; ?>

        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $active_users; ?></h3>
                    <p>Active Users</p>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $inactive_users; ?></h3>
                    <p>Inactive Users</p>
                </div>
                <div class="stat-icon red">
                    <i class="fas fa-user-times"></i>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="card">
                <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="full_name" required placeholder="Enter full name">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" required placeholder="Enter email address">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-id-badge"></i> Username</label>
                        <input type="text" name="new_username" required placeholder="Enter username">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="password" name="new_password" required placeholder="Enter password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add User
                    </button>
                </form>
            </div>

            <div class="card">
                <h2><i class="fas fa-users-cog"></i> Manage Users</h2>
                <div class="users-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $users_result->data_seek(0);
                            while ($user = $users_result->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['created_at'] ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($user['username'] !== 'superadmin'): ?>
                                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" class="btn-small btn-edit" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $user['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" class="btn-small btn-toggle" title="Toggle Status">
                                                    <i class="fas fa-toggle-<?php echo $user['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn-small btn-delete" title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 12px;">Protected</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit"></i> Edit User</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="edit_full_name" id="edit_full_name" required placeholder="Enter full name">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="edit_email" id="edit_email" required placeholder="Enter email address">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-id-badge"></i> Username</label>
                    <input type="text" name="edit_username" id="edit_username" required placeholder="Enter username">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password (leave blank to keep current)</label>
                    <input type="password" name="edit_password" id="edit_password" placeholder="Enter new password">
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Modal functions
        function openEditModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_full_name').value = user.full_name || '';
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_password').value = '';
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>