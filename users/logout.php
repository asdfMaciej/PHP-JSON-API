<?php
namespace API\Users;
use Boilerplate\User;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";


class Logout extends \APIBuilder {
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
		$worked = $this->authentication->delete_session($this->token);
		if ($worked) {
			$message = "Successfully logged out.";
			$code = 200;
		} else {
			$message = "Incorrect token.";
			$code = 400;
		}

		echo $this->response_builder->generate_and_set($code, $message);
	}
}

$api = new Logout();
$api->run();
?>