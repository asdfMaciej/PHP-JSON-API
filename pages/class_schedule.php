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

		$this->init_url();
	}

	protected function handle_url() {
		$uri = explode('?', $_SERVER['REQUEST_URI'], 2);
		$path = $uri[0];
		$path_levels = explode("/", $path);
		array_shift($path_levels);
		$this->url_path = $path_levels;
	}

	protected function init_url() {
		$this->handle_url();
		if (count($this->url_path) >= 3 && $this->auth_user->admin) {
			$this->class_id = $this->url_path[2];
		} else {
			$this->class_id = $this->auth_user->class_id;
		}
		$this->class_id = urldecode($this->class_id);
		$this->schedule_who = $this->url_path[1];
	}

	private function f400() {
		$this->response_builder->add_template("codes/400.php", []);
		$this->render();
	}

	public function run() {
		if ($this->class_id == -1) {
			$this->f400();
			return;
		}

		$for_whom = "";

		if ($this->schedule_who == "class") {
			$query = "SELECT * FROM lessons WHERE class_id = :class_id ORDER BY period ASC";
		} elseif ($this->schedule_who == "teacher") {
			$query = "SELECT * FROM lessons WHERE teacher_id = :class_id ORDER BY period ASC"; 
		} elseif ($this->schedule_who == "classroom") {
			$query = "SELECT * FROM lessons WHERE classroom = :class_id ORDER BY period ASC"; 
			$for_whom = $this->class_id;
		}

		$stat = $this->database->prepare($query);
		$stat->bindParam(':class_id', $this->class_id);
		
		$stat->execute();
		$lessons = $stat->fetchAll(PDO::FETCH_ASSOC);

		if (count($lessons) > 0) {
			if ($this->schedule_who == "teacher") {
				$for_whom = $lessons[0]["teacher"];
			} elseif ($this->schedule_who == "class") {
				$for_whom = $lessons[0]["class"];
			}
		}

		$schedule = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
		foreach ($lessons as $l) {
			if ($l["d_monday"]) {if (!isset($schedule[1][$l["period"]])) {$schedule[1][$l["period"]]=[];} $schedule[1][$l["period"]][] = $l;}
			elseif ($l["d_tuesday"]) {if (!isset($schedule[2][$l["period"]])) {$schedule[2][$l["period"]]=[];} $schedule[2][$l["period"]][] = $l;}
			elseif ($l["d_wednesday"]) {if (!isset($schedule[3][$l["period"]])) {$schedule[3][$l["period"]]=[];} $schedule[3][$l["period"]][] = $l;}
			elseif ($l["d_thursday"]) {if (!isset($schedule[4][$l["period"]])) {$schedule[4][$l["period"]]=[];} $schedule[4][$l["period"]][] = $l;}
			elseif ($l["d_friday"]) {if (!isset($schedule[5][$l["period"]])) {$schedule[5][$l["period"]]=[];} $schedule[5][$l["period"]][] = $l;}
		}

		$this->response_builder->add_template("tables/schedule.php", [
			"user" => $this->auth_user,
			"schedule" => $schedule,
			"schedule_who" => $for_whom,
			"days" => ["Poniedziałek", "Wtorek", "Środa", "Czwartek", "Piątek"]
		]);
		$this->render();
		//var_dump($schedule);
		//echo $cl->lessons_class_id;
		
		
	}
}

$api = new ClassSchedule();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>