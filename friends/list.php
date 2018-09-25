<?php
namespace API\Friends;
use Boilerplate\Friendship;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/friendship.php";

include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";
use Boilerplate\User;

class ListFriends extends \APIBuilder {
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
		$uid = $this->retrieve($this->uid_get);
		if ($uid == "") {
			$uid = $this->auth_user->id;
		}

		$user = new User($this->database_class);
		$user->id = $uid;
		$data = [];
		if ($this->auth_user->id == $user->id || $this->auth_user->admin == 1) {
			$statement = $user->get_friends();
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
			$message = "Friends profiles acquired.";
			$code = 200;
		} else {
			$message = "Insufficient permission.";
			$code = 403;
		}

		echo $this->response_builder->generate_and_set($code, $message, $data);
	}
}

$api = new ListFriends();
$api->run();
?>