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

class PostsView extends \WebBuilder {
	private $login_get = "login";
	private $password_get = "password";
	protected $get_method = "get";

	protected $url_path = [];
	protected $class_id = 0;
	protected $post_id = 0;
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
		$this->class_id = $this->auth_user->admin ? -1 : $this->auth_user->class_id;
		$this->post_id = $this->url_path[2];
	}

	private function f400() {
		$this->response_builder->add_template("codes/400.php", []);
		$this->render();
	}

	public function run() {
		$pos = new Post($this->database_class);
		$pos->id = $this->post_id;
		$pos->class_id = $this->class_id;
		$cl_post = $pos->get_post();
		
		$this->response_builder->add_template("messages/posts_view.php", [
			"user" => $this->auth_user,
			"post" => $pos,
			"result" => $cl_post
		]);
		
		$this->render();
		
		
	}
}

$api = new PostsView();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>