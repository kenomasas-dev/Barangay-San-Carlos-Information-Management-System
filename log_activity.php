<?php
/**
 * Log Admin Activity
 * 
 * This function records admin actions in the database
 * 
 * @param string $admin_name - Name of the admin performing the action
 * @param string $action - Brief description of the action
 * @param string $action_type - Type of action (Certificate Issued, Resident Accepted, etc.)
 * @param string $details - Additional details about the action (optional)
 * @param string $target_user - User affected by the action (optional)
 * @param int $admin_id - Admin ID (optional)
 */
function logAdminActivity($admin_name, $action, $action_type, $details = null, $target_user = null, $admin_id = null) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "barangay";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        error_log("Log Activity - Connection failed: " . $conn->connect_error);
        return false;
    }
    
    // If admin_name is not provided or is 'Unknown User', try to get it from session or database
    if (empty($admin_name) || $admin_name === 'Unknown User') {
        // Try to get from session first
        if (isset($_SESSION['username'])) {
            $admin_name = $_SESSION['username'];
            if (!$admin_id && isset($_SESSION['admin_id'])) {
                $admin_id = $_SESSION['admin_id'];
            }
        } 
        // If still unknown and we have admin_id, get from database
        elseif ($admin_id !== null) {
            $stmt = $conn->prepare("SELECT username, full_name FROM admin_login WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    $admin_name = $user['username'];
                }
                $stmt->close();
            }
        }
    }
    
    // If still unknown, set a default that won't be misleading
    if (empty($admin_name) || $admin_name === 'Unknown User') {
        $admin_name = 'System';
    }
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    // Add IP address to details if not already included
    if ($details && strpos($details, 'IP:') === false) {
        $details .= " | IP: " . $ip_address;
    } elseif (!$details) {
        $details = "IP: " . $ip_address;
    }
    
    // Prepare SQL
    $sql = "INSERT INTO admin_activity_logs (admin_id, admin_name, action, action_type, details, target_user, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("issssss", $admin_id, $admin_name, $action, $action_type, $details, $target_user, $ip_address);
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $result;
    } else {
        error_log("Log Activity - Prepare failed: " . $conn->error);
        $conn->close();
        return false;
    }
}

/**
 * Log Logout Activity
 * 
 * Special function for logging logout to ensure username is captured correctly
 * 
 * @param int $admin_id - Admin ID from session (optional)
 * @return bool - Success status
 */
function logLogout($admin_id = null) {
    // Try multiple sources for username
    $admin_name = 'Unknown User';
    
    // Source 1: Session
    if (isset($_SESSION['username'])) {
        $admin_name = $_SESSION['username'];
        if (!$admin_id && isset($_SESSION['admin_id'])) {
            $admin_id = $_SESSION['admin_id'];
        }
    }
    
    // Source 2: If we have admin_id but no username, query database
    if (($admin_name === 'Unknown User' || empty($admin_name)) && $admin_id !== null) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "barangay";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error === null) {
            $stmt = $conn->prepare("SELECT username FROM admin_login WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    $admin_name = $user['username'];
                }
                $stmt->close();
            }
            $conn->close();
        }
    }
    
    // Log the logout
    return logAdminActivity(
        $admin_name,
        "Admin logged out successfully",
        "Logout",
        "Logout at " . date('F j, Y - g:i A'),
        null,
        $admin_id
    );
}

// Example usage:
// logAdminActivity("John Doe", "Issued Barangay Clearance", "Certificate Issued", "Certificate ID: 12345", "Juan dela Cruz", 1);
// logAdminActivity("Admin User", "Accepted new resident", "Resident Accepted", "Resident: Maria Santos", "Maria Santos");
// logAdminActivity("Admin User", "Exported residents data", "Data Exported", "Exported 150 residents to Excel");
// logAdminActivity("Admin User", "Approved certificate request", "Request Approved", "Request ID: 789", "Pedro Garcia");
// logLogout(); // For logout - automatically captures username
?>