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

class ClassMain extends \WebBuilder {
	private $login_get = "login";
	private $password_get = "password";
	protected $get_method = "post";

	protected $url_path = [];
	protected $class_id = 0;
	public function __construct() {
		parent::__construct();
		$this->require_token = 1;
		$this->require_active = 0;
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
		if (count($this->url_path) >= 2 && $this->auth_user->admin) {
			$this->class_id = $this->url_path[1];
		} else {
			$this->class_id = $this->auth_user->class_id;
		}
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

		$us = new User($this->database_class);
		$cl_users = $us->get_class_users($this->class_id);

		if ($cl_users != True) {
			$this->f400();
			return;
		}

		$this->response_builder->add_template("messages/class_main.php", [
			"user" => $this->auth_user,
			"cl_users" => $cl_users,
		]);
		$this->render();
		
		
	}
}

$api = new ClassMain();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>