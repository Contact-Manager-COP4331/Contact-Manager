<?php

	$inData = getRequestInfo();
	
	$searchResults = "";
	$searchCount = 0;

	//Connects to database
	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP433119");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		//Binds parameters
		$stmt = $conn->prepare("SELECT * FROM Contacts WHERE (FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ? OR Email LIKE ? OR CreatedAt LIKE ? OR UpdatedAt LIKE ?) AND UserID=?");
		$search = "%" . $inData["search"] . "%";
		$stmt->bind_param("ssssssi", $search, $search, $search, $search, $search, $search, $inData["userId"]);
		$stmt->execute();
		
		$result = $stmt->get_result();

		//Loops through all matching rows
		while($row = $result->fetch_assoc())
		{
			//Adds comma between search results
			if( $searchCount > 0 )
			{
				$searchResults .= ",";
			}
			$searchCount++;
			//Appends full contact data to search results
			$searchResults .= '{"firstName" : "' . $row["FirstName"]. '", 
                          "lastName" : "' . $row["LastName"]. '",
                          "phone" : "' . $row["Phone"]. '",
                          "email" : "' . $row["Email"]. '",
						  "createdAt" : "' . $row["CreatedAt"]. '",
						  "updatedAt" : "' . $row["UpdatedAt"]. '"}';
		}

		//Returns results depending on if the search exists in the database
		if( $searchCount == 0 )
		{
			returnWithError( "No Contact Found" );
		}
		else
		{
			returnWithInfo( $searchResults );
		}
		
		$stmt->close();
		$conn->close();
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}

	//Returns error response
	function returnWithError( $err )
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

	//Returns successful contact from search
	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>
