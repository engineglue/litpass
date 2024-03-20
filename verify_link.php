<?php

	//verificationCode validation
	if(isset($_GET['vc'])){
		$verificationCode = $_GET['vc'];
	} else {
		$verificationCode = "";
	}
	
	if(strlen($verificationCode) < 1){
		//error
		header("Location: ./error.htm");
		exit;
	}
	
	$verifyLink = "./verify.php?vc=$verificationCode";

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
		
			<h1 style="font-size:18px;font-family:'Arial';color:#333;">"Emailed" verification code</h1>
			
			<hr><br>
			
			<a href="<?php echo $verifyLink; ?>"><?php echo $verifyLink; ?></a>

		</main>

	</body>
</html>