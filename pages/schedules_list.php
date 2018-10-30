<?php
namespace Web\Pages;
use Boilerplate\User;
use Boilerplate\Classes;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/classes.php";

session_start();

class ClassSchedule extends \WebBuilder {
	private $login_get = "login";
	private $password_get = "password";
	protected $get_method = "post";

	protected $url_path = [];
	protected $class_id = 0;
	protected $schedule_who = "";

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

	private function f400() {
		$this->response_builder->add_template("codes/400.php", []);
		$this->render();
	}

	public function run() {
		$query = "SELECT * FROM lessons";
		$stat = $this->database->prepare($query);
		$stat->execute();
		$lessons = $stat->fetchAll(PDO::FETCH_ASSOC);

		$teachers_l = [];
		$classes_l = [];
		$classrooms_l = [];

		foreach ($lessons as $l) {
			if (!array_key_exists($l["teacher_id"], $teachers_l)) {
				$teachers_l[$l["teacher_id"]] = $l["teacher"];
			}
			if (!array_key_exists($l["class_id"], $classes_l)) {
				$classes_l[$l["class_id"]] = $l["class"];
			}
			if (!array_key_exists($l["classroom"], $classrooms_l)) {
				$classrooms_l[$l["classroom"]] = $l["classroom"];
			}
		}

		$this->response_builder->add_template("lists/schedules.php", [
			"user" => $this->auth_user,
			"teachers" => $teachers_l,
			"classes" => $classes_l,
			"classrooms" => $classrooms_l
		]);

		$this->render();
		//var_dump($schedule);
		//echo $cl->lessons_class_id;
		
		
	}
}

$api = new ClassSchedule();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>