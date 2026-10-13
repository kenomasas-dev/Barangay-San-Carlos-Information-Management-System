<?php
// update_request_status.php
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
    
    // Get form data
    $requestId = isset($_POST['request_id']) ? $_POST['request_id'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    if (empty($requestId) || empty($status)) {
        throw new Exception("Request ID and status are required");
    }
    
    // Get request details before updating (for logging)
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
    
    // Update request status
    $sql = "UPDATE certificate_requests 
            SET status = ?, processed_date = NOW(), notes = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssi", $status, $notes, $requestId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log the status update (only if admin is logged in)
            if (isset($_SESSION['username']) && $requestData) {
                $admin_name = $_SESSION['username'];
                $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
                $reqId = $requestData['request_id'];
                $resident = $requestData['resident_name'];
                $certType = $requestData['certificate_type'];
                
                // Determine action type based on status
                $actionType = "Request Processed";
                $actionDesc = "Updated certificate request status";
                
                if (strtolower($status) === 'approved') {
                    $actionType = "Request Approved";
                    $actionDesc = "Approved certificate request";
                } elseif (strtolower($status) === 'declined' || strtolower($status) === 'rejected') {
                    $actionType = "Request Rejected";
                    $actionDesc = "Declined certificate request";
                } elseif (strtolower($status) === 'issued') {
                    $actionType = "Certificate Issued";
                    $actionDesc = "Issued certificate";
                }
                
                $details = "Request ID: {$reqId}, Type: {$certType}, Status: {$status}";
                if (!empty($notes)) {
                    $details .= ", Notes: {$notes}";
                }
                
                logAdminActivity(
                    $admin_name,
                    $actionDesc,
                    $actionType,
                    $details,
                    $resident,
                    $admin_id
                );
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Request status updated successfully'
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