<?php

	$_POST = json_decode(file_get_contents('php://input'), true);

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
	
	//maximum failed access attempt count & reset duration in epoch
	$accessFail = 15;
	$accessResetDuration = 3600; //3600 = 1hr
	
	//create access record if it doesn't exist
	$accessRecord = $db->querySingle("SELECT COUNT(*) FROM access_attempts WHERE ip = '$ip' ");
	
	if($accessRecord < 1){
		$db->exec("INSERT INTO access_attempts (ip, epoch_reset, count) VALUES ('$ip', $epoch, 0)");
	}
	
	//check failed access count and reset datetime
	$accessCount = $db->querySingle("SELECT access_count FROM access_attempts WHERE ip = '$ip' ");
	$accessReset = $db->querySingle("SELECT epoch_reset FROM access_attempts WHERE ip = '$ip' ");
	
	//restrict access
	if($accessCount >= $accessFail && $accessReset > $epoch){
		
		$epochReset = $epoch + $accessResetDuration;
		
		//udate datetime in epoch seconds
		$db->exec("UPDATE access_attempts SET epoch_reset = $epochReset WHERE ip = '$ip' ");
		
		$output['result'] = "failed";
		$output['message'] = "Too many failed access attempts.";
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
		
		
	}
	
	//reset time elapsed
	if($accessCount >= $accessFail && $accessReset < $epoch){
		
		//udate count
		$db->exec("UPDATE access_attempts SET access_count = 0 WHERE ip = '$ip' ");

	}
	
	//tick access attempt
	$db->exec("UPDATE access_attempts SET access_count = access_count + 1 WHERE ip = '$ip' ");
	
	$defaultWarnings = '';
	
	$output = array('result' => 'failed', 'message' => $defaultWarnings);
	
	//email validation
	if(isset($_POST['emailAddress'])){
		$email = $_POST['emailAddress'];
	} else {
		$email = "";
	}

	$email = filter_var($email, FILTER_SANITIZE_EMAIL);

	if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
		$email = "";
	}

	if(strlen($email) < 5){
		
		$output['result'] = "failed";
		$output['message'] = "An email address is required.";
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
		
	}
	
	//password validation
	if(isset($_POST['password'])){
		$password = $_POST['password'];
	} else {
		$password = "";
	}
	
	$password = filter_var($password, FILTER_SANITIZE_URL);
	
	if(strlen($password) < 1){
		
		$output['result'] = "failed";
		$output['message'] = "A password is required.";
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
		
	}
	
	//salt verification
	if(isset($_POST['salt'])){
		$salt = $_POST['salt'];
	} else {
		$salt = "";
	}
	
	if(strlen($salt) < 1){
		
		$output['result'] = "failed";
		$output['message'] = "There was an unknown error.";
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
		
	}
	
	//check if user exists
	$userExists = $db->querySingle("SELECT COUNT(*) FROM users WHERE email = '$email' ");
	
	//user exists
	if($userExists > 0){
		
		$output['result'] = "failed";
		$output['message'] = "User exists.";
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
		
	}
	
	$verificationCode = hash("sha256", rand(10000, 99999) . $epoch . "whatever");
	
	//check if email in verification table
	$verifyExists = $db->querySingle("SELECT COUNT(*) FROM user_verification WHERE email = '$email' ");
	
	if($verifyExists > 0){
		
		//update record in verification list
		$db->exec("UPDATE user_verification SET password = '$password',
												salt = '$salt',
												verification_code = '$verificationCode'
													WHERE email = '$email' ");
		
	} else {
		
		//add record to verification list
		$db->exec("INSERT INTO user_verification (email, password, salt, verification_code) VALUES ('$email', '$password', '$salt', '$verificationCode')");
		
	}

	$verificationLink = "./verify_link.php?vc=$verificationCode";
	$checkEmailLink = "./check_email.htm";

	//email the user
	//mail($email, 'Website Verification Link', $verificationLink);
	//$output['result'] = "success";
	//$output['message'] = $checkEmailLink;

	//or just post it to the screen
	$output['result'] = "success";
	$output['message'] = $verificationLink;
	
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($output);
	exit


?>