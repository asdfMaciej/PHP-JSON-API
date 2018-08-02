<?php
namespace API\User;
use Boilerplate\User;
use \PDO;

include_once "../config/database.php";
include_once "../config/functions.php";
include_once "../config/builder.php";
include_once "../boilerplate/user.php";


class ListUsers extends \APIBuilder {
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
		$user = new User($this->database_class);
		$list = $user->get_all_rows();
		echo $this->response_builder->generate_and_set(200, "Request completed", $list);
	}
}

$api = new ListUsers();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>