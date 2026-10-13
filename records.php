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

// Fetch bad records
$sql = "SELECT * FROM bad_records ORDER BY created_at DESC";
$result = $conn->query($sql);

// Fetch uploaded files
$sql_files = "SELECT * FROM uploaded_files ORDER BY created_at DESC";
$result_files = $conn->query($sql_files);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records - BSCIMS</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .header {
            background: linear-gradient(135deg, #31363F 0%, #222831 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .container-main {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .upload-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .table-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table {
            margin-top: 20px;
        }

        .table thead {
            background: linear-gradient(135deg, #31363F 0%, #222831 100%);
            color: white;
        }

        .table thead th {
            border: none;
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .badge-danger {
            background-color: #dc3545;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .btn-delete {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 6px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .file-input-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .file-name {
            margin-left: 15px;
            font-weight: 500;
            color: #666;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        .back-btn {
            background-color: #6c757d;
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            background-color: #5a6268;
            color: white;
            transform: translateY(-2px);
        }

         .no-records {
             text-align: center;
             padding: 50px;
             color: #6c757d;
             font-size: 1.2rem;
         }

         .nav-tabs .nav-link {
             color: #31363F;
             font-weight: 600;
             border: none;
             border-bottom: 3px solid transparent;
             transition: all 0.3s ease;
         }

         .nav-tabs .nav-link:hover {
             border-color: #667eea;
             color: #667eea;
         }

         .nav-tabs .nav-link.active {
             color: #667eea;
             border-color: #667eea;
             background: transparent;
         }

         .btn-download {
             background-color: #28a745;
             border: none;
             color: white;
             padding: 6px 15px;
             border-radius: 6px;
             transition: all 0.3s ease;
             margin-right: 5px;
         }

         .btn-download:hover {
             background-color: #218838;
             transform: translateY(-2px);
         }

         .file-icon {
             font-size: 1.5rem;
             margin-right: 10px;
         }

         .file-upload-section {
             background: white;
             border-radius: 10px;
             padding: 30px;
             box-shadow: 0 2px 10px rgba(0,0,0,0.1);
             margin-bottom: 30px;
         }

         .search-box {
             position: relative;
             margin-bottom: 20px;
         }

         .search-box input {
             padding: 12px 45px 12px 15px;
             border: 2px solid #e0e0e0;
             border-radius: 8px;
             width: 100%;
             transition: all 0.3s ease;
             font-size: 1rem;
         }

         .search-box input:focus {
             border-color: #667eea;
             outline: none;
             box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
         }

         .search-box i {
             position: absolute;
             right: 15px;
             top: 50%;
             transform: translateY(-50%);
             color: #666;
             font-size: 1.1rem;
         }

         .highlight {
             background-color: yellow;
             padding: 2px 4px;
             border-radius: 3px;
         }

         .no-results {
             text-align: center;
             padding: 30px;
             color: #6c757d;
             font-size: 1.1rem;
         }
     </style>
 </head>
 <body>
    <!-- Header -->
    <div class="header">
        <div class="container-main">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <img src="logo.png" alt="Logo" style="width: auto; height: 45px; margin-right: 15px;">
                    <div>
                        <h1 class="fs-3 mb-0">Records Management</h1>
                        <p class="mb-0" style="font-size: 0.9rem; opacity: 0.9;">Barangay San Carlos Information Management System</p>
                    </div>
                </div>
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-main">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="recordTabs" role="tablist">

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="file-uploads-tab" data-bs-toggle="tab" data-bs-target="#file-uploads" type="button" role="tab">
                    <i class="fas fa-file-upload me-2"></i>File Uploads
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="recordTabContent">
            <!-- Bad Records Tab -->
            <div class="tab-pane fade show active" id="bad-records" role="tabpanel">
        <!-- Upload Section -->
        <div class="upload-section">
            <h4 class="mb-3"><i class="fas fa-upload me-2"></i>Upload Records</h4>
            <p class="text-muted mb-4"></p>
            
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="d-flex align-items-center">
                    <div class="file-input-wrapper">
                        <input type="file" id="excelFile" name="excelFile" accept=".csv, .xlsx, .xls" required>
                        <label for="excelFile" class="file-input-label">
                            <i class="fas fa-file-excel me-2"></i>Choose File
                        </label>
                    </div>
                    <span class="file-name" id="fileName">No file chosen</span>
                    <button type="submit" class="upload-btn ms-auto">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Upload Records
                    </button>
                </div>
            </form>

            <div class="mt-3">
                <small class="text-muted">
                
                    
                </small>
                <br>
                <small class="text-muted">
                    <i class="fas fa-lightbulb me-1"></i>
                    <strong>Tip:</strong> If using Excel, you can convert to CSV (File > Save As > CSV) for better compatibility
                </small>
            </div>
        </div>

       
            <!-- End Bad Records Tab -->

            <!-- File Uploads Tab -->
            <div class="tab-pane fade" id="file-uploads" role="tabpanel">
                <!-- File Upload Section -->
                <div class="file-upload-section">
                    <h4 class="mb-3"><i class="fas fa-file-upload me-2"></i>Upload Excel/CSV File</h4>
                    <p class="text-muted mb-4">Upload an Excel or CSV file - data will be displayed in the table below</p>
                    
                    <form id="fileUploadForm" enctype="multipart/form-data">
                        <div class="d-flex align-items-center">
                            <div class="file-input-wrapper">
                                <input type="file" id="uploadFile" name="uploadFile" accept=".csv, .xlsx, .xls" required>
                                <label for="uploadFile" class="file-input-label">
                                    <i class="fas fa-file-excel me-2"></i>Choose File
                                </label>
                            </div>
                            <span class="file-name" id="selectedFileName">No file chosen</span>
                            <button type="submit" class="upload-btn ms-auto">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Upload & View Data
                            </button>
                        </div>
                    </form>

                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Supported formats: .csv, .xlsx, .xls
                        </small>
                    </div>
                </div>

                <!-- Files Data Table Section -->
                <div class="table-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4><i class="fas fa-table me-2"></i>Uploaded Files Data</h4>
                        <span class="badge bg-primary" style="font-size: 1rem; padding: 8px 15px;">
                            Total Files: <?php echo $result_files ? $result_files->num_rows : 0; ?>
                        </span>
                    </div>

                    <!-- Search Box -->
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search in all files..." onkeyup="searchTables()">
                        <i class="fas fa-search"></i>
                    </div>

                    <?php
                    if ($result_files && $result_files->num_rows > 0) {
                        while($file = $result_files->fetch_assoc()) {
                            $filePath = $file['file_path'];
                            $fileName = $file['original_name'];
                            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            
                            echo "<div class='file-container mb-4'>";
                            echo "<h5 class='text-primary'><i class='fas fa-file-excel me-2'></i>" . htmlspecialchars($fileName) . "</h5>";
                            echo "<small class='text-muted'>Uploaded: " . date('M d, Y h:i A', strtotime($file['created_at'])) . " | ";
                            echo "By: " . htmlspecialchars($file['uploaded_by'] ?? 'Admin') . "</small>";
                            echo "<button class='btn btn-delete btn-sm float-end' onclick='deleteFile(" . $file['id'] . ")'>";
                            echo "<i class='fas fa-trash me-1'></i>Delete</button>";
                            echo "<div class='table-responsive mt-3'>";
                            echo "<table class='table table-bordered table-sm searchable-table'>";
                            
                            // Read and display file content
                            if (file_exists($filePath)) {
                                if ($fileExtension == 'csv') {
                                    // Read CSV file
                                    $handle = fopen($filePath, "r");
                                    $rowNum = 0;
                                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                        if ($rowNum == 0) {
                                            // Header row
                                            echo "<thead class='table-dark'><tr>";
                                            foreach ($row as $cell) {
                                                echo "<th>" . htmlspecialchars($cell) . "</th>";
                                            }
                                            echo "</tr></thead><tbody>";
                                        } else {
                                            // Data rows
                                            echo "<tr>";
                                            foreach ($row as $cell) {
                                                echo "<td>" . htmlspecialchars($cell) . "</td>";
                                            }
                                            echo "</tr>";
                                        }
                                        $rowNum++;
                                        if ($rowNum > 100) break; // Limit to 100 rows
                                    }
                                    fclose($handle);
                                    echo "</tbody>";
                                } elseif (in_array($fileExtension, ['xls', 'xlsx'])) {
                                    // Try to read Excel file
                                    if (file_exists('vendor/autoload.php')) {
                                        require_once 'vendor/autoload.php';
                                        try {
                                            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                                            $worksheet = $spreadsheet->getActiveSheet();
                                            $rows = $worksheet->toArray();
                                            
                                            foreach ($rows as $rowNum => $row) {
                                                if ($rowNum == 0) {
                                                    // Header row
                                                    echo "<thead class='table-dark'><tr>";
                                                    foreach ($row as $cell) {
                                                        echo "<th>" . htmlspecialchars($cell) . "</th>";
                                                    }
                                                    echo "</tr></thead><tbody>";
                                                } else {
                                                    // Data rows
                                                    echo "<tr>";
                                                    foreach ($row as $cell) {
                                                        echo "<td>" . htmlspecialchars($cell) . "</td>";
                                                    }
                                                    echo "</tr>";
                                                }
                                                if ($rowNum > 100) break; // Limit to 100 rows
                                            }
                                            echo "</tbody>";
                                        } catch (Exception $e) {
                                            echo "<tbody><tr><td colspan='100' class='text-center text-danger'>";
                                            echo "Error reading Excel file: " . htmlspecialchars($e->getMessage());
                                            echo "</td></tr></tbody>";
                                        }
                                    } else {
                                        echo "<tbody><tr><td colspan='100' class='text-center text-warning'>";
                                        echo "PhpSpreadsheet library not installed. Please convert to CSV or install the library.";
                                        echo "</td></tr></tbody>";
                                    }
                                }
                            } else {
                                echo "<tbody><tr><td colspan='100' class='text-center text-danger'>";
                                echo "File not found: " . htmlspecialchars($filePath);
                                echo "</td></tr></tbody>";
                            }
                            
                            echo "</table>";
                            echo "</div>";
                            echo "</div>";
                            echo "<hr>";
                        }
                    } else {
                        echo "<div class='no-records'>";
                        echo "<i class='fas fa-inbox fa-3x mb-3' style='opacity: 0.3;'></i><br>";
                        echo "No files uploaded yet. Upload a CSV or Excel file to view its data here.";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            <!-- End File Uploads Tab -->
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="toastTitle">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                <!-- Message will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Display selected file name for bad records Excel
        document.getElementById('excelFile').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            document.getElementById('fileName').textContent = fileName;
        });

        // Display selected file name for file upload
        document.getElementById('uploadFile').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            document.getElementById('selectedFileName').textContent = fileName;
        });

        // Handle form submission
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('upload_bad_records.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Error', 'Failed to upload file', 'error');
                console.error('Error:', error);
            });
        });

        // Handle file upload form submission
        document.getElementById('fileUploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('upload_file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Error', 'Failed to upload file', 'error');
                console.error('Error:', error);
            });
        });

        // Search functionality
        function searchTables() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const tables = document.querySelectorAll('.searchable-table');
            let hasResults = false;

            tables.forEach(table => {
                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                const rows = tbody.querySelectorAll('tr');
                let tableHasResults = false;

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    let found = false;

                    cells.forEach(cell => {
                        const text = cell.textContent || cell.innerText;
                        if (text.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                        }
                    });

                    if (found || filter === '') {
                        row.style.display = '';
                        tableHasResults = true;
                        hasResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Hide/show the entire file container if no results
                const fileContainer = table.closest('.file-container');
                if (fileContainer) {
                    if (tableHasResults || filter === '') {
                        fileContainer.style.display = '';
                    } else {
                        fileContainer.style.display = 'none';
                    }
                }
            });

            // Show/hide no results message
            let noResultsMsg = document.getElementById('noResultsMessage');
            if (!hasResults && filter !== '') {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'noResultsMessage';
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.innerHTML = '<i class="fas fa-search fa-2x mb-2" style="opacity: 0.3;"></i><br>No results found for "' + filter + '"';
                    document.querySelector('.table-section').appendChild(noResultsMsg);
                } else {
                    noResultsMsg.innerHTML = '<i class="fas fa-search fa-2x mb-2" style="opacity: 0.3;"></i><br>No results found for "' + filter + '"';
                    noResultsMsg.style.display = 'block';
                }
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        }

        // Delete record function
        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete this record?')) {
                fetch('delete_bad_record.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success', data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('Error', 'Failed to delete record', 'error');
                    console.error('Error:', error);
                });
            }
        }

        // Show toast notification
        function showToast(title, message, type) {
            const toastElement = document.getElementById('toast');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');
            
            toastTitle.textContent = title;
            toastMessage.textContent = message;
            
            // Set color based on type
            const toastHeader = toastElement.querySelector('.toast-header');
            if (type === 'success') {
                toastHeader.style.backgroundColor = '#28a745';
                toastHeader.style.color = 'white';
            } else {
                toastHeader.style.backgroundColor = '#dc3545';
                toastHeader.style.color = 'white';
            }
            
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }

        // Delete file function
        function deleteFile(id) {
            if (confirm('Are you sure you want to delete this file?')) {
                fetch('delete_file.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success', data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('Error', 'Failed to delete file', 'error');
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>