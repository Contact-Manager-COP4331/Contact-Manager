<?php
	$inData = getRequestInfo();

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
	if ($conn->connect_error) 
	{
		returnWithError($conn->connect_error);
	} 
	else
	{
		$stmt = $conn->prepare("DELETE FROM Contacts WHERE FirstName LIKE ? AND LastName LIKE ? AND UserID=?");
		$stmt->bind_param("ssi", $inData["firstName"], $inData["lastName"], $inData["userId"]);

		if ($stmt->execute())
		{
			//message to tell if contact was deleted or not
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
	
	function returnWithError($err)
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson($retValue);
	}

	function returnWithInfo( $deleteResults )
	{
		$retValue = '{"results":"' . $deleteResults . '"}';
		sendResultInfoAsJson( $retValue );
	}
?>
