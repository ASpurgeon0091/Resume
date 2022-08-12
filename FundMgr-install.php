<?php

ini_set('display_errors',0);

$ip = getenv('REMOTE_ADDR');


require_once("libs/Smarty.class.php");
$smarty = new Smarty();

$perms = fileperms('vars.php');

if (($perms & 0xC000) == 0xC000) {
   // Socket
   $info = 's';
} elseif (($perms & 0xA000) == 0xA000) {
   // Symbolic Link
   $info = 'l';
} elseif (($perms & 0x8000) == 0x8000) {
   // Regular
   $info = '-';
} elseif (($perms & 0x6000) == 0x6000) {
   // Block special
   $info = 'b';
} elseif (($perms & 0x4000) == 0x4000) {
   // Directory
   $info = 'd';
} elseif (($perms & 0x2000) == 0x2000) {
   // Character special
   $info = 'c';
} elseif (($perms & 0x1000) == 0x1000) {
   // FIFO pipe
   $info = 'p';
} else {
   // Unknown
   $info = 'u';
}

// Owner
$info .= (($perms & 0x0100) ? 'r' : '-');
$info .= (($perms & 0x0080) ? 'w' : '-');
$info .= (($perms & 0x0040) ?
           (($perms & 0x0800) ? 's' : 'x' ) :
           (($perms & 0x0800) ? 'S' : '-'));

// Group
$info .= (($perms & 0x0020) ? 'r' : '-');
$info .= (($perms & 0x0010) ? 'w' : '-');
$info .= (($perms & 0x0008) ?
           (($perms & 0x0400) ? 's' : 'x' ) :
           (($perms & 0x0400) ? 'S' : '-'));

// World
$info .= (($perms & 0x0004) ? 'r' : '-');
$info .= (($perms & 0x0002) ? 'w' : '-');
$info .= (($perms & 0x0001) ?
           (($perms & 0x0200) ? 't' : 'x' ) :
           (($perms & 0x0200) ? 'T' : '-'));

if ($info!="-rw-rw-rw-") {
echo("Please set file permission for vars.php to 666");
die();
}


if ($_GET[step]=="1" ) {

$VARS_SERVER	=mysql_escape_string($_POST[mysql_server]);
$VARS_USER	=mysql_escape_string($_POST[mysql_user]);
$VARS_PASS	=mysql_escape_string($_POST[mysql_pass]);
$VARS_DB	=mysql_escape_string($_POST[mysql_db]);
$TEMP_MY_KEY	=mysql_escape_string($_POST[my_key]);

$temp_host=strtolower(getenv("HTTP_HOST"));
$temp_host=str_replace("www.", "", $temp_host);

if ($TEMP_MY_KEY!=md5("7218j8j8djnnnnnnn3whh2hhhq7ye$@#$KR%$#^%$#%$#%$#TDG%$#TEFasjdhajnsjidnbwQ@2@@4378378364FGEDEREtwuiehfkjsnkjn377n8ni8niusnekin32nrkrbkjnfjnfkjnewiubekrjfbskd".$temp_host."432h3289jruinbnnneu789eh299ubsiuhfkjbkskjhdbfn!@#$dfuewhfuibinnfhbhbf")) {
	$show_error="Invalid License Key!";
	$_GET[step]="";
} else {



	if (mysql_connect($VARS_SERVER, $VARS_USER, $VARS_PASS)) {
		
		if (mysql_select_db($VARS_DB)) {
			
			//All is good, Start installing...
			if (mysql_query("CREATE TABLE `accounts` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) collate latin1_general_ci NOT NULL,
  `email` varchar(255) collate latin1_general_ci NOT NULL,
  `password` varchar(255) collate latin1_general_ci NOT NULL,
  `balance` double NOT NULL,
  `credits` double NOT NULL,
  `name` varchar(255) collate latin1_general_ci NOT NULL,
  `refer` int(11) NOT NULL,
  `join_date` varchar(255) collate latin1_general_ci NOT NULL,
  `last_pay` varchar(255) collate latin1_general_ci NOT NULL,
  `last_surf` double NOT NULL,
  `last_count` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `join_datetime` varchar(255) collate latin1_general_ci NOT NULL,
  `join_ip` varchar(255) collate latin1_general_ci NOT NULL,
  `is_admin` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=211 ;")) {
		echo("Created `accounts, ");
		} else {
		echo("Failed to create `accounts, ");
		}

		if (mysql_query("CREATE TABLE `banners` (
  `banner` varchar(255) collate latin1_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;")) {
		echo("Created `banners, ");
		} else {
		echo("Failed to create `banners, ");
		}


		if (mysql_query("CREATE TABLE `history` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) collate latin1_general_ci NOT NULL,
  `amount` double NOT NULL,
  `type` int(11) NOT NULL COMMENT '1-Deposit,2-Withdraw,3-Ref Commission',
  `date` varchar(255) collate latin1_general_ci NOT NULL,
  `datetime` varchar(255) collate latin1_general_ci NOT NULL,
  `source` varchar(255) collate latin1_general_ci NOT NULL,
  `paid` int(11) NOT NULL,
  `ref_id` varchar(255) collate latin1_general_ci NOT NULL,
  `account_id` varchar(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=135 ;")) {
		echo("Created `history, ");
		} else {
		echo("Failed to create `history, ");
		}

		if (mysql_query("CREATE TABLE `hits` (
  `date` varchar(255) collate latin1_general_ci NOT NULL,
  `count` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;")) {
		echo("Created `hits, ");
		} else {
		echo("Failed to create `hits, ");
		}
		
		if (mysql_query("CREATE TABLE `member_news` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) collate latin1_general_ci NOT NULL,
  `article` longtext collate latin1_general_ci NOT NULL,
  `date` varchar(255) collate latin1_general_ci NOT NULL,
  `createdby` varchar(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;")) {
		echo("Created `member_news, ");
		} else {
		echo("Failed to create `member_news, ");
		}

		if (mysql_query("CREATE TABLE `merchants` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate latin1_general_ci NOT NULL,
  `field` varchar(255) collate latin1_general_ci NOT NULL,
  `active` int(11) NOT NULL default '0',
  `eta` varchar(255) collate latin1_general_ci NOT NULL,
  `instant` int(11) NOT NULL,
  `min_withdraw` double NOT NULL default '0.01',
  `mass_pay` mediumtext collate latin1_general_ci NOT NULL,
  `pay_code` longtext collate latin1_general_ci NOT NULL,
  `hard_coded` int(11) NOT NULL,
  `account_id` varchar(255) collate latin1_general_ci NOT NULL,
  `autopay_account_id` varchar(255) collate latin1_general_ci NOT NULL,
  `autopay_account_pass` varchar(255) collate latin1_general_ci NOT NULL,
  `instant_pay` int(11) NOT NULL,
  `secret_code` varchar(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=7 ;")) {
		echo("Created `merchants, ");
		} else {
		echo("Failed to create `merchants, ");
		}
	
		if (mysql_query("CREATE TABLE `news` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) collate latin1_general_ci NOT NULL,
  `article` longtext collate latin1_general_ci NOT NULL,
  `date` varchar(255) collate latin1_general_ci NOT NULL,
  `createdby` varchar(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;")) {
		echo("Created `news, ");
		} else {
		echo("Failed to create `news, ");
		}
	
		if (mysql_query("CREATE TABLE `sessions` (
  `sessionid` varchar(255) NOT NULL default '',
  `username` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `ip` varchar(255) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;")) {
		echo("Created `sessions, ");
		} else {
		echo("Failed to create `sessions, ");
		}


		if (mysql_query("CREATE TABLE `sites` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) collate latin1_general_ci NOT NULL,
  `credits` double NOT NULL,
  `url` varchar(255) collate latin1_general_ci NOT NULL,
  `title` varchar(255) collate latin1_general_ci NOT NULL,
  `enabled` int(11) NOT NULL default '1',
  `views` double NOT NULL,
  `views_today` double NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=49 ;")) {
		echo("Created `sites, ");
		} else {
		echo("Failed to create `sites, ");
		}


		if (mysql_query("CREATE TABLE `surf_history` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) collate latin1_general_ci NOT NULL,
  `date` varchar(255) collate latin1_general_ci NOT NULL,
  `paid` int(11) NOT NULL,
  `credits_earned` double NOT NULL,
  `site_views` double NOT NULL,
  `last_surf` double NOT NULL,
  `last_site` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=4 ;")) {
		echo("Created `surf_history, ");
		} else {
		echo("Failed to create `surf_history, ");
		}


		if (mysql_query("CREATE TABLE `transactions` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL default '',
  `amount` double NOT NULL default '0',
  `method` varchar(255) NOT NULL default '',
  `trans_id` varchar(255) NOT NULL default '',
  `timestamp` varchar(255) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;")) {
		echo("Created `transactions, ");
		} else {
		echo("Failed to create `transactions, ");
		}

		if (mysql_query("CREATE TABLE `upgrades` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) collate latin1_general_ci NOT NULL,
  `amount` double NOT NULL,
  `days_left` double NOT NULL,
  `amount_earned` double NOT NULL,
  `method` varchar(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=3 ;")) {
		echo("Created `upgrades, ");
		} else {
		echo("Failed to create `upgrades, ");
		}

		if (mysql_query("INSERT INTO `merchants` VALUES (1, 'E-gold', 'E-gold', 1, 'Instant', 1, 0.01, '', '<form action=\"https://www.e-gold.com/sci_asp/payments.asp\" method=\"POST\" target=_top>\r\n<div align=\"center\">\r\n<input type=\"hidden\" name=\"PAYEE_ACCOUNT\" value=\"[accountid]\">\r\n<input type=\"hidden\" name=\"PAYEE_NAME\" value=\"[title]\">\r\n<input type=\"hidden\" name=\"PAYMENT_AMOUNT\" value=\"[amount]\">\r\n<input type=\"hidden\" name=\"PAYMENT_UNITS\" value=1>\r\n<input type=\"hidden\" name=\"PAYMENT_METAL_ID\" value=1>\r\n<input type=\"hidden\" name=\"STATUS_URL\" value=\"http://[site_url]/egold.php\">\r\n<input type=\"hidden\" name=\"NOPAYMENT_URL\" value=\"http://[site_url]/\">\r\n<input type=\"hidden\" name=\"NOPAYMENT_URL_METHOD\" value=\"LINK\">\r\n<input type=\"hidden\" name=\"PAYMENT_URL\" value=\"http://[site_url]/?n=thanks\">\r\n<input type=\"hidden\" name=\"PAYMENT_URL_METHOD\" value=\"LINK\">\r\n<input type=\"hidden\" name=\"BAGGAGE_FIELDS\" value=\"DATA_ID\">\r\n<input type=\"hidden\" name=\"DATA_ID\" value=\"[userid]\">\r\n<input type=\"hidden\" name=\"DATA_ITEM\" value=\"[itemid]\">\r\n<input type=\"hidden\" name=\"SUGGESTED_MEMO\" value=''[memo]''>\r\n<input type=\"submit\" name=\"PAYMENT_METHOD\" value=\"Pay $[amount] with E-Gold\">\r\n</div></form>', 1, '', '', '', 0, '')")) {
		echo("Inserted Merchant E-gold");
		} else {
		echo("Failed to insert merchant e-gold");
		}


	$todays_date	=date("m-d-y");

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
	$somecontent .= '$settings[enable_member_news]="'."1"."\";\n\n";
	$somecontent .= '$settings[unit_per_credit]="'.mysql_escape_string($_POST[unit_per_credit])."\";\n\n";
	$somecontent .= '$settings[max_views]="'.mysql_escape_string($_POST[max_views])."\";\n\n";
	$somecontent .= '$settings[date_launched]="'.$todays_date."\";\n\n";

	$somecontent .= '$settings[surf_page_refresh]="'.mysql_escape_string($_POST[surf_page_refresh])."\";\n\n";
	$somecontent .= '$settings[free_member_credits]="'.mysql_escape_string($_POST[free_member_credits])."\";\n\n";
	$somecontent .= '$settings[pro_member_credits]="'.mysql_escape_string($_POST[pro_member_credits])."\";\n\n";
	$somecontent .= '$settings[surf_required]="'.mysql_escape_string($_POST[surf_required])."\";\n\n";
	$somecontent .= '$settings[daily_percent]="'.mysql_escape_string($_POST[daily_percent])."\";\n\n";

	$somecontent .= '$settings[my_key]="'.$TEMP_MY_KEY."\";\n\n";


	$somecontent .= '$settings[is_installed]="'."1"."\";\n\n";

	$somecontent .= '$settings[show_news]="'."1"."\";\n";
	$somecontent .= '$settings[news_count]="'."5"."\";\n";
	$somecontent .= '$settings[show_stats]="'.mysql_escape_string($_POST[show_stats])."\";\n\n";
	$somecontent .= '$settings[show_stats_total_members]="'.mysql_escape_string($_POST[show_stats_total_members])."\";\n";
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
  
  
   	fclose($handle);

	} else {
 	  //echo("FAILED");
	 $show_error = "Failed to save settings!";
	}



		} else {
			$show_error = "Failed to select the database!";
			$_GET[step]="";
		}


	} else {
		$show_error = "Failed to connect to mysql server!";
		$_GET[step]="";
	}

}

}


if ($_GET[step]==2) {
	include("vars.php");
	if (mysql_connect($VARS_SERVER, $VARS_USER, $VARS_PASS)) {
		
		if (mysql_select_db($VARS_DB)) {
			
			$temp_username	=mysql_escape_string($_POST[username]);
			$temp_password	=mysql_escape_string($_POST[password]);
			$temp_email	=mysql_escape_string($_POST[email]);
			$temp_name	=mysql_escape_string($_POST[name]);
			$todays_date	=date("m-d-y");

			$temp_password  =md5("mfSSD>$#@99mdJISoAsk10A".$temp_password);
			
			mysql_query("INSERT INTO `accounts` ( `id` , `username` , `email` , `password` , `balance` , `credits` , `name` , `refer` , `join_date` , `last_pay` , `last_surf` , `last_count` , `status` , `join_datetime` , `is_admin`, `join_ip` ) VALUES (NULL , '$temp_username', '$temp_email', '$temp_password', '', '', '$temp_name', '', '$todays_date', '', '', '', '1', '".time()."', '1', '$ip');");			

			header("Location: /");

		}
	
	}

}

$temp_host=strtolower(getenv("HTTP_HOST"));
$temp_host=str_replace("www.", "", $temp_host);


$smarty->assign ('temp_host', $temp_host);

$smarty->assign ('show_error', $show_error);
$smarty->assign ('step', $_GET[step]);

$smarty->compile_check = true;
$smarty->template_dir = './style/';
$smarty->compile_dir = './temp_data';
$smarty->display ('install.tpl');

?> 


