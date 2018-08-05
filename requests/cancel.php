<?php
namespace API\Requests;

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


class Cancel extends \APIBuilder {
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

		$request = new Request($this->database_class);
		$token_uid = $this->auth_user->id;
		$exists = $request->check_if_sent($token_uid, $uid);

		$data = [];
		if ($exists) {
			$removed = $request->remove_request($token_uid, $uid);
			if ($removed == True) {
				$code = 200;
				$message = "Friend request canceled.";
			} else {
				$code = 400;
				$message = $removed;
			}
		} else {
			$code = 400;
			$message = "Specified friend request doesn't exist.";
		}

		echo $this->response_builder->generate_and_set($code, $message, $data);
	}
}

$api = new Cancel();
$api->run();
?>