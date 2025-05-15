<?php
// Start session to check access
session_start();

// Check if user is logged in and coming from request.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'request.php') === false) {
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

// Get selected city IDs
$cityIds = isset($_POST['cities']) && is_array($_POST['cities']) ? $_POST['cities'] : [];
if (count($cityIds) !== 10) {
    die("Please select exactly 10 cities.");
}

// Fetch data for selected cities
$placeholders = implode(',', array_fill(0, count($cityIds), '?'));
$stmt = $conn->prepare("SELECT ID, Country, City, AQI FROM info WHERE ID IN ($placeholders)");
$stmt->bind_param(str_repeat('i', count($cityIds)), ...$cityIds);
$stmt->execute();
$result = $stmt->get_result();
$selectedCities = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $selectedCities[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AQI of Selected Cities</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background-color: #F5F6F5;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .aqi-table {
            width: 80%;
            max-width: 800px;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .aqi-table th, .aqi-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        .aqi-table th {
            background: #4A90E2;
            color: white;
        }
        .aqi-table tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <h1>AQI of Selected Cities</h1>
    <table class="aqi-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Country</th>
                <th>City</th>
                <th>AQI</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($selectedCities as $city): ?>
                <tr>
                    <td><?php echo htmlspecialchars($city['ID']); ?></td>
                    <td><?php echo htmlspecialchars($city['Country']); ?></td>
                    <td><?php echo htmlspecialchars($city['City']); ?></td>
                    <td><?php echo htmlspecialchars($city['AQI']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>