<?php
include_once "../config/database.php";
include_once "../config/functions.php";
include_once "../config/builder.php";
include_once "../boilerplate/user.php";


class Profile extends APIBuilder {
	private $uid_get = "uid";

	public function __construct() {
		parent::__construct();
		$this->require_token = 1;
		$this->require_active = 1;
		$this->require_admin = 0;
		$success = $this->init();

		if (!$success) {
			exit;
		}
	}

	public function run() {
		$user = new User($this->database);
		$uid = $this->retrieve($this->uid_get);
		if ($uid == "") {
			$uid = $this->auth_user->id;
		}

		$user->id = $uid;
		$exists = $user->get_matching_user(True);

		$data = [];
		if ($exists === True) {
			$data["active"] = $user->active;
			$data["nick"] = $user->nick;
			$data["first_name"] = $user->first_name;
			$data["last_name"] = $user->last_name;
			$data["register_timestamp"] = $user->register_timestamp;
			$data["id"] = $user->id;
			if ($this->auth_user->id == $user->id || $this->auth_user->admin == 1) {
				$data["email"] = $user->email;
				$data["admin"] = $user->admin;
			}
			$message = "Profile data acquired.";
			$code = 200;
			
		} else {
			$message = "User doesn't exist.";
			$code = 400;
		}
		
		echo $this->response_builder->generate_and_set($code, $message, $data);
	}
}

$api = new Profile();
$api->run();
?>