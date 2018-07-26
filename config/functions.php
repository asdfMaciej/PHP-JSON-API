<?php
function json_headers() { 
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json");
}

function get_ip() {
	/*
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR']; // security risk, but proxy on mikr.us
		} else {
				$ip = $_SERVER['REMOTE_ADDR'];
		}
	*/
	$ip = $_SERVER['REMOTE_ADDR'];
	return $ip;
}

function validate_name($name) {
	$name = trim($name);  // strip() from Python, removes trailing and prequeling whitespace
	$reg_pl = "/^[a-zA-ZąĄćĆęĘłŁŃńÓóśŚźŹŻż ]*/";
	$valid = True;
	if (!preg_match($reg_pl, $name) or strlen($name) > 28 or strlen($name) < 3) {
		$valid = False;
	}
	return $valid;
}

function validate_password($password) {
	return strlen($password) <= 32 && strlen($password) >= 6;
}

function validate_email($email) {  // i dont remember the php standard library, it's quite big
	return filter_var($email, FILTER_VALIDATE_EMAIL) != False;
}

function validate_ip($ip) {
	return filter_var($ip, FILTER_VALIDATE_IP) != False;
}

function get($id) {
	if(isset($_GET[$id])) {
    	return $_GET[$id];
	} else {
		return "";
	}
}

function post($id) {
	if(isset($_POST[$id])) {
    	return $_POST[$id];
	} else {
		return "";
	}
}

function retrieve($method, $string) {
	if ($method == "post") {
		return post($string);
	} elseif ($method == "get") {
		return get($string);
	} 
}

?>