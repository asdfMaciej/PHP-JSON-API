<?php
namespace API\Users;
use Boilerplate\User;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";


class Register extends \APIBuilder {
	private $email_get = "email";
	private $nick_get = "nick";
	private $password_get = "password";
	private $first_name_get = "first_name";
	private $last_name_get = "last_name";

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
		$user = new User($this->database_class);
		$user->email = $this->retrieve($this->email_get);
		$user->nick = $this->retrieve($this->nick_get);
		$user->password = $this->retrieve($this->password_get);
		$user->first_name = $this->retrieve($this->first_name_get);
		$user->last_name = $this->retrieve($this->last_name_get);
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