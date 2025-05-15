<?php
// Start session
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fallback logging to a file
$logFile = 'C:/xampp/htdocs/aqi_log.txt';
file_put_contents($logFile, "Log started at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aqi";

$conn = new mysqli($servername, $username, $password);

// Explicitly select the database
if (!$conn->select_db($dbname)) {
    file_put_contents($logFile, "Failed to select database: " . $conn->error . "\n", FILE_APPEND);
    die(json_encode(["error" => "Failed to select database: " . $conn->error]));
}

// Check connection
if ($conn->connect_error) {
    file_put_contents($logFile, "Connection failed: " . $conn->connect_error . "\n", FILE_APPEND);
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Ensure auto-commit is enabled
$conn->autocommit(true);

// Create table if it doesn't exist
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
    file_put_contents($logFile, "Table creation failed: " . $conn->error . "\n", FILE_APPEND);
    die(json_encode(["error" => "Table creation failed: " . $conn->error]));
}
file_put_contents($logFile, "Table creation successful or table already exists\n", FILE_APPEND);

// Handle registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'register') {
    file_put_contents($logFile, "Received POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

    $name = trim($_POST['fname'] ?? '');
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $email = trim($_POST['mail'] ?? '');
    $password = password_hash(trim($_POST['pass'] ?? ''), PASSWORD_DEFAULT);
    $dob = trim($_POST['birthday'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $opinion = trim($_POST['comment'] ?? '');
    $color = trim($_POST['colorPicker'] ?? '');

    // Server-side validation
    $errors = [];

    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($dob)) $errors[] = "Date of birth is required";
    if (empty($country)) $errors[] = "Country is required";

    // Email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // DOB not in future
    $today = new DateTime('2025-05-15');
    $dobDate = new DateTime($dob);
    if ($dobDate > $today) {
        $errors[] = "Date of birth cannot be in the future";
    }

    // Gender validation
    if (!in_array($gender, ["Male", "Female"])) {
        $errors[] = "Invalid gender";
    }

    // Country validation
    $validCountries = ["Bangladesh", "Pakistan", "Afghanistan", "United States", "Singapore", "Uganda", "Qatar"];
    if (!in_array($country, $validCountries)) {
        $errors[] = "Invalid country";
    }

    // Color length
    if (strlen($color) > 32) {
        $errors[] = "Color value too long";
    }

    if (!empty($errors)) {
        file_put_contents($logFile, "Validation errors: " . implode("; ", $errors) . "\n", FILE_APPEND);
        echo json_encode(["error" => implode("; ", $errors)]);
        exit;
    }

    // Check for duplicate Name + DOB
    $stmt = $conn->prepare("SELECT ID FROM users WHERE Name = ? AND DOB = ?");
    $stmt->bind_param("ss", $name, $dob);
    if (!$stmt->execute()) {
        file_put_contents($logFile, "Check duplicate query failed: " . $stmt->error . "\n", FILE_APPEND);
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

    // Insert data
    $stmt = $conn->prepare("INSERT INTO users (Name, Gender, Email, Password, DOB, Country, Opinion, Color) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $gender, $email, $password, $dob, $country, $opinion, $color);

    // Log the query for debugging
    $queryLog = "INSERT INTO users (Name, Gender, Email, Password, DOB, Country, Opinion, Color) VALUES ('$name', '$gender', '$email', '$password', '$dob', '$country', '$opinion', '$color')";
    file_put_contents($logFile, "Executing query: " . $queryLog . "\n", FILE_APPEND);

    if ($stmt->execute()) {
        // Check affected rows
        $affectedRows = $stmt->affected_rows;
        file_put_contents($logFile, "Affected rows: $affectedRows\n", FILE_APPEND);

        if ($affectedRows > 0) {
            // Get the last inserted ID
            $lastId = $conn->insert_id;
            file_put_contents($logFile, "Last inserted ID: $lastId\n", FILE_APPEND);

            // Verify data
            $verifyStmt = $conn->prepare("SELECT * FROM users WHERE ID = ?");
            $verifyStmt->bind_param("i", $lastId);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            $insertedData = $verifyResult->fetch_assoc();
            file_put_contents($logFile, "Inserted data: " . print_r($insertedData, true) . "\n", FILE_APPEND);

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
            file_put_contents($logFile, "Insert failed: " . $stmt->error . "\n", FILE_APPEND);
            echo json_encode(["error" => "Failed to register: " . $stmt->error]);
        }
    }
    $stmt->close();
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = trim($_POST['loginEmail']);
    $password = trim($_POST['loginPassword']);

    // Server-side validation for login
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
            // Set session variables
            $_SESSION['loggedin'] = true;
            $_SESSION['email'] = $email;
            echo json_encode(["success" => true, "name" => $row['Name']]);
        } else {
            echo json_encode(["error" => "Invalid email or password"]);
        }
    } else {
        echo json_encode(["error" => "Invalid email or password"]);
    }
    $stmt->close();
}

$conn->close();
?>