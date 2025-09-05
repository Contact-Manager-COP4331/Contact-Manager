<?php
    $inData = getRequestInfo();

    $firstName = $inData["firstName"];
    $lastName  = $inData["lastName"];
    $phone     = $inData["phone"];
    $email     = $inData["email"];
    $userId    = $inData["userId"];

    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");

    if ($conn->connect_error) 
    {
        returnWithError($conn->connect_error);
    } 
    else
    {
        // Update contact by matching FirstName + LastName + UserID
        $stmt = $conn->prepare("UPDATE Contacts 
                                SET Phone=?, Email=? 
                                WHERE FirstName=? AND LastName=? AND UserID=?");

        $stmt->bind_param("ssssi", $phone, $email, $firstName, $lastName, $userId);

        if ($stmt->execute())
        {
            if ($stmt->affected_rows > 0)
            {
                returnWithError(""); // success
            }
            else
            {
                returnWithError("No matching contact found or no changes made");
            }
        }
        else
        {
            returnWithError("Update failed");
        }

        $stmt->close();
        $conn->close();
    }

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

