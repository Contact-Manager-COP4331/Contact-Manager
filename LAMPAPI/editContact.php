<?php
session_start(); // Identify the logged-in user

// --- 0. Require login ---
if (!isset($_SESSION['userId'])) {
    returnWithError("User not signed in.");
    exit;
}

$userId = $_SESSION['userId']; // signed-in user's ID

// --- 1. Read and trim input ---
$inData    = getRequestInfo();
$firstName = trim($inData["firstName"] ?? "");
$lastName  = trim($inData["lastName"]  ?? "");
$phone     = trim($inData["phone"]     ?? "");
$email     = trim($inData["email"]     ?? "");

// --- 2. Validate inputs ---
if ($firstName === "" || $lastName === "" || $phone === "" || $email === "") {
    returnWithError("All fields are required.");
    exit;
}

// Email must be a valid RFC-compliant address
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    returnWithError("Invalid email format.");
    exit;
}

// Phone: allow digits, +, -, spaces, parentheses, length 7–20
if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $phone)) {
    returnWithError("Invalid phone number format.");
    exit;
}

// --- 3. Connect to database ---
$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
if ($conn->connect_error) {
    returnWithError("Database connection failed: " . $conn->connect_error);
    exit;
}

// --- 4. Update contact and set UpdatedAt ---
$now = date("Y-m-d H:i:s");
$stmt = $conn->prepare(
    "UPDATE Contacts
     SET Phone = ?, Email = ?, UpdatedAt = ?
     WHERE FirstName = ? AND LastName = ? AND UserID = ?"
);
$stmt->bind_param("sssssi", $phone, $email, $now, $firstName, $lastName, $userId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        sendResultInfoAsJson('{"error":""}');
    } else {
        returnWithError("No matching contact found or no changes made.");
    }
} else {
    returnWithError("Update failed: " . $stmt->error);
}

$stmt->close();
$conn->close();


// ----------------------
// Helper functions
// ----------------------
function getRequestInfo() {
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj) {
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err) {
    $retValue = '{"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}
?>
