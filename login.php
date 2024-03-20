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
	
	//check if user exists
	$userExists = $db->querySingle("SELECT COUNT(*) FROM users WHERE email = '$email' ");
	
	//user doesn't exist
	if($userExists < 1){
		
		//tick access attempt
		$db->exec("UPDATE access_attempts SET access_count = access_count + 1 WHERE ip = '$ip' ");
		
		$output['result'] = "failed";
		$output['message'] = "User doesn't exist.";
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
		
	}
	
	//get password from db
	$userPassword = $db->querySingle("SELECT password FROM users WHERE email = '$email' ");

	//get salt from db
	$userSalt = $db->querySingle("SELECT salt FROM users WHERE email = '$email' ");
	
	
	
	
	if(empty($_SERVER['HTTPS'])){
		
		//non production:
		$saltedPassword = $password . $userSalt;
		
		//the client side javascript hash function doesn't work without SSL
		//and in a dev environment, the passwords are stored without hashing.
		//please make sure you use ssl in production and remember this if
		//you ever need to test a production database in a dev environment.
		
	} else {
		
		//salt password (production)
		$saltedPassword = hash('sha256', $password . $userSalt);
		
	}
	
	//authenticate
	if($saltedPassword == $userPassword){
		
		$userID = $db->querySingle("SELECT userID FROM users WHERE email = '$email' ");
		
		session_start();
		$_SESSION['userID'] = $userID;

		$output['result'] = "success";
		$output['userID'] = $userID;
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
		
	} else {

		//tick access attempt
		$db->exec("UPDATE access_attempts SET access_count = access_count + 1 WHERE ip = '$ip' ");
		
		$output['result'] = "failed";
		$output['message'] = "Authentication failed.";
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
		exit;
		
	}

//$day_exists = $db->querySingle("SELECT COUNT(*) FROM history WHERE date = $epoch_day");
//$db->exec("UPDATE history SET count = count + 1 WHERE date = $epoch_day ");
//$db->exec("INSERT INTO history (date, count) VALUES ($epoch_day, 1)");
	
	

?>