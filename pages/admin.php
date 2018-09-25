<?php
namespace Web\Pages;
use Boilerplate\User;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";

session_start();
class AdminRoot extends \WebBuilder {
	protected $get_method = "post";

	public function __construct() {
		parent::__construct();
		$this->require_token = 1;
		$this->require_active = 1;
		$this->require_admin = 1;

		$this->functions_map = [
			"delete" => [$this, "on_delete"],
			"activate" => [$this, "on_activate"],
			"deactivate" => [$this, "on_deactivate"],
			"rename" => [$this, "on_rename"]
		];
		$auth = $this->authenticate();

		if ($auth === True) {
			$res = $this->handle_actions($this->functions_map);
			if (is_array($res)) {
				$this->top_message_code = $res[0];
				$this->top_message = $res[1];
			}
		}

		$success = $this->init();
		if (!$success) {
			exit;
		}
	}

	public function run() {
		$user_t = new User($this->database_class);
		$users = $user_t->get_all_rows();
		$this->response_builder->add_template("admin/panel.php", ["users" => $users]);
		$this->render();
	}

	protected function _query($query, $message) {
		$uids = $this->retrieve("user_id") ?? [];
		$statement = $this->database->prepare($query);
		foreach ((array) $uids as $uid) {
			$statement->bindParam(":uid", $uid);
			$statement->execute();
		}
		return [200, $message];
	}

	protected function on_delete() {
		return $this->_query("DELETE FROM users WHERE id = :uid", "Usunięto podane konta");
	}

	protected function on_activate() {
		return $this->_query("UPDATE users SET active = 1 WHERE id = :uid", "Aktywowano podane konta");
	}

	protected function on_deactivate() {
		return $this->_query("UPDATE users SET active = 0 WHERE id = :uid", "Deaktywowano podane konta");
	}

	protected function on_rename() {
		$uids = $this->retrieve("user_id") ?? [];
		$fname = $this->retrieve("fname");
		$lname = $this->retrieve("lname");
		$query = "UPDATE users SET first_name = :fname, last_name = :lname WHERE id = :uid";
		$statement = $this->database->prepare($query);
		foreach ((array) $uids as $uid) {
			$statement->bindParam(":uid", $uid);
			$statement->bindParam(":fname", $fname);
			$statement->bindParam(":lname", $lname);
			$statement->execute();
		}
		return [200, "a"];
	}
}

$api = new AdminRoot();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>