<?php
namespace Web\Pages;
use Boilerplate\User;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";

session_start();
class AdminLogs extends \WebBuilder {
	protected $get_method = "get";

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
		$after = $this->retrieve("after");
		if ($after == "") {$after = 0;}
		$t_logs = $this->database_class->get_table_name("logs");
		$q = "
			SELECT * FROM $t_logs ORDER BY id desc LIMIT 100 OFFSET :after
		";
		$stat = $this->database->prepare($q);
		$stat->bindParam(":after", $after, PDO::PARAM_INT);
		$stat->execute();

		$logs = $stat->fetchAll(PDO::FETCH_ASSOC);
		$this->response_builder->add_template("admin/logs.php", [
			"logs" => $logs, "after" => $after
		]);
		$this->render();
	}
}

$api = new AdminLogs();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>