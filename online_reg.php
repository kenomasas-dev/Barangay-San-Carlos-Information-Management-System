<?php
// Database configuration
$host = "localhost";
$username = "root"; // default in XAMPP
$password = "";     // default in XAMPP
$database = "barangay";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle profile image upload
    $profileFile = $_FILES['profile-upload']['name'];
    $targetDir = "uploads/";
    $targetFilePath = $targetDir . basename($profileFile);

    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (!empty($profileFile)) {
        move_uploaded_file($_FILES['profile-upload']['tmp_name'], $targetFilePath);
    } else {
        $targetFilePath = ""; // Default empty if no upload
    }

    // Handle purok clearance file upload (required)
    $purokClearanceFile = "";
    if (!isset($_FILES['purok-clearance']) || $_FILES['purok-clearance']['error'] !== UPLOAD_ERR_OK) {
        if (!isset($_FILES['purok-clearance']) || $_FILES['purok-clearance']['error'] === UPLOAD_ERR_NO_FILE) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Purok Clearance Certificate is required. Please upload the file.']);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error uploading purok clearance file. Please try again.']);
            exit();
        }
    }
    
    if (isset($_FILES['purok-clearance']) && $_FILES['purok-clearance']['error'] === UPLOAD_ERR_OK) {
        $purokClearanceDir = "uploads/purok_clearance/";
        
        // Create directory if it doesn't exist
        if (!is_dir($purokClearanceDir)) {
            mkdir($purokClearanceDir, 0755, true);
        }
        
        // Get file info
        $purokFileName = $_FILES['purok-clearance']['name'];
        $purokFileTmp = $_FILES['purok-clearance']['tmp_name'];
        $purokFileSize = $_FILES['purok-clearance']['size'];
        $purokFileType = $_FILES['purok-clearance']['type'];
        
        // Validate file type
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 
                        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        // Validate file size (5MB max)
        $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
        
        if ($purokFileSize > $maxFileSize) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Purok clearance file size exceeds 5MB limit.']);
            exit();
        }
        
        if (!in_array($purokFileType, $allowedTypes)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload PDF, JPG, PNG, DOC, or DOCX files only.']);
            exit();
        }
        
        // Generate unique filename to avoid overwrites
        $fileExtension = pathinfo($purokFileName, PATHINFO_EXTENSION);
        $uniqueFileName = 'purok_clearance_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $purokClearancePath = $purokClearanceDir . $uniqueFileName;
        
        // Move uploaded file
        if (move_uploaded_file($purokFileTmp, $purokClearancePath)) {
            $purokClearanceFile = $purokClearancePath;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to upload purok clearance file.']);
            exit();
        }
    }

    // Collect all form data
    $last_name = $_POST['last-name'];
    $first_name = $_POST['first-name'];
    $middle_name = $_POST['middle-name'];
    $nickname_alias = $_POST['nickname-alias'];
    $birthdate = $_POST['birthdate'];
    $birthplace = $_POST['birthplace'];
    $citizenship = $_POST['citizenship'];
    // Handle "Other" citizenship
    if ($citizenship === 'Other' && !empty($_POST['other_citizenship'])) {
        $citizenship = $_POST['other_citizenship'];
    }
    $gender = $_POST['gender'];
    $mobile_number = $_POST['mobile-number'];
    $email = $_POST['email'];
    $marital_status = $_POST['marital-status'];
    $religion = $_POST['religion'];
    // Handle custom religion
    if ($religion === 'Other' && !empty($_POST['customReligion'])) {
        $religion = $_POST['customReligion'];
    }
    $sector = $_POST['sector'];
    $education = $_POST['education'];
    $occupation = $_POST['occupation'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $sitio = $_POST['sitio'];
    $house_number = $_POST['house-number'];
    $purok = $_POST['purok'];
    $since_date = $_POST['since-date'];
    $household_number = $_POST['household-number'];
    $house_owner = $_POST['house-owner'];
    $shelter_type = $_POST['shelter-type'];
    $house_material = $_POST['house-material'];
    
    // Validate 6 months residency from since_date
    if (!empty($since_date)) {
        $selectedDate = new DateTime($since_date);
        $today = new DateTime();
        $interval = $selectedDate->diff($today);
        
        // Calculate total months
        $monthsDiff = ($interval->y * 12) + $interval->m;
        
        if ($monthsDiff < 6) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You must be a resident for at least 6 months to register.']);
            exit();
        }
    }
    
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt the password

    // Insert query
    $sql = "INSERT INTO reg_online (
        profile_upload, last_name, first_name, middle_name, nickname_alias,
        birthdate, birthplace, citizenship, gender, mobile_number, email,
        marital_status, religion, sector, education, occupation, height, weight,
        sitio, house_number, purok, since_date, household_number, house_owner,
        shelter_type, house_material, password, purok_clearance
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssssssssssssssss",
        $targetFilePath, $last_name, $first_name, $middle_name, $nickname_alias,
        $birthdate, $birthplace, $citizenship, $gender, $mobile_number, $email,
        $marital_status, $religion, $sector, $education, $occupation, $height, $weight,
        $sitio, $house_number, $purok, $since_date, $household_number, $house_owner,
        $shelter_type, $house_material, $password, $purokClearanceFile
    );

    if ($stmt->execute()) {
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Registration successful! Please wait for approval.']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
