<?php
namespace API\Friends;
use Boilerplate\Friendship;
use \PDO;

include_once "../config/database.php";
include_once "../config/functions.php";
include_once "../config/builder.php";
include_once "../boilerplate/friendship.php";


class Friends extends \APIBuilder {
	public function __construct() {
		parent::__construct();
		$this->require_token = 0;
		$this->require_active = 0;
		$this->require_admin = 0;
		$success = $this->init();

		if (!$success) {
			exit;
		}
	}

	public function run() {
		$user = new Friendship($this->database_class);
		$result = $user->create_friendship(1, 0);
		$list = $user->get_user_friendships(1)->fetchAll(PDO::FETCH_ASSOC);

		var_dump($list);
		var_dump($result);

		echo $this->response_builder->generate_and_set(200, "Request completed", $list);
	}
}

$api = new Friends();
$api->run();
?>