<?php
session_start();

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

// Get file ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid file ID']);
    exit();
}

// Get file path before deleting from database
$sql = "SELECT file_path FROM uploaded_files WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $file = $result->fetch_assoc();
    $filePath = $file['file_path'];
    
    // Delete from database
    $deleteSql = "DELETE FROM uploaded_files WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $id);
    
    if ($deleteStmt->execute()) {
        // Delete physical file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete file from database']);
    }
    
    $deleteStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'File not found']);
}

$stmt->close();
$conn->close();
?>
