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
		$f = new Friendship($this->database_class);
		$friends_col = $this->database_class->get_table_user_columns("friendships", True);
		$friends_table = $this->database_class->get_table_name("friendships");
		$users_col = $this->database_class->get_table_user_columns("users", True);
		$users_table = $this->database_class->get_table_name("users");

		$query = "SELECT "
				. implode(", ", $friends_col) . ", "
				. implode(", ", $users_col)
				. " FROM $friends_table "
				. " INNER JOIN $users_table ON "
				. "$users_table.id = $friends_table.uid2 "
				. " WHERE $friends_table.uid1 = 1";

		$statement = $this->database->prepare($query);
		$statement->execute();

		$list = $statement->fetchAll(PDO::FETCH_ASSOC);
		echo $query;
		//echo $this->response_builder->generate_and_set(200, "Request completed", $list);
	}
}

$api = new Friends();
$api->run();
?>