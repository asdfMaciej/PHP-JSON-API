<?php
namespace API\User;
use Boilerplate\User;
use \PDO;

include_once "../config/database.php";
include_once "../config/functions.php";
include_once "../config/builder.php";
include_once "../boilerplate/user.php";


class Deactivate extends \APIBuilder {  // copy paste.. oh well, gives more clarity to api calls
	private $uid_get = "uid";

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
		$user->id = $this->retrieve($this->uid_get);

		$data = [];
		$exists = $user->get_matching_user(True);	
		if ($exists === True) {
			$success = $user->deactivate();

			if ($success === True) {
				$message = "Successfully deactivated.";
				$code = 200;
			} else {
				$message = $success;
				$code = 400;
			}
		} else {
			$message = $exists;
			$code = 400;
		}
		echo $this->response_builder->generate_and_set($code, $message, $data);
	}
}

$api = new Deactivate();
$api->run();
?>