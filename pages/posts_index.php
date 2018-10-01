<?php
namespace Web\Pages;
use Boilerplate\User;
use Boilerplate\Classes;
use Boilerplate\Post;
use \PDO;

include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/builder.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/user.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/post.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/boilerplate/classes.php";

session_start();

class PostsIndex extends \WebBuilder {
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
			$this->class_id = $this->url_path[2];
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

		$pos = new Post($this->database_class);
		$pos->class_id = $this->class_id;
		$cl_posts = $pos->get_class_posts();

		/*if ($cl_posts != True) {
			$this->f400();
			return;
		}*/

		$this->response_builder->add_template("messages/posts_index.php", [
			"user" => $this->auth_user,
			"posts" => $cl_posts,
		]);
		//var_dump($cl_posts);
		$this->render();
		
		
	}
}

$api = new PostsIndex();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>