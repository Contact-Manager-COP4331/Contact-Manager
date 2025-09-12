<?php
    $inData = getRequestInfo();
    
    $firstName = $inData["firstName"];
    $lastName  = $inData["lastName"];
    $phone     = $inData["phone"];
    $email     = $inData["email"];
    $userId    = $inData["userId"];
    

    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Valid email format.";
    } else {
        echo "Invalid email format.";
    if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $phone)) {
        returnWithError("Invalid phone number format.");
    exit;
}
    if ($conn->connect_error) 
    {
        returnWithError($conn->connect_error);
    } 
    else
    {
        // 1. Check if this contact already exists for the same user
        $check = $conn->prepare("SELECT ID FROM Contacts WHERE FirstName=? AND LastName=? AND Phone=? AND Email=? AND UserID=?");
        $check->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0)
        {
            returnWithError("Contact already exists");
            $check->close();
        }
        else
        {
            $check->close();

            // 2. Insert new contact
            $stmt = $conn->prepare("INSERT INTO Contacts (FirstName, LastName, Phone, Email, UserID) VALUES (?,?,?,?,?)");
            $stmt->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userId);

            if ($stmt->execute())
            {
                returnWithError(""); // success = no error
            }
            else
            {
                returnWithError("Insert failed");
            }

            $stmt->close();
        }

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
