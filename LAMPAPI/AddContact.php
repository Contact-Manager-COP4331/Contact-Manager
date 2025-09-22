<?php

// --- 1. Read and validate input ---
$inData    = getRequestInfo();
$userId    = isset($inData["userId"]) ? (int)$inData["userId"] : 0;
if ($userId <= 0) {
    returnWithError("Missing or invalid userId.");
    exit;
}
$firstName = trim($inData["firstName"]);
$lastName  = trim($inData["lastName"]);
$phone     = trim($inData["phone"]);
$email     = trim($inData["email"]);

// Email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    returnWithError("* Invalid email address. Please use the format: user@example.com
");
    exit;
}

// Phone number (digits, +, -, spaces, parentheses, 7–20 chars)
if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $phone)) {
    returnWithError("*Invalid phone number. Example: 123-456-7890 of +1 (123) 456–7890.");
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
     WHERE FirstName=? AND LastName=? AND UserID=?"
);
$check->bind_param("ssi", $firstName, $lastName, $userId);
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
$now = date("Y-m-d H:i:s");

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
    $now,
    $now
);

if ($stmt->execute()) {
    sendResultInfoAsJson('{"error":""}');
} else {
    returnWithError("Insert failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

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

