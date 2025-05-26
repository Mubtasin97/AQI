<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.html");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aqi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_SESSION['email'];
$name = $_SESSION['name'];

$stmt = $conn->prepare("SELECT Color FROM users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userColor = "#ffffff"; 
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
    <title>AQI Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: <?php echo htmlspecialchars($userColor); ?>;
            font-family: Arial, sans-serif;
        }
        .header {
            width: 100%;
            background-color: #4A90E2;
            color: white;
            text-align: center;
            padding: 20px 0;
            font-size: 24px;
            font-weight: bold;
            position: relative;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 16px;
            background-color: #FF4D4D;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .logout-btn:hover {
            background-color: #D43F3F;
        }
        .dashboard-container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .dashboard-image {
            width: 100%;
            max-width: 400px;
            height: auto;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .welcome-message {
            font-size: 20px;
            color: #333;
            margin: 10px 0;
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
            margin-top: 20px;
        }
        .aqi-button:hover {
            background-color: #357ABD;
        }
    </style>
</head>
<body>
    <div class="header">
        Welcome to the AQI app
        <button class="logout-btn" onclick="logout()">Logout</button>
    </div>
    <div class="dashboard-container">
        <img src="default.png" alt="Default Image" class="dashboard-image">
        <div class="welcome-message">Welcome, <?php echo htmlspecialchars($name); ?>!</div>
        <button class="aqi-button" onclick="window.location.href='request.php'">Show AQI</button>
    </div>

    <script>
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