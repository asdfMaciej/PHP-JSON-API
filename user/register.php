<?php
include_once "../config/database.php";
include_once "../config/functions.php";
include_once "../config/builder.php";
include_once "../boilerplate/user.php";


class Register extends APIBuilder {
	private $email_get = "email";
	private $nick_get = "nick";
	private $password_get = "password";

	public function __construct() {
		parent::__construct();
		$this->require_token = 0;
		$this->require_active = 0;
		$this->require_admin = 0;
		$success = $this->init();

		if (!$success) {
			exit;
		}
	}

	public function run() {
		$user = new User($this->database);
		$user->email = $this->retrieve($this->email_get);
		$user->nick = $this->retrieve($this->nick_get);
		$user->password = $this->retrieve($this->password_get);
		$user->register_ip = get_ip();

		$registered = $user->register();
		if ($registered === True) {
			$message = "Successfully registered.";
			$code = 200;  // OK
		} else {
			$message = $registered;  // it returns either True or an error msg.
			$code = 400;  // Bad request
		}
		echo $this->response_builder->generate_and_set($code, $message);
	}
}

$api = new Register();
$api->run();
?>