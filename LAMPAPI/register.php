<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get JSON input
$inData = getRequestInfo();

// Validate input
if (!$inData || !isset($inData["firstName"], $inData["lastName"], $inData["login"], $inData["password"])) {
    returnWithError("Invalid input. Please provide firstName, lastName, login, and password.");
    exit();
}

$firstName = $inData["firstName"];
$lastName  = $inData["lastName"];
$login     = $inData["login"];
$password  = $inData["password"];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Connect to database
$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
if ($conn->connect_error) {
    returnWithError("Database connection failed: " . $conn->connect_error);
    exit();
}

// Check if login already exists
$check = $conn->prepare("SELECT ID FROM Users WHERE Login = ?");
$check->bind_param("s", $login);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    returnWithError("Login already exists.");
    $check->close();
    $conn->close();
    exit();
}
$check->close();

// Prepare insert statement
$stmt = $conn->prepare("INSERT INTO Users (FirstName, LastName, Login, Password) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    returnWithError("Prepare failed: " . $conn->error);
    $conn->close();
    exit();
}

// Bind parameters and execute
$stmt->bind_param("ssss", $firstName, $lastName, $login, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(["message" => "User added successfully."]);
} else {
    returnWithError("Error inserting user: " . $stmt->error);
}

// Close connections
$stmt->close();
$conn->close();

// Functions
function getRequestInfo() {
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithError($err) {
    echo json_encode(["message" => $err]);
}
?>


