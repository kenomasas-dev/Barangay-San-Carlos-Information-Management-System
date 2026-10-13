<?php
// Test file to check upload errors
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><body><pre>";
echo "=== Upload Test ===\n\n";

// Test database connection
echo "1. Testing database connection...\n";
$conn = new mysqli('localhost', 'root', '', 'barangay');
if ($conn->connect_error) {
    echo "   ❌ FAILED: " . $conn->connect_error . "\n";
} else {
    echo "   ✅ SUCCESS: Connected to database\n";
}

// Check if table exists
echo "\n2. Checking if bad_records table exists...\n";
$result = $conn->query("SHOW TABLES LIKE 'bad_records'");
if ($result && $result->num_rows > 0) {
    echo "   ✅ SUCCESS: Table exists\n";
    
    // Show table structure
    echo "\n3. Table structure:\n";
    $structure = $conn->query("DESCRIBE bad_records");
    while ($row = $structure->fetch_assoc()) {
        echo "   - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "   ❌ FAILED: Table does not exist\n";
    echo "\n   SQL to create table:\n";
    echo "   CREATE TABLE bad_records (\n";
    echo "     id INT(11) NOT NULL AUTO_INCREMENT,\n";
    echo "     name VARCHAR(255) NOT NULL,\n";
    echo "     age VARCHAR(10) DEFAULT NULL,\n";
    echo "     address VARCHAR(500) DEFAULT NULL,\n";
    echo "     offense TEXT NOT NULL,\n";
    echo "     date_recorded VARCHAR(50) DEFAULT NULL,\n";
    echo "     status VARCHAR(50) DEFAULT 'Active',\n";
    echo "     remarks TEXT DEFAULT NULL,\n";
    echo "     created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n";
    echo "     updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
    echo "     PRIMARY KEY (id)\n";
    echo "   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";
}

// Check file upload settings
echo "\n4. PHP upload settings:\n";
echo "   upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "   post_max_size: " . ini_get('post_max_size') . "\n";
echo "   file_uploads: " . (ini_get('file_uploads') ? 'enabled' : 'disabled') . "\n";

// Check if upload_bad_records.php exists
echo "\n5. Checking if upload_bad_records.php exists...\n";
if (file_exists('upload_bad_records.php')) {
    echo "   ✅ SUCCESS: File exists\n";
} else {
    echo "   ❌ FAILED: File not found\n";
}

$conn->close();
echo "\n=== End of Test ===";
echo "</pre></body></html>";
?>

