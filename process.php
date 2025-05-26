<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aqi";

$conn = new mysqli($servername, $username, $password);

if (!$conn->select_db($dbname)) {
    echo json_encode(["error" => "Failed to select database: " . $conn->error]);
    exit;
}

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$conn->autocommit(true);

$sql = "CREATE TABLE IF NOT EXISTS users (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(32),
    Gender VARCHAR(10),
    Email VARCHAR(100) UNIQUE,
    Password VARCHAR(255),
    DOB DATE,
    Country VARCHAR(50),
    Opinion TEXT,
    Color VARCHAR(32)
)";
if (!$conn->query($sql)) {
    echo json_encode(["error" => "Table creation failed: " . $conn->error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'register') {
    $name = trim($_POST['fname'] ?? '');
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $email = trim($_POST['mail'] ?? '');
    $password = password_hash(trim($_POST['pass'] ?? ''), PASSWORD_DEFAULT);
    $dob = trim($_POST['birthday'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $opinion = trim($_POST['comment'] ?? '');
    $color = trim($_POST['colorPicker'] ?? '');

    
    $_SESSION['registration_data'] = [
        'name' => $name,
        'gender' => $gender,
        'email' => $email,
        'password' => $password,
        'dob' => $dob,
        'country' => $country,
        'opinion' => $opinion,
        'color' => $color
    ];

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration Summary</title>
        <style>
            body {
                margin: 0;
                padding: 20px;
                font-family: Arial, sans-serif;
                display: flex;
                flex-direction: column;
                align-items: center;
                background-color: #F5F6F5;
            }
            .summary-container {
                max-width: 600px;
                width: 100%;
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            h2 {
                text-align: center;
                color: #333;
            }
            .summary-item {
                margin: 10px 0;
            }
            .summary-item label {
                font-weight: bold;
            }
            .buttons {
                display: flex;
                justify-content: center;
                gap: 20px;
                margin-top: 20px;
            }
            button {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .confirm-btn {
                background-color: #4A90E2;
                color: white;
            }
            .goback-btn {
                background-color: #FF4D4D;
                color: white;
            }
            .hidden-password {
                display: inline-block;
                width: 100px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="summary-container">
            <h2>Registration Summary</h2>
            <div class="summary-item">
                <label>Name:</label> <?php echo htmlspecialchars($name); ?>
            </div>
            <div class="summary-item">
                <label>Gender:</label> <?php echo htmlspecialchars($gender); ?>
            </div>
            <div class="summary-item">
                <label>Email:</label> <?php echo htmlspecialchars($email); ?>
            </div>
            <div class="summary-item">
                <label>Password:</label>
                <span id="passwordField" class="hidden-password">********</span>
                <input type="checkbox" id="showPassword">
                <label for="showPassword">Show Password</label>
            </div>
            <div class="summary-item">
                <label>Date of Birth:</label> <?php echo htmlspecialchars($dob); ?>
            </div>
            <div class="summary-item">
                <label>Country:</label> <?php echo htmlspecialchars($country); ?>
            </div>
            <div class="summary-item">
                <label>Opinion:</label> <?php echo htmlspecialchars($opinion); ?>
            </div>
            <div class="summary-item">
                <label>Color:</label> <?php echo htmlspecialchars($color); ?>
            </div>
            <div class="buttons">
                <button class="confirm-btn">Confirm</button>
                <button class="goback-btn">Go Back</button>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'confirm_registration') {
    if (!isset($_SESSION['registration_data'])) {
        echo json_encode(["error" => "No registration data found"]);
        exit;
    }

    $data = $_SESSION['registration_data'];
    $name = $data['name'];
    $gender = $data['gender'];
    $email = $data['email'];
    $password = $data['password'];
    $dob = $data['dob'];
    $country = $data['country'];
    $opinion = $data['opinion'];
    $color = $data['color'];

    $errors = [];

    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($dob)) $errors[] = "Date of birth is required";
    if (empty($country)) $errors[] = "Country is required";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    $today = new DateTime('2025-05-15');
    $dobDate = new DateTime($dob);
    if ($dobDate > $today) {
        $errors[] = "Date of birth cannot be in the future";
    }

    if (!in_array($gender, ["Male", "Female"])) {
        $errors[] = "Invalid gender";
    }

    $validCountries = ["Bangladesh", "Pakistan", "Afghanistan", "United States", "Singapore", "Uganda", "Qatar"];
    if (!in_array($country, $validCountries)) {
        $errors[] = "Invalid country";
    }

    if (strlen($color) > 32) {
        $errors[] = "Color value too long";
    }

    if (!empty($errors)) {
        echo json_encode(["error" => implode("; ", $errors)]);
        exit;
    }

    $stmt = $conn->prepare("SELECT ID FROM users WHERE Name = ? AND DOB = ?");
    $stmt->bind_param("ss", $name, $dob);
    if (!$stmt->execute()) {
        echo json_encode(["error" => "Failed to check duplicates: " . $stmt->error]);
        $stmt->close();
        exit;
    }
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(["error" => "User already exists"]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO users (Name, Gender, Email, Password, DOB, Country, Opinion, Color) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $gender, $email, $password, $dob, $country, $opinion, $color);

    if ($stmt->execute()) {
        $affectedRows = $stmt->affected_rows;
        if ($affectedRows > 0) {
            $lastId = $conn->insert_id;
            $verifyStmt = $conn->prepare("SELECT * FROM users WHERE ID = ?");
            $verifyStmt->bind_param("i", $lastId);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            $insertedData = $verifyResult->fetch_assoc();
            if ($insertedData) {
                echo json_encode(["success" => true, "debug" => "Affected rows: $affectedRows, Last ID: $lastId"]);
            } else {
                echo json_encode(["error" => "Data not found in database after insert"]);
            }
            $verifyStmt->close();
        } else {
            echo json_encode(["error" => "No rows affected by insert"]);
        }
    } else {
        if ($conn->errno == 1062) {
            echo json_encode(["error" => "User already exists"]);
        } else {
            echo json_encode(["error" => "Failed to register: " . $stmt->error]);
        }
    }
    $stmt->close();
    unset($_SESSION['registration_data']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = trim($_POST['loginEmail']);
    $password = trim($_POST['loginPassword']);
    $errors = [];
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if (!empty($errors)) {
        echo json_encode(["error" => implode("; ", $errors)]);
        exit;
    }

    $stmt = $conn->prepare("SELECT Name, Password FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $row['Name'];
            echo json_encode(["success" => true, "name" => $row['Name']]);
        } else {
            echo json_encode(["error" => "Invalid email or password"]);
        }
    } else {
        echo json_encode(["error" => "Invalid email or password"]);
    }
    $stmt->close();
    exit;
}

echo json_encode(["error" => "Invalid request"]);
$conn->close();
?>