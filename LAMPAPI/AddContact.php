<?php
$inData = getRequestInfo();

$firstName = $inData["firstName"];
$lastName  = $inData["lastName"];
$phone     = $inData["phone"];
$email     = $inData["email"];
$userId    = $inData["userId"];

// 1. Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    returnWithError("Invalid email format.");
    exit;
}

// 2. Validate phone
if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $phone)) {
    returnWithError("Invalid phone number format.");
    exit;
}

// 3. Validate userId
if (!filter_var($userId, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
    returnWithError("Invalid user ID.");
    exit;
}

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
if ($conn->connect_error) {
    returnWithError($conn->connect_error);
    exit;
}

// 4. Check if user exists
$stmt = $conn->prepare("SELECT ID FROM Users WHERE ID = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    returnWithError("User ID does not exist.");
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// 5. Check if contact exists
$check = $conn->prepare("SELECT ID FROM Contacts WHERE FirstName=? AND LastName=? AND Phone=? AND Email=? AND UserID=?");
$check->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userId);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    returnWithError("Contact already exists");
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// 6. Insert new contact
$stmt = $conn->prepare("INSERT INTO Contacts (FirstName, LastName, Phone, Email, UserID) VALUES (?,?,?,?,?)");
$stmt->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userId);

if ($stmt->execute()) {
    returnWithError(""); // success = no error
} else {
    returnWithError("Insert failed");
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
