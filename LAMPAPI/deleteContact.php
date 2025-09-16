<?php
	$inData = getRequestInfo();

	//Connects to database
	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
	if ($conn->connect_error) 
	{
		returnWithError($conn->connect_error);
	} 
	else
	{
		//Prepares and deletes a contact based on matching first name, last name, and userID
		$stmt = $conn->prepare("DELETE FROM Contacts WHERE FirstName = ? AND LastName = ? AND UserID=?");
		$stmt->bind_param("ssi", $inData["firstName"], $inData["lastName"], $inData["userId"]);

		if ($stmt->execute())
		{
			//Message to tell if contact was deleted or not based on if rows were affected
			if($stmt->affected_rows > 0)
			{
				returnWithInfo("Contact deleted");
			}
			else
			{
				returnWithInfo("No contact found");
			}
		}
		else
		{
			//Error handling for if execution failed
			returnWithError("Deletion failed");
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

	//Returns error response
	function returnWithError($err)
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson($retValue);
	}

	//Returns info response for successful deletion execute
	function returnWithInfo( $deleteResults )
	{
		$retValue = '{"results":"' . $deleteResults . '"}';
		sendResultInfoAsJson( $retValue );
	}
?>
