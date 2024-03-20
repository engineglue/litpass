<?php

	if(!isset($_SESSION['userID'])){
		//error
		header("Location: ./error.htm");
		exit;
	}
	
?>