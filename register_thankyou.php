<?php

	session_start();
	
	//require("./validate_user.php");

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>LitPass Authentication Example</title>
		<link rel="icon" href="./images/icon.png" type="image/x-icon">
	</head>
	<body bgcolor="#f9f9f9">
		<main>
		
			<h1 style="font-size:18px;font-family:'Arial';color:#333;">Thank you for registering</h1>
			
			<hr><br>
			
			<input type="button" value="Logout" 
						style="	width:75px;
								margin-right:-2px;
								padding:5px 8px 5px 8px;
								font-size:16px;"
						onclick="window.location.href = './logout.php';">

		</main>

	</body>
</html>