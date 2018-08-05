<?php
namespace API\Users;
use Boilerplate\User;
use \PDO;

include_once "../config/database.php";
include_once "../config/functions.php";
include_once "../config/builder.php";
include_once "../boilerplate/user.php";


class Login extends \APIBuilder {
	private $login_get = "login";  // username or nickname
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
		$user = new User($this->database_class);
		$user->email = $this->retrieve($this->login_get);
		$user->nick = $this->retrieve($this->login_get);

		$data = [];
		$exists = $user->get_matching_user(True);	
		if ($exists === True) {
			$password = $this->retrieve($this->password_get);
			$correct_password = password_verify($password, $user->password);

			if ($correct_password) {
				$token = $this->authentication->create_session($user->id);
				$data["token"] = $token;
				$data["uid"] = $user->id;

				$message = "Successfully logged in.";
				$code = 200;
			} else {
				$message = "Incorrect password.";
				$code = 401;
			}
		} else {
			$message = $exists;
			$code = 400;
		}
		echo $this->response_builder->generate_and_set($code, $message, $data);
	}
}

$api = new Login();
$api->run();
?>