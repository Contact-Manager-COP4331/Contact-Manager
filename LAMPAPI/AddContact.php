<?php
// --- 1. Read and validate input ---
$inData = getRequestInfo();
$userId = isset($inData["userId"]) ? (int)$inData["userId"] : 0;

if ($userId <= 0) {
    returnWithError("Missing or invalid userId.");
}

// Trim input fields
$firstName = trim($inData["firstName"] ?? '');
$lastName  = trim($inData["lastName"] ?? '');
$phone     = trim($inData["phone"] ?? '');
$email     = trim($inData["email"] ?? '');

// Validate required fields
if (!$firstName || !$lastName || !$phone || !$email) {
    returnWithError("All fields (first name, last name, phone, email) are required.");
}

// --- 2. Validate email ---
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    returnWithError("*Invalid email address. Example: user@example.com");
}

if (!preg_match('/^\+\d{1,3} \(\d{2,4}\) \d{3}-\d{4}$/', $phone)) {
    returnWithError("*Invalid phone number. Example: +1 (123) 456-7890");
    exit;
}

// --- 4. Connect to database ---
$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
if ($conn->connect_error) {
    returnWithError("Database connection failed: " . $conn->connect_error);
}

// --- 5. Check for duplicate contact ---
$check = $conn->prepare(
    "SELECT ID FROM Contacts WHERE FirstName=? AND LastName=? AND UserID=?"
);
$check->bind_param("ssi", $firstName, $lastName, $userId);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    $conn->close();
    returnWithError("Contact already exists for this user.");
}
$check->close();

// --- 6. Insert new contact ---
$now = date("Y-m-d H:i:s");

$stmt = $conn->prepare(
    "INSERT INTO Contacts (FirstName, LastName, Phone, Email, UserID, CreatedAt, UpdatedAt) 
     VALUES (?, ?, ?, ?, ?, ?, ?)"
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
    sendResultInfoAsJson(["error" => ""]); // success
} else {
    returnWithError("Insert failed: " . $stmt->error);
}

// Close connections
$stmt->close();
$conn->close();

// --- Functions ---
function getRequestInfo() {
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj) {
    header('Content-Type: application/json');
    echo json_encode($obj);
    exit();
}

function returnWithError($err) {
    sendResultInfoAsJson(["error" => $err]);
}
?>


