<?php

	date_default_timezone_set('America/Los_Angeles');
	
	//connect to db
	$dbPath = "./databases/user.db";
	$db = new SQLite3($dbPath);
	
	$ip = $_SERVER['REMOTE_ADDR'];
	if(!filter_var($ip, FILTER_VALIDATE_IP)){
		$ip = "0.0.0.0";
	}

	$epoch = time();
	$today = date("Y-m-d", $epoch);

	//verificationCode validation
	if(isset($_GET['vc'])){
		$verificationCode = $_GET['vc'];
	} else {
		$verificationCode = "";
	}
	
	if(strlen($verificationCode) < 1){
		echo "Unknown error occurred.";
		exit;
	}

	//get details
	$email = $db->querySingle("SELECT email FROM user_verification WHERE verification_code = '$verificationCode' ");
	$password = $db->querySingle("SELECT password FROM user_verification WHERE verification_code = '$verificationCode' ");
	$salt = $db->querySingle("SELECT salt FROM user_verification WHERE verification_code = '$verificationCode' ");
	
	//check if user exists
	$userExists = $db->querySingle("SELECT COUNT(*) FROM users WHERE email = '$email' ");
	
	//user doesn't exist
	if($userExists == 0){
		
		//move credentials into user table
		$db->exec("INSERT INTO users (email, password, salt) VALUES ('$email', '$password', '$salt')");
		
		$userID = $db->querySingle("SELECT userID FROM users WHERE email = '$email' ");
		
		session_start();
		$_SESSION['userID'] = $userID;
		
		header("Location: ./register_thankyou.php");
		
	} else {
		
		echo "Unknown error occurred.";
		
	}
	
	//delete verification link
	$db->exec("DELETE FROM user_verification where verification_code = '$verificationCode' ");

?>