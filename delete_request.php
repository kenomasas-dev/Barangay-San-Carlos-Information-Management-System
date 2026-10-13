<?php
// delete_request.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for admin info
session_start();

// Include logging function
require_once 'log_activity.php';

header('Content-Type: application/json');

try {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "barangay";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get request ID
    $requestId = isset($_POST['request_id']) ? $_POST['request_id'] : '';
    
    if (empty($requestId)) {
        throw new Exception("Request ID is required");
    }
    
    // Get request details before deleting (for logging)
    $getRequestSql = "SELECT request_id, resident_name, certificate_type FROM certificate_requests WHERE id = ?";
    $getRequestStmt = $conn->prepare($getRequestSql);
    $getRequestStmt->bind_param("i", $requestId);
    $getRequestStmt->execute();
    $requestResult = $getRequestStmt->get_result();
    $requestData = null;
    if ($requestResult->num_rows > 0) {
        $requestData = $requestResult->fetch_assoc();
    }
    $getRequestStmt->close();
    
    // Delete request
    $sql = "DELETE FROM certificate_requests WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $requestId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log the deletion (only if admin is logged in)
            if (isset($_SESSION['username']) && $requestData) {
                $admin_name = $_SESSION['username'];
                $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
                $reqId = $requestData['request_id'];
                $resident = $requestData['resident_name'];
                $certType = $requestData['certificate_type'];
                
                logAdminActivity(
                    $admin_name,
                    "Deleted certificate request",
                    "Request Processed",
                    "Deleted Request ID: {$reqId}, Type: {$certType}",
                    $resident,
                    $admin_id
                );
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Request deleted successfully'
            ]);
        } else {
            throw new Exception("No request found with the given ID");
        }
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>