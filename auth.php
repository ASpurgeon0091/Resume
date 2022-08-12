<?php

include("mysql.php");

$ip = getenv('REMOTE_ADDR');

session_start();

if (isset($_SESSION['SessionID'])==TRUE) {
	$SessionID = $mysqli->	real_escape_string($_SESSION['SessionID']);


	$result= $mysqli->query("SELECT * FROM customer_sessions 	WHERE sessionid='$SessionID' LIMIT 1") 
             or die ("auth.1:".$mysqli->error);
	$sessioninfo= mysqli_fetch_array($result);
	$Username=$sessioninfo['email'];
	$Password=$sessioninfo['password'];


	
	$valid=0;
	$result= $mysqli->query("SELECT * FROM `customer_accounts` WHERE email='$Username' LIMIT 1") 
             or die ("auth.2:".$mysqli->error);
	$timeout=time()-1800;
	$userinfo=mysqli_fetch_array($result);
		if ($sessioninfo['last_activity'] < $timeout) 		{     // This determines if the user has been inactive for 		greater than 30 mins
		$mysqli->query("DELETE FROM customer_sessions WHERE sessionid='$SessionID' LIMIT 1"); // Lets delete this 	session cause its expired
		$valid=0; // Session is not valid, request user to re		 sign-in
		
	} else {     //   Session is still valid so verify its authenticity
		
		if ($Password==$userinfo['password'] && $Password != "") { 
			$valid = 1; // User is logged in if set to 1
			$mysqli->query("UPDATE customer_accounts SET last_activity='".time()."' WHERE email='$Username' LIMIT 1"); 
			$mysqli->query("UPDATE customer_sessions SET last_activity='".time()."' WHERE email='$Username' LIMIT 1");
		}
	}
}


?>