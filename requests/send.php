<?php
namespace API\Requests;

use Boilerplate\Friendship;
use Boilerplate\Request;
use Boilerplate\User;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";

include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/friendship.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/request.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";


class Send extends \APIBuilder {
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
			$code = 400;
			$message = "Specified friend request already exists.";
		} else {
			$created = $request->create_request($token_uid, $uid);
			if ($created == True) {
				$code = 200;
				$message = "Friend request sent.";
			} else {
				$code = 400;
				$message = $created;
			}
		}

		echo $this->response_builder->generate_and_set($code, $message, $data);
	}
}

$api = new Send();
$api->run();
?>