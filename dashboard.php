<?php
// Start session to check login status
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.html");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aqi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user's email from session
$email = $_SESSION['email'];

// Fetch the user's selected color
$stmt = $conn->prepare("SELECT Color FROM users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userColor = "#ffffff"; // Default color if not found
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userColor = $row['Color'] ?: "#ffffff";
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            background-color: <?php echo htmlspecialchars($userColor); ?>;
            font-family: Arial, sans-serif;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-top: 20px;
        }
        .aqi-button {
            background-color: #4A90E2;
            color: white;
            padding: 20px 40px;
            border: none;
            border-radius: 10px;
            font-size: 24px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .aqi-button:hover {
            background-color: #357ABD;
        }
        .logout-button {
            background-color: #FF4D4D;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }
        .logout-button:hover {
            background-color: #D93636;
        }
    </style>
</head>
<body>
    <h1>Welcome, <span id="userName"></span>!</h1>
    <button class="aqi-button" onclick="window.location.href='request.php'">Show AQI</button>
    <button class="logout-button" onclick="logout()">Logout</button>

    <script>
        // Display user name from localStorage
        const userName = localStorage.getItem('userName') || 'User';
        document.getElementById('userName').textContent = userName;

        // Logout function
        function logout() {
            fetch('logout.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    localStorage.removeItem('userName');
                    window.location.href = 'index.html';
                }
            })
            .catch(error => {
                alert('Error during logout: ' + error);
            });
        }
    </script>
</body>
</html>