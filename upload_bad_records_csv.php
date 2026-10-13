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
if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] != 0) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

// Validate file extension
$allowedExtensions = ['csv', 'xls', 'xlsx'];
$fileExtension = strtolower(pathinfo($_FILES['excelFile']['name'], PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file format. Please upload .csv, .xls or .xlsx file']);
    exit();
}

try {
    $insertedCount = 0;
    $errorCount = 0;
    
    // Handle CSV files
    if ($fileExtension == 'csv') {
        $handle = fopen($_FILES['excelFile']['tmp_name'], "r");
        if ($handle === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to read CSV file']);
            exit();
        }
        
        // Skip header row
        fgetcsv($handle);
        
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Extract data from columns
            // Expected: Name, Age, Address, Offense/Violation, Date Recorded, Status, Remarks
            $name = $conn->real_escape_string(trim($row[0] ?? ''));
            $age = $conn->real_escape_string(trim($row[1] ?? ''));
            $address = $conn->real_escape_string(trim($row[2] ?? ''));
            $offense = $conn->real_escape_string(trim($row[3] ?? ''));
            $date_recorded = $conn->real_escape_string(trim($row[4] ?? ''));
            $status = $conn->real_escape_string(trim($row[5] ?? 'Active'));
            $remarks = $conn->real_escape_string(trim($row[6] ?? ''));
            
            // Validate required fields
            if (empty($name) || empty($offense)) {
                $errorCount++;
                continue;
            }
            
            // Insert into database
            $sql = "INSERT INTO bad_records (name, age, address, offense, date_recorded, status, remarks, created_at) 
                    VALUES ('$name', '$age', '$address', '$offense', '$date_recorded', '$status', '$remarks', NOW())";
            
            if ($conn->query($sql)) {
                $insertedCount++;
            } else {
                $errorCount++;
            }
        }
        
        fclose($handle);
    } else {
        // Excel files - need PhpSpreadsheet
        echo json_encode([
            'success' => false, 
            'message' => 'Excel files (.xls, .xlsx) require PhpSpreadsheet library. Please convert your Excel file to CSV format and upload, or contact your system administrator to install the library.'
        ]);
        exit();
    }
    
    if ($insertedCount > 0) {
        echo json_encode([
            'success' => true, 
            'message' => "$insertedCount records uploaded successfully" . ($errorCount > 0 ? " ($errorCount errors)" : "")
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No valid records found in the file']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error processing file: ' . $e->getMessage()]);
}

$conn->close();
?>

