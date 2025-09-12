<?php
session_start(); // Identify the logged-in user

// --- 0. Require login ---
if (!isset($_SESSION['userId'])) {
    returnWithError("User not signed in.");
    exit;
}

$userId = $_SESSION['userId'];

// --- 1. Read and validate input ---
$inData    = getRequestInfo();
$firstName = trim($inData["firstName"]);
$lastName  = trim($inData["lastName"]);
$phone     = trim($inData["phone"]);
$email     = trim($inData["email"]);

// Email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    returnWithError("Invalid email format.");
    exit;
}

// Phone number (digits, +, -, spaces, parentheses, 7–20 chars)
if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $phone)) {
    returnWithError("Invalid phone number format.");
    exit;
}

// --- 2. Connect to database ---
$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
if ($conn->connect_error) {
    returnWithError("Database connection failed: " . $conn->connect_error);
    exit;
}

// --- 3. Check for duplicates ---
$check = $conn->prepare(
    "SELECT ID FROM Contacts
     WHERE FirstName=? AND LastName=? AND Phone=? AND Email=? AND UserID=?"
);
$check->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userId);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    $conn->close();
    returnWithError("Contact already exists for this user.");
    exit;
}
$check->close();

// --- 4. Insert new contact with timestamps ---
$now = date("Y-m-d H:i:s"); // current date/time for both CreatedAt & UpdatedAt

$stmt = $conn->prepare(
    "INSERT INTO Contacts
     (FirstName, LastName, Phone, Email, UserID, CreatedAt, UpdatedAt)
     VALUES (?,?,?,?,?,?,?)"
);
$stmt->bind_param(
    "ssssiss",
    $firstName,
    $lastName,
    $phone,
    $email,
    $userId,
    $now,   // CreatedAt
    $now    // UpdatedAt (initially same as CreatedAt)
);

if ($stmt->execute()) {
    sendResultInfoAsJson('{"error":""}');
} else {
    returnWithError("Insert failed: " . $stmt->error);
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
