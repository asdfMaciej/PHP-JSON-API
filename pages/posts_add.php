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

class PostsAdd extends \WebBuilder {
	private $login_get = "login";
	private $password_get = "password";

	protected $get_method = "post";
	protected $form_action = "add";
	protected $form_href = "/posts/add";

	protected $class_id;
	protected $post_id;

	public $set_title = "";
	public $set_text = "";
	public $set_class_id = "";

	public function __construct() {
		parent::__construct();
		$this->require_token = 1;
		$this->require_active = 1;
		$this->require_admin = 0;

		$this->functions_map = [
			"add" => [$this, "on_add"],
			"edit" => [$this, "on_edit"]
		];

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
		if (count($this->url_path) > 2) {
			$this->post_id = $this->url_path[2];
			$this->form_action = "edit";
			$this->form_href = "/posts/edit/" . $this->post_id;
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				$this->init_edit();
			}
		}
	}

	public function init_edit() {
		$post = new Post($this->database_class);
		$post->id = $this->post_id;
		$post->class_id = -1;
		$post->get_post();

		$this->set_title = $post->title;
		$this->set_text = $post->text;
		$this->set_class_id = $post->class_id;
	}

	public function verify() {
		// Verifies if admin, teacher or post made by auth user
		$post = new Post($this->database_class);

		$post->id = $this->post_id;
		$user_id = ($this->auth_user->admin || $this->auth_user->teacher) ? -1 : $this->auth_user->id;
		$post->class_id = $this->auth_user->admin ? -1 : $this->auth_user->class_id;

		$result = $post->get_post($user_id);
		return $result === True;
	}

	public function on_edit() {
		$post = new Post($this->database_class);

		if (!$this->verify()) {
			return [401, "Nie masz permisji na edytowanie tego postu"];
		}

		$post->id = $this->post_id;
		$post->class_id = $this->retrieve("class");
		$post->title = $this->retrieve("title");
		$post->text = $this->retrieve("text");

		$result = $post->update_by_id();
		if ($result !== True) {
			$this->set_title = $this->retrieve("title");
			$this->set_text = $this->retrieve("text");
			$this->set_class_id = $this->retrieve("class");
			return [400, $result];
		} else {
			$txt = 'Zaktualizowano post - <a href="/posts/view/'.$post->id.'">';
			$txt .= 'kliknij aby zobaczyć</a>';
			return [200, $txt];
		}
	}

	public function on_add() {
		$post = new Post($this->database_class);

		$post->title = $this->retrieve("title");
		$post->text = $this->retrieve("text");
		$post->class_id = $this->retrieve("class");
		$post->author_id = $this->auth_user->id;

		if (!($post->class_id == $this->auth_user->class_id || $this->auth_user->admin)) {
			return [401, "Nie masz permisji na dodawanie w tej klasie"];
		}

		$result = $post->create_new();
		if ($result !== True) {
			$this->set_title = $this->retrieve("title");
			$this->set_text = $this->retrieve("text");
			$this->set_class_id = $this->retrieve("class");
			return [400, $result];
		} else {
			$txt = 'Utworzono post - <a href="/posts/view/'.$post->id.'">';
			$txt .= 'kliknij aby zobaczyć</a>';
			return [200, $txt];
		}
	}

	public function run() {
		$u = $this->auth_user;
		if ($u->admin) {
			$_cl = new Classes($this->database_class);
			$classes = $_cl->get_all_rows();
		} else {
			$classes = [["id" => $u->class_id, "name" => $u->class_name]]; 
		}
		$this->response_builder->add_template("forms/new_post.php", [
			"user" => $this->auth_user,
			"classes" => $classes,
			"form_action" => $this->form_action,
			"form_href" => $this->form_href,
			"title" => $this->set_title ?? "",
			"text" => $this->set_text ?? "",
			"class_id" => $this->set_class_id ?? ""
		]);
		$this->response_builder->add_template("messages/posts_add.php", []);
		
		$this->render();
	}
}

$api = new PostsAdd();
$api->run();  // test token is $2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2
?>