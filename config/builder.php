<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/config/functions.php";
use Boilerplate\User;

interface ResponseBuilderInterface {
	public function generate();
}

class ResponseBuilder implements ResponseBuilderInterface {
	private $response = [
		"data" => [], 
		"status" => [
			"code" => 200, 
			"message" => ""
		]
	];
	private $error_msg = "";
	private $response_code = 200;
	

	public function r_ok() {$this->response_code = 200;}
	public function r_created() {$this->response_code = 201;}
	public function r_bad_request() {$this->response_code = 400;}
	public function r_unauthorized() {$this->response_code = 401;}
	public function r_forbidden() {$this->response_code = 403;}
	public function r_not_found() {$this->response_code = 404;}

	public function set_error($error) {
		$this->error_msg = $error;
		$this->response["status"]["message"] = $this->error_msg;
	}

	public function generate_and_set($code, $message, $data=[]) {
		$this->error_msg = $message;
		$this->response_code = $code;
		return $this->generate($data);
	}

	public function generate($data=[]) {
		$this->response["status"]["message"] = $this->error_msg;
		$this->response["status"]["code"] = $this->response_code;
		$this->response["data"] = $data;

		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json");
		http_response_code($this->response_code);

		return json($this->response);
	}
	
}
class Authentication {
	private $db;
	private $db_class;
	private $table;
	private $session_expire = 31556926;  // seconds in 1 year 

	public function __construct($db) {
		$this->db_class = $db;
		$this->db = $this->db_class->getConnection();
		$this->table = $this->db_class->get_table_name("authentication");
	}

	public function get_all() {
		$query = "SELECT * from $this->table";
		$statement = $this->db->prepare($query);
		$statement->execute();

		return $statement;
	}

	public function create_session($uid) {
		$timestamp = date_timestamp_get(date_create());
		$token = $uid . ' ' . $this->session_expire;
		$token = password_hash($token, PASSWORD_DEFAULT);
		$expire = $timestamp + $this->session_expire;

		$query = "INSERT INTO $this->table VALUES (:uid, :token, :expire)";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid', $uid);
		$statement->bindParam(':token', $token);
		$statement->bindParam(':expire', $expire);
		$statement->execute();

		return $token;
	}

	public function delete_session($token) {
		$query = "DELETE FROM $this->table WHERE token = :token";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':token', $token);
		$statement->execute();

		$affected = $statement->rowCount();
		if ($affected > 0) {
			return True;
		}
		return False;
	}

	public function verify_token($token) {
		$timestamp = date_timestamp_get(date_create());

		$query = "SELECT * from $this->table WHERE token = :token";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':token', $token);
		$statement->execute();

		$valid_token = False;
		while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			if (intval($row["expire"]) > intval($timestamp)) {
				$valid_token = $row["uid"];
				break;
			}
		}

		return $valid_token;
	}
}

class Logger {
	private $db;
	private $db_class;
	private $table;

	public $api_call;
	public $uid = -1;
	public $ip;

	public function __construct($db) {
		$this->db_class = $db;
		$this->db = $this->db_class->getConnection();
		$this->table = $this->db_class->get_table_name("logs");
	}

	public function log($success) {
		$timestamp = date_timestamp_get(date_create());
		$query = "INSERT INTO $this->table (uid, api_call, ip, timestamp, success) VALUES "
				. "(:uid, :api_call, :ip, :timestamp, :success)";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid', $this->uid);
		$statement->bindParam(':api_call', $this->api_call);
		$statement->bindParam(':ip', $this->ip);
		$statement->bindParam(':timestamp', $timestamp);
		$statement->bindParam(':success', $success);
		$statement->execute();
	}
}

class APIBuilder {
	public $database;
	public $database_class;
	public $response_builder;
	public $authentication;

	public $require_token = 0;
	public $require_active = 0;
	public $require_admin = 0;

	protected $token = "";
	protected $get_method = "get";
	protected $auth_user;

	protected $token_get = "token";
	protected $call_name;

	public function __construct() {
		$this->database_class = new DBClass();
		$this->database = $this->database_class->getConnection();
		$this->response_builder = new ResponseBuilder();
		$this->authentication = new Authentication($this->database_class);
		$this->logger = new Logger($this->database_class);
	}

	public function retrieve($field) {
		return retrieve($this->get_method, $field);  // from functions.php, get or post
	}

	protected function authenticate()  {
		$this->token = $this->retrieve($this->token_get);
		$token_uid = $this->authentication->verify_token($this->token);

		if (!isset($this->auth_user) || $this->auth_user->id ?? "" != $token_uid) {
 			$this->auth_user = new User($this->database_class);
			$this->auth_user->id = $token_uid;
		}

		$res = $this->auth_user->get_matching_user(True);
		if ($res !== True) {
			unset($this->auth_user);
		}

		if ($this->require_token) {
			if (!$token_uid) {
				return [401, "Invalid or unprovided token."];
			}
			$this->logger->uid = $this->auth_user->id;

			if (intval($this->auth_user->active) == 0 && $this->require_active) {
				return [405, "Inactive account. Contact the administrator."];
			}
			if (intval($this->auth_user->admin) == 0 && $this->require_admin) {
				return [403, "Administrator permission required."];
			}
		}
		return True;
	}

	public function init() {
		$invalid_config = $this->require_admin && !$this->require_token;
		$invalid_config = $invalid_config || ($this->require_active && !$this->require_token);
		if ($invalid_config) {  // if requires active||admin, then it needs to require token
			echo $this->response_builder->generate_and_set(500, "APIBuilder serverside init failure.");
			return False;  // I won't just set require token to 1 in order to avoid ambigous situations
		}

		$this->logger->api_call = get_class($this);  // returns name of the child class it's called from
		$this->logger->ip = get_ip();
		$auth = $this->authenticate();
		if ($auth !== True) {
			echo $this->response_builder->generate_and_set($auth[0], $auth[1]);
			$this->logger->log(False);
			return False;
		}

		$this->logger->log(True);
		return True;
	}
}

class WebBuilder extends APIBuilder {
	public $title = "Memy na Leśnej 3";
	public $top_message = "";
	public $top_message_code = 200;

	protected $f_head = "template/head.php";
	protected $f_header_msg = "template/header_message.php";
	protected $f_header = "template/header.php";
	protected $f_foot = "template/foot.php";
	protected $f_footer = "template/footer.php";

	public function __construct() {
		parent::__construct();
		$this->response_builder = new TemplateBuilder();
	}

	public function retrieve($field, $force_post=False) {
		$supplied = retrieve($this->get_method, $field);
		//if ($supplied == "") {
		//	$supplied = $_COOKIE[$field] ?? "";
		//}
		if ($supplied == "" && !$force_post) {
			$supplied = $_SESSION[$field] ?? "";
		}
		return $supplied;
	}

	public function set($field, $value="") {
		$_SESSION[$field] = $value;
	}

	public function exists($field) {
		return isset($_SESSION[$field]);
	}

	public function get_user() {
		return $this->auth_user ?? False;
	}

	protected function can_access() {
		$invalid_config = $this->require_admin && !$this->require_token;
		$invalid_config = $invalid_config || ($this->require_active && !$this->require_token);
		if ($invalid_config) {  // if requires active||admin, then it needs to require token
			return False;  // I won't just set require token to 1 in order to avoid ambigous situations
		}

		$this->logger->api_call = get_class($this);  // returns name of the child class it's called from
		$this->logger->ip = get_ip();
		$auth = $this->authenticate();
		if ($auth !== True) {
			$this->logger->log(False);
			return $auth;
		}

		$this->logger->log(True);
		return True;
	}

	public function init($force_auth_user=False) {
		if ($force_auth_user) {
			$this->auth_user = $force_auth_user;
		}
		$ret_val = $this->can_access();
		$this->response_builder->add_template($this->f_head, [
			"title" => $this->title,
			"stylesheets" => [
				"/style/style.css"
			]
		]);

		if ($this->top_message) {
			$this->response_builder->add_template($this->f_header_msg, [
				"message" => $this->top_message,
				"code" => $this->top_message_code,
			]);
		}

		$logged_in = isset($this->auth_user);
		if ($logged_in) {
			$user = $this->auth_user;
		} else {
			$user = False;
		}

		$this->response_builder->add_template($this->f_header, [
			"fname" => "Maciej",
			"lname" => "Kaszkowiak",
			"user" => $user
		]);

		if ($ret_val !== True) {
			$this->response_builder->add_template("codes/generic.php", [
				"code" => $ret_val[0],
				"message" => $ret_val[1]
			]);
			$this->render();
		}
		return $ret_val === True;
	}

	public function set_title($t) {
		$this->title = $t;
	}

	public function render() {
		$this->response_builder->add_template($this->f_footer, [
			"footer" => "Wielosztuki w Żabce"
		]);
		$this->response_builder->add_template($this->f_foot, []);
		$this->response_builder->generate();
	}

	public function handle_actions($reference) {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			return False;
		}

		if (!isset($_POST['action'])) {
			return False;
		}

		$action = $_POST['action'];
		if (!array_key_exists($action, $reference)) {
			return False;
		}

		return $reference[$action]();
	}
}

class TemplateBuilder implements ResponseBuilderInterface {
	protected $templates = [];
	protected $templates_data = [];
	protected $template_count = 0;

	protected $response_code = 200;

	public function __construct() {

	}

	public function add_template($filename, $data) {
		if (is_string($filename)) {
			$tmp = new Template();
			if (!$tmp->set_template_file($filename)) {
				return False;
			}
		} elseif ($filename instanceof TemplateInterface) {
			$tmp = $filename;
		} else {
			return False;
		}

		$this->templates[$this->template_count] = $tmp;
		$this->templates_data[$this->template_count] = $data;
		$this->template_count += 1;
	}

	public function set_response_code($code) {
		$this->response_code = $code;
	}

	public function generate() {
		http_response_code($this->response_code);

		foreach ($this->templates as $n => &$template) {
			$template->generate($this->templates_data[$n]);
		}
	}

}

interface TemplateInterface {
	public function generate($data);
}

class Template implements TemplateInterface {
	protected $template_dir = "/templates/";
	protected $template_path = "";
	protected $nest_extract = [];

	public function __construct() {

	}

	public function set_template_file($filename) {
		$dir = $_SERVER["DOCUMENT_ROOT"] . $this->template_dir;
		$path = $dir . $filename;
		if (file_exists($path) && is_readable($path)) {
			$this->template_path = $path;
			return True;
		} else {
			return False;
		}
	}

	public function generate($data) {
		extract($this->nest_extract);
		$this->nest_extract = $data;
		extract($this->nest_extract);

		if ($this->template_path != "") {
			include $this->template_path;
		}
	}

	protected function nest($filename, $data) { // input there should be correct
		$temp = new Template();
		$temp->set_template_file($filename);
		$temp->generate($data);
	}
}
?>