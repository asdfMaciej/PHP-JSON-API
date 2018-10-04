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

class PostsDelete extends \WebBuilder {
	private $login_get = "login";
	private $password_get = "password";

	protected $get_method = "post";
	protected $form_action = "add";
	protected $form_href = "/posts/add";

	protected $post_id;

	public function __construct() {
		parent::__construct();
		$this->require_token = 1;
		$this->require_active = 1;
		$this->require_admin = 0;

		$this->functions_map = [];

		$auth = $this->authenticate();
		$this->init_url();

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

	public function run() {
		$post = new Post($this->database_class);
		$post->id = $this->post_id;

		$id = $this->auth_user->id;
		$user_id = $this->auth_user->admin ? -1 : $id;
		$post->class_id = $this->auth_user->admin ? -1 : $this->auth_user->class_id;

		$result = $post->get_post($user_id);
		
		if ($result !== True) {
			$this->response_builder->add_template("codes/generic.php", [
				"message" => "Nie masz uprawnień, aby usunąć ten post!"
			]);
			$this->render();
		} else {
			$post->delete();

			if ($this->class_id == -1) {
				header("Location: /");
			} else {
				header("Location: /posts/class/" . $this->class_id);
			}
			exit;
		}
	}
}

$api = new PostsDelete();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>