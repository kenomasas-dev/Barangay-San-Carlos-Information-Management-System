<?php
// export_residents.php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to get admin information
session_start();

// Include logging function
require_once 'log_activity.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "barangay";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameter
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'all';

// Build SQL query based on filter
$sql = "SELECT * FROM residents";
$whereClause = "";
$filename_suffix = "";

switch($filter) {
    case 'gender_male':
        $whereClause = " WHERE gender = 'Male'";
        $filename_suffix = "_male";
        break;
    case 'gender_female':
        $whereClause = " WHERE gender = 'Female'";
        $filename_suffix = "_female";
        break;
    case 'age_children':
        $whereClause = " WHERE TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 0 AND 17";
        $filename_suffix = "_children";
        break;
    case 'age_adults':
        $whereClause = " WHERE TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 59";
        $filename_suffix = "_adults";
        break;
    case 'age_seniors':
        $whereClause = " WHERE TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60";
        $filename_suffix = "_seniors";
        break;
    case 'purok_1':
        $whereClause = " WHERE purok = '1' OR purok = 'Purok 1'";
        $filename_suffix = "_purok1";
        break;
    case 'purok_2':
        $whereClause = " WHERE purok = '2' OR purok = 'Purok 2'";
        $filename_suffix = "_purok2";
        break;
    case 'purok_3':
        $whereClause = " WHERE purok = '3' OR purok = 'Purok 3'";
        $filename_suffix = "_purok3";
        break;
    case 'purok_4':
        $whereClause = " WHERE purok = '4' OR purok = 'Purok 4'";
        $filename_suffix = "_purok4";
        break;
    case 'purok_5':
        $whereClause = " WHERE purok = '5' OR purok = 'Purok 5'";
        $filename_suffix = "_purok5";
        break;
    case 'purok_6':
        $whereClause = " WHERE purok = '6' OR purok = 'Purok 6'";
        $filename_suffix = "_purok6";
        break;
    case 'purok_7':
        $whereClause = " WHERE purok = '7' OR purok = 'Purok 7'";
        $filename_suffix = "_purok7";
        break;
    
    // Sector filters
    case 'sector_indigenous':
        $whereClause = " WHERE sector = 'Indigenous Peoples'";
        $filename_suffix = "_indigenous_people";
        break;
    case 'sector_senior':
        $whereClause = " WHERE sector = 'Senior Citizen'";
        $filename_suffix = "_senior_citizens";
        break;
    case 'sector_pwd':
        $whereClause = " WHERE sector = 'PWD (Persons with Disabilities)'";
        $filename_suffix = "_pwd";
        break;
    case 'sector_youth':
        $whereClause = " WHERE sector = 'Youth'";
        $filename_suffix = "_youth";
        break;
    case 'sector_lgbtq':
        $whereClause = " WHERE sector = 'LGBTQ+'";
        $filename_suffix = "_lgbtq";
        break;
        
    default:
        $whereClause = "";
        $filename_suffix = "_all";
        break;
}

$sql .= $whereClause . " ORDER BY last_name, first_name";

// Fetch filtered residents from database
$result = $conn->query($sql);

// Get count for logging
$record_count = $result->num_rows;

// Log the export activity
if (isset($_SESSION['username'])) {
    $admin_name = $_SESSION['username'];
    $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
    
    // Create a descriptive filter name
    $filter_description = "All Residents";
    switch($filter) {
        case 'gender_male': $filter_description = "Male Residents"; break;
        case 'gender_female': $filter_description = "Female Residents"; break;
        case 'age_children': $filter_description = "Children (0-17)"; break;
        case 'age_adults': $filter_description = "Adults (18-59)"; break;
        case 'age_seniors': $filter_description = "Seniors (60+)"; break;
        case 'purok_1': $filter_description = "Purok 1"; break;
        case 'purok_2': $filter_description = "Purok 2"; break;
        case 'purok_3': $filter_description = "Purok 3"; break;
        case 'purok_4': $filter_description = "Purok 4"; break;
        case 'purok_5': $filter_description = "Purok 5"; break;
        case 'purok_6': $filter_description = "Purok 6"; break;
        case 'purok_7': $filter_description = "Purok 7"; break;
        case 'sector_indigenous': $filter_description = "Indigenous Peoples"; break;
        case 'sector_senior': $filter_description = "Senior Citizens"; break;
        case 'sector_pwd': $filter_description = "PWD"; break;
        case 'sector_youth': $filter_description = "Youth"; break;
        case 'sector_lgbtq': $filter_description = "LGBTQ+"; break;
    }
    
    $action_details = "Exported {$record_count} {$filter_description} to Excel";
    
    logAdminActivity(
        $admin_name,
        "Exported residents data to Excel",
        "Data Exported",
        $action_details,
        null,
        $admin_id
    );
}

// Set headers for Excel download with dynamic filename
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="residents_export' . $filename_suffix . '_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Start building the Excel content
echo '<table border="1">';

// Header row
echo '<tr style="background-color: #4CAF50; color: white; font-weight: bold;">';
echo '<th>ID</th>';
echo '<th>Last Name</th>';
echo '<th>First Name</th>';
echo '<th>Middle Name</th>';
echo '<th>Nickname/Alias</th>';
echo '<th>Birthdate</th>';
echo '<th>Birthplace</th>';
echo '<th>Citizenship</th>';
echo '<th>Gender</th>';
echo '<th>Mobile Number</th>';
echo '<th>Email</th>';
echo '<th>Marital Status</th>';
echo '<th>Religion</th>';
echo '<th>Sector</th>';
echo '<th>Education</th>';
echo '<th>Height (cm)</th>';
echo '<th>Weight (kg)</th>';
echo '<th>Sitio</th>';
echo '<th>House Number</th>';
echo '<th>Purok</th>';
echo '<th>Since Year</th>';
echo '<th>Household Number</th>';
echo '<th>House Owner</th>';
echo '<th>Shelter Type</th>';
echo '<th>House Material</th>';
echo '<th>Profile Image</th>';
echo '<th>Date Added</th>';
echo '</tr>';

// Data rows
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['last_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['first_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['middle_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['nickname'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['birthdate'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['birthplace'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['citizenship'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['gender'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['mobile_number'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['email'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['marital_status'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['religion'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['sector'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['education'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['height'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['weight'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['sitio'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['house_number'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['purok'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['since_year'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['household_number'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['house_owner'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['shelter_type'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['house_material'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['profile_image'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($row['created_at'] ?? '') . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="27">No residents found</td></tr>';
}

echo '</table>';

$conn->close();
?>