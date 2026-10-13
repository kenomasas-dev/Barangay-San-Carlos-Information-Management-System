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

// Check if file was uploaded
if (!isset($_FILES['uploadFile']) || $_FILES['uploadFile']['error'] != 0) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

// Get form data
$category = isset($_POST['category']) ? $_POST['category'] : 'General';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$uploaded_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';

// File information
$originalName = basename($_FILES['uploadFile']['name']);
$fileSize = $_FILES['uploadFile']['size'];
$fileType = $_FILES['uploadFile']['type'];
$tmpName = $_FILES['uploadFile']['tmp_name'];

// Validate file size (max 50MB)
if ($fileSize > 50 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 50MB limit']);
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/records/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$extension = pathinfo($originalName, PATHINFO_EXTENSION);
$fileName = time() . '_' . uniqid() . '.' . $extension;
$filePath = $uploadDir . $fileName;

// Move uploaded file
if (move_uploaded_file($tmpName, $filePath)) {
    // Insert into database
    $sql = "INSERT INTO uploaded_files (file_name, original_name, file_path, file_type, file_size, uploaded_by, description, category, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisss", $fileName, $originalName, $filePath, $fileType, $fileSize, $uploaded_by, $description, $category);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
    } else {
        // Delete file if database insert fails
        unlink($filePath);
        echo json_encode(['success' => false, 'message' => 'Failed to save file information to database']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}

$conn->close();
?>

