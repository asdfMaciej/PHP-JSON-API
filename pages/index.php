<?php
namespace API\Users;
use Boilerplate\User;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";

session_start();

class Index extends \WebBuilder {
	private $login_get = "login";
	private $password_get = "password";
	protected $get_method = "post";

	public function __construct() {
		parent::__construct();
		$this->require_token = 0;
		$this->require_active = 0;
		$this->require_admin = 0;

		$this->functions_map = [
			"login" => [$this, "on_login"],
			"logout" => [$this, "on_logout"]
		];

		$res = $this->handle_actions($this->functions_map);
		if (is_array($res)) {
			$this->top_message_code = $res[0];
			$this->top_message = $res[1];
		}

		$success = $this->init();

		if (!$success) {
			exit;
		}
	}

	public function run() {
		$this->response_builder->add_template("welcome.php", []);
		$this->response_builder->add_template("list_items.php", [
			"items" => [123, 1525, 5351351, 14]
		]);
		$this->response_builder->add_template("forms/login.php", [
			"user" => $this->get_user()
		]);
		
		$this->render();
	}

	protected function on_login() {
		$user = new User($this->database_class);
		$user->email = $this->retrieve($this->login_get);
		$user->nick = $this->retrieve($this->login_get);

		$data = [];
		$exists = $user->get_matching_user(True);
		$return_val = False;	
		if ($exists === True) {
			$password = $this->retrieve($this->password_get);
			$correct_password = password_verify($password, $user->password);

			if ($correct_password) {
				$token = $this->authentication->create_session($user->id);
				$this->set("token", $token);
				$this->set("uid", $user->id);

				$message = "Successfully logged in.";
				$code = 200;
				$this->auth_user = $user;
			} else {
				$message = "Incorrect password.";
				$code = 401;
			}
		} else {
			$message = $exists;
			$code = 400;
		}
		return [$code, $message];
	}

	protected function on_logout() {
		$worked = $this->authentication->delete_session($this->retrieve("token"));
		if ($worked) {
			$message = "Successfully logged out.";
			$code = 200;

			$this->set("token", "");
			$this->set("uid", "");
		} else {
			$message = "Incorrect token.";
			$code = 400;
		}
		return [$code, $message];
	}
}

$api = new Index();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>