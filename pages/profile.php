<?php
namespace Web\Pages;
use Boilerplate\User;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";

session_start();

class Profile extends \WebBuilder {
	private $login_get = "login";
	private $password_get = "password";
	protected $get_method = "post";

	public function __construct() {
		parent::__construct();
		$this->require_token = 1;
		$this->require_active = 0;
		$this->require_admin = 0;

		$success = $this->init();

		if (!$success) {
			exit;
		}
	}

	public function run() {
		$this->response_builder->add_template("messages/profile.php", [
			"user" => $this->auth_user
		]);
		$this->render();
	}
}

$api = new Profile();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>