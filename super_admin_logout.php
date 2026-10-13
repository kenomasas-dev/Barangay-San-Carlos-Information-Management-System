<?php
session_start();

// Include logging function if it exists
if (file_exists('log_activity.php')) {
    require_once 'log_activity.php';
}

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barangay";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Store username before destroying session
$logged_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown User';
$is_superadmin = ($logged_user === 'superadmin');

// Log the logout activity if possible
if ($conn->connect_error === null) {
    // Log to activity_logs table (for backward compatibility)
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (username, action, log_time) VALUES (?, 'logout', NOW())");
    if ($log_stmt) {
        $log_stmt->bind_param("s", $logged_user);
        $log_stmt->execute();
        $log_stmt->close();
    }
    
    // Log to admin_activity_logs if function exists
    if (function_exists('logAdminActivity')) {
        $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
        logAdminActivity(
            $logged_user,
            "Admin logged out",
            "Logout",
            "Logout at " . date('F j, Y - g:i A'),
            null,
            $admin_id
        );
    }
}

// Close database connection
if ($conn) {
    $conn->close();
}

// Destroy all session data
session_unset();
session_destroy();

// Always redirect to super admin login page
$redirect_page = 'super_admin_login.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logout-container {
            text-align: center;
            background: white;
            padding: 50px 60px;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .logout-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(102, 126, 234, 0);
            }
        }

        .logout-icon i {
            font-size: 50px;
            color: white;
        }

        h2 {
            font-size: 32px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }

        p {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }

        .username {
            font-size: 18px;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 25px;
        }

        /* Loading Animation */
        .loader-container {
            margin: 30px 0 20px;
        }

        .loader {
            width: 60px;
            height: 60px;
            border: 6px solid #f3f3f3;
            border-top: 6px solid #667eea;
            border-radius: 50%;
            margin: 0 auto;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Progress Bar */
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 25px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            animation: progress 3s ease-out forwards;
        }

        @keyframes progress {
            to { width: 100%; }
        }

        .redirect-text {
            font-size: 14px;
            color: #999;
            margin-top: 20px;
        }

        /* Success Icon Animation */
        .checkmark-container {
            display: none;
            margin: 20px auto;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 3;
            stroke: #4caf50;
            stroke-miterlimit: 10;
            box-shadow: inset 0px 0px 0px #4caf50;
            animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
        }

        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 3;
            stroke-miterlimit: 10;
            stroke: #4caf50;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        @keyframes stroke {
            100% { stroke-dashoffset: 0; }
        }

        @keyframes scale {
            0%, 100% { transform: none; }
            50% { transform: scale3d(1.1, 1.1, 1); }
        }

        @keyframes fill {
            100% { box-shadow: inset 0px 0px 0px 30px #4caf50; }
        }

        .farewell-message {
            font-size: 15px;
            color: #555;
            margin-top: 15px;
            line-height: 1.6;
        }

        @media (max-width: 480px) {
            .logout-container {
                padding: 40px 30px;
                margin: 20px;
            }

            h2 {
                font-size: 26px;
            }

            .logout-icon {
                width: 80px;
                height: 80px;
            }

            .logout-icon i {
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        
        <h2>Logging Out</h2>
        <p class="username">Goodbye, <?php echo htmlspecialchars($logged_user); ?>!</p>
        <p class="farewell-message">
            Your session has been successfully terminated.<br>
            Thank you for using the system.
        </p>

        <div class="loader-container">
            <div class="loader"></div>
        </div>

        <div class="checkmark-container" id="checkmarkContainer">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>

        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>

        <p class="redirect-text">Redirecting to login page...</p>
    </div>

    <script>
        // Show checkmark after 1.5 seconds
        setTimeout(() => {
            document.querySelector('.loader-container').style.display = 'none';
            document.getElementById('checkmarkContainer').style.display = 'block';
        }, 1500);

        // Redirect after 3 seconds
        setTimeout(() => {
            window.location.href = '<?php echo $redirect_page; ?>';
        }, 3000);

        // Prevent back button
        window.history.pushState(null, '', window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, '', window.location.href);
        };
    </script>
</body>
</html>