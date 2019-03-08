<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;

class Nexmo 
{


/**
* Constructor
*/
public function __construct() 
{

$this->api_key = registry::config('nexmo_api_key');
    $this->secret_key = registry::config('nexmo_secret_key');
	
}

/**
* Confirm a phone number is valid

public function confirm_number($userid, $country_code, $number) { 



	// Format phone number
	$phone = preg_replace("/\D/", "", $country_code) . ' ' . preg_replace("/\D/", "", $number);
	$phone = preg_replace("/[\+\s\-]/", "", $phone);
	$code = rand(100000, 999999);

	// Delete expired
	db_query("DELETE FROM nexmo_requests WHERE (date_expire < now() AND status = 'pending') OR (type = 'confirm' AND userid = $userid)");

	// Send message
	$message = $config['nexmo_from_name'] . ' verification code:  ' . $code;
	$result = $this->send_request($userid, 'confirm', $phone, $message, $code);

	// Return
	return $result;

}

///////////////////////////////////////////////////////////////////////
// Notify user
///////////////////////////////////////////////////////////////////////

public function notify_user($userid, $type, $amount, $currency = 'BTC') { 

	// Initialize
	global $config;

	// Get profile
	list($exists, $profile) = db_cget('bitcoins_profile', $userid);
	if ($exists == 0) { return false; }
	elseif ($profile['mobile_number_confirmed'] != 1) { return false; }
	elseif ($profile['mobile_number_country'] == '') { return false; }
	elseif ($profile['mobile_number'] == '') { return false; }
	elseif ($profile['sms_notification'] == 'none') { return false; }
	elseif ($profile['sms_notification'] != 'all' && $profile['sms_notification'] != $type) { return false; }

	// Set variables	
	$phone = $profile['mobile_number_country'] . $profile['mobile_number'];
	$message = "New $config[nexmo_from_name] $type of " . bitcoin_fmoney($amount) . ' ' . $currency;

	// Get message
	$result = $this->send_request($userid, 'notify', $phone, $message);
	return $result;

}


/**
* Send SMS message to a user
*/
public function send_request($userid, $type, $phone, $message, $code = '') { 


	// Add to db
	if ($type != 'notify') { 
	
		// Set variables
		if ($code == '') { $code = rand(100000, 999999); }
		$expire = db_get_field("SELECT date_add(now(), interval 3 hour)");
	
		// Add to db
		db_insert('nexmo_requests', '', array(
			0, 
			$userid, 
			'pending', 
			$type, 
			$code, 
			$phone, 
			$expire)
		);
	}
	
	// Set request
	$phone = preg_replace("/[\D]/", "", $phone);
	$request = array(
		'api_key' => $this->api_key, 
		'api_secret' => $this->secret_key, 
		'from' => $config['nexmo_from_name'], 
		'to' => $phone, 
		'text' => $message
	);
	
	// Send request
	$response = send_remote_request('https://rest.nexmo.com/sms/json', $request, 'POST');
	$vars = json_decode($response);
	
	// Return
	$ok = preg_match("/\"error-text\":\"(.+?)\"/", $response, $match) ? false : true;
	return $ok; 
	
}

///////////////////////////////////////////////////////////////////////
// Check confirmation code
///////////////////////////////////////////////////////////////////////

public function check_code($userid, $code, $type = 'withdraw') { 

	// Initial code checks
	if (preg_match("/\D/", $code)) { return false; }
	if (strlen($code) != 6) { return false; }

	// Get row
	list($exists, $row) = db_get_row("SELECT * FROM nexmo_requests WHERE userid = $userid AND status = 'pending' AND type = '$type' ORDER BY id DESC LIMIT 0,1");
	if ($exists == 0) { return false; }
	if ($row['verify_code'] != $code) { return false; }
	
	// Update database
	db_query("UPDATE nexmo_requests SET status = 'approved' WHERE id = $row[id]");
	
	// Return
	return true;

}



}

?>
