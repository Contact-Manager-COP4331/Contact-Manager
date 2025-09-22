<?php
session_start();

$inData = getRequestInfo();

$userId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : 0;
if ($userId <= 0 && isset($inData['userId']))
{
    $userId = (int)$inData['userId'];
}
if ($userId <= 0)
{
    returnWithError("Missing or invalid userId.");
    exit;
}

$originalFirstName = trim($inData['originalFirstName'] ?? '');
$originalLastName  = trim($inData['originalLastName'] ?? '');
$firstName         = trim($inData['firstName'] ?? '');
$lastName          = trim($inData['lastName'] ?? '');
$phone             = trim($inData['phone'] ?? '');
$email             = trim($inData['email'] ?? '');

if ($originalFirstName === '' || $originalLastName === '')
{
    returnWithError("Current first and last name are required.");
    exit;
}

if ($firstName === '' || $lastName === '' || $phone === '' || $email === '')
{
    returnWithError("All fields are required.");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
{
    returnWithError("* Invalid email address. Please use the format: user@example.com");
    exit;
}

if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $phone))
{
    returnWithError("* Invalid phone number. Example: 123-456-7890 of +1 (123) 456–7890.");
    exit;
}

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
if ($conn->connect_error)
{
    returnWithError("Database connection failed: " . $conn->connect_error);
    exit;
}

$now = date("Y-m-d H:i:s");
$stmt = $conn->prepare(
    "UPDATE Contacts
     SET FirstName = ?, LastName = ?, Phone = ?, Email = ?, UpdatedAt = ?
     WHERE FirstName = ? AND LastName = ? AND UserID = ?"
);
$stmt->bind_param(
    "sssssssi",
    $firstName,
    $lastName,
    $phone,
    $email,
    $now,
    $originalFirstName,
    $originalLastName,
    $userId
);

if ($stmt->execute())
{
    if ($stmt->affected_rows > 0)
    {
        sendResultInfoAsJson('{"error":""}');
    }
    else
    {
        returnWithError("No matching contact found or no changes made.");
    }
}
else
{
    returnWithError("Update failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err)
{
    $retValue = '{"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}
?>
