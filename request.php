<?php
// Start session to check access
session_start();

// Check if user is logged in and coming from dashboard.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'dashboard.php') === false) {
    header("Location: index.html");
    exit;
}

$name = $_SESSION['name'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aqi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cities from the info table (read-only, no updates allowed)
$result = $conn->query("SELECT ID, City FROM info");
$cities = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row;
    }
}
// Note: info table is static with 20 cities and should not be modified
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select 10 Cities</title>
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
        .header {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            padding: 10px;
            position: absolute;
            top: 0;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .city-list {
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .city-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .city-item:last-child {
            border-bottom: none;
        }
        .submit-button {
            background-color: #4A90E2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .submit-button:hover {
            background-color: #357ABD;
        }
        .submit-button:disabled {
            background-color: #A3CFFA;
            cursor: not-allowed;
        }
        .warning {
            color: red;
            text-align: center;
            margin-top: 10px;
            font-size: 16px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <span>Welcome, <?php echo htmlspecialchars($name); ?>!</span>
        <button onclick="logout()" style="margin-left: 10px;">Logout</button>
    </div>
    <h1>Select 10 Cities</h1>
    <form id="cityForm" action="showaqi.php" method="POST">
        <div class="city-list">
            <?php foreach ($cities as $city): ?>
                <div class="city-item">
                    <span><?php echo htmlspecialchars($city['ID']); ?> - <?php echo htmlspecialchars($city['City']); ?></span>
                    <input type="checkbox" name="cities[]" value="<?php echo htmlspecialchars($city['ID']); ?>" onchange="checkSelection()">
                </div>
            <?php endforeach; ?>
        </div>
        <div id="selectionWarning" class="warning"></div>
        <button type="submit" class="submit-button" id="submitButton" disabled>Submit</button>
    </form>

    <script>
        function checkSelection() {
            const checkboxes = document.querySelectorAll('input[name="cities[]"]:checked');
            const submitButton = document.getElementById('submitButton');
            const warningDiv = document.getElementById('selectionWarning');
            const count = checkboxes.length;

            if (count === 10) {
                submitButton.disabled = false;
                warningDiv.style.display = 'none';
                warningDiv.textContent = '';
            } else {
                submitButton.disabled = true;
                warningDiv.style.display = 'block';
                if (count < 10) {
                    warningDiv.textContent = `Please select ${10 - count} more cities.`;
                } else {
                    warningDiv.textContent = `You have selected ${count - 10} cities too many.`;
                    checkboxes[checkboxes.length - 1].checked = false;
                    checkSelection();
                }
            }
        }

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