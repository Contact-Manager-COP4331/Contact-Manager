<?php
session_start(); // Start session to identify logged-in user

// Make sure user is signed in
if (!isset($_SESSION['userId'])) {
    returnWithError("User not signed in.");
    exit;
}

$userId = $_SESSION['userId']; // signed-in user's ID

// Get input data from front end (JSON)
$inData = getRequestInfo();
$firstName = trim($inData["firstName"]);
$lastName  = trim($inData["lastName"]);
$phone     = trim($inData["phone"]);
$email     = trim($inData["email"]);

// ----------------------
// 1. Validate inputs
// ----------------------

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    returnWithError("Invalid email format.");
    exit;
}

// Validate phone number (digits, +, -, spaces, parentheses, 7-20 chars)
if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $phone)) {
    returnWithError("Invalid phone number format.");
    exit;
}

// ----------------------
// 2. Connect to database
// ----------------------
$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
if ($conn->connect_error) {
    returnWithError("Database connection failed: " . $conn->connect_error);
    exit;
}

// ----------------------
// 3. Check for duplicate contact
// ----------------------
$check = $conn->prepare(
    "SELECT ID FROM Contacts WHERE FirstName=? AND LastName=? AND Phone=? AND Email=? AND UserID=?"
);
$check->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userId);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    returnWithError("Contact already exists for this user.");
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// ----------------------
// 4. Insert new contact with current date
// ----------------------
$dateCreated = date("Y-m-d H:i:s"); // current date/time

$stmt = $conn->prepare(
    "INSERT INTO Contacts (FirstName, LastName, Phone, Email, UserID, DateCreated) VALUES (?,?,?,?,?,?)"
);
$stmt->bind_param("ssssis", $firstName, $lastName, $phone, $email, $userId, $dateCreated);

if ($stmt->execute()) {
    sendResultInfoAsJson('{"error":""}'); // success
} else {
    returnWithError("Insert failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

// ----------------------
// 5. Helper functions
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

