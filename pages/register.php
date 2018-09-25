<?php
//namespace API\Users;
namespace Web\Pages;
use Boilerplate\User;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";

session_start();

class Register extends \WebBuilder {
	private $nick_get = "nick";
	private $password_get = "password";
	private $email_get = "email";
	private $fname_get = "first_name";
	private $lname_get = "last_name";

	protected $get_method = "post";

	public function __construct() {
		parent::__construct();
		$this->require_token = 0;
		$this->require_active = 0;
		$this->require_admin = 0;

		$this->functions_map = [
			"register" => [$this, "on_register"]
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
		if (isset($this->auth_user)) {
			//var_dump($_SESSION);
			header("Location: /");
			session_write_close();
			exit();
		}
		$this->response_builder->add_template("decorators/center/start.php", []);
		$this->response_builder->add_template("forms/register.php", [
			"user" => $this->get_user()
		]);
		$this->response_builder->add_template("decorators/center/end.php", []);
		
		$this->render();
	}

	protected function on_register() {
		$user = new User($this->database_class);
		$user->email = $this->retrieve($this->email_get);
		$user->nick = $this->retrieve($this->nick_get);
		$user->password = $this->retrieve($this->password_get);
		$user->first_name = $this->retrieve($this->fname_get);
		$user->last_name = $this->retrieve($this->lname_get);
		$user->register_ip = get_ip();

		$registered = $user->register();
		if ($registered === True) {
			$message = "Pomyślnie zarejestrowano.";
			$code = 200;  // OK
			var_dump($user->id);
			$token = $this->authentication->create_session($user->id);
			$this->set("token", $token);
			$this->set("uid", $user->id);
			$this->auth_user = $user;
		} else {
			$message = $registered;  // it returns either True or an error msg.
			$code = 400;  // Bad request
		}
		return [$code, $message];
	}
}

$api = new Register();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>