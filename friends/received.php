<?php
namespace API\Friends;

use Boilerplate\Friendship;
use Boilerplate\Request;
use Boilerplate\User;
use \PDO;

include_once "../config/database.php";
include_once "../config/functions.php";
include_once "../config/builder.php";

include_once "../boilerplate/friendship.php";
include_once "../boilerplate/request.php";
include_once "../boilerplate/user.php";


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

		$user = new Request($this->database_class);
		$data = [];
		$code = 200;
		$message = "foobar";
		$data = $user->get_received_profiles(1)->fetchAll(PDO::FETCH_ASSOC);

		echo $this->response_builder->generate_and_set($code, $message, $data);
	}
}

$api = new ListFriends();
$api->run();
?>