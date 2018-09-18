<?php
namespace API\Users;
use Boilerplate\User;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";


class AdminRoot extends \WebBuilder {
	public function __construct() {
		parent::__construct();
		$this->require_token = 1;
		$this->require_active = 1;
		$this->require_admin = 1;
		$success = $this->init();

		if (!$success) {
			exit;
		}
	}

	public function run() {
		//$this->response_builder->add_template("example.php", []);
		$this->render();
	}
}

$api = new AdminRoot();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>