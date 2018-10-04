<?php
function json_headers() { 
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json");
}

function get_ip() {
	
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR']; // security risk, but proxy on mikr.us
		} else {
				$ip = $_SERVER['REMOTE_ADDR'];
		}
	
	//$ip = $_SERVER['REMOTE_ADDR'];
	return $ip;
}

function validate_name($name) {
	$name = trim($name);  // strip() from Python, removes trailing and prequeling whitespace
	$reg_pl = "/^[a-zA-ZąĄćĆęĘłŁŃńÓóśŚźŹŻż\-_0-9$#%@!%\^&\*]*/";
	$valid = True;
	if (!preg_match($reg_pl, $name) or strlen($name) > 16 or strlen($name) < 3) {
		$valid = False;
	}
	return $valid;
}

function validate_fname($name) {
	$name = trim($name);
	$reg_pl = "/^[a-zA-ZąĄćĆęĘłŁŃńÓóśŚźŹŻż ]*/";
	$valid = True;
	if (!preg_match($reg_pl, $name) or strlen($name) > 16 or strlen($name) < 3) {
		$valid = False;
	}
	return $valid;
}

function validate_password($password) {
	return strlen($password) <= 32 && strlen($password) >= 6;
}

function validate_post_title($title) {
	return strlen($title) <= 160 && strlen($title) >= 12;
}

function validate_post_text($text) {
	return strlen($text) <= 5500 && strlen($text) >= 16;
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

function json($str) {
	return json_encode($str, JSON_UNESCAPED_SLASHES);
}

function split($string) {
	$array = preg_split("/\r\n|\n|\r/", $string);
	return $array;
}

function markdown($string) {
	// it's not exactly markdown
	// but it should do its job (prove me wrong)
	// ~ Maciej Kaszkowiak, 27.05.2018
	$string = htmlspecialchars($string);
	$lines = split($string);
	if (count($lines) > 100) {
		return 0;
	}
	$html = "";
	foreach ($lines as $line) {
		$h2 = 0;
		$newline = 1;
		if (substr($line, 0, 2) == "# ") {
			$html .= "<h2>";
			$h2 = 1;
			$newline = 0;
			$line = substr($line, 2);
		}
		if (substr($line, 0, 3) == "---") {
			$html .= "<hr>";
			$newline = 0;
			$line = substr($line, 3);
		}
		$html .= $line;
		if ($h2) {
			$html .= "</h2>";
		}
		if ($newline) {
			$html .=  "<br>";
		}
		$html .= "\n";
	}
	$bold_lines = explode("**", $html);
	if (count($bold_lines) < 3) {
		return $html;
	}
	$html = "";
	$opened = 0;
	foreach ($bold_lines as $line) {
		$html .= $line;
		if (!$opened) {
			$html .= "<b>";
			$opened = 1;
		} else {
			$html .= "</b>";
			$opened = 0;
		}
	}
	if ($opened) {
		$html = substr($html, 0, -3);
	}
	return $html;
}

?>