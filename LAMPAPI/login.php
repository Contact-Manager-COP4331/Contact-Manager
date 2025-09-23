<?php
session_start(); // Start session to store user info

$inData = getRequestInfo();

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");

if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare("SELECT ID, firstName, lastName, Password FROM Users WHERE Login=?");
    $stmt->bind_param("s", $inData["login"]);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($inData["password"], $row["Password"])) {
            // ✅ Store user info in session
            $_SESSION['userId'] = $row['ID'];
            $_SESSION['firstName'] = $row['firstName'];
            $_SESSION['lastName'] = $row['lastName'];

            // Return user info + login success message
            returnWithInfo($row["firstName"], $row["lastName"], $row["ID"], "Login successful");
        } else {
            returnWithError("Incorrect password");
        }
    } else {
        returnWithError("No records found");
    }

    $stmt->close();
    $conn->close();
}

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
    $retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '","message":""}';
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($firstName, $lastName, $id, $message = "") {
    $retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":"","message":"' . $message . '"}';
    sendResultInfoAsJson($retValue);
}
?>


