<?php
include_once "../config/database.php";
include_once "../config/functions.php";


class ResponseBuilder {
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

		return json_encode($this->response);
	}
	
}
class Authentication {
	private $db;
	private $table = "sessions";
	private $session_expire = 31556926;  // seconds in 1 year 

	public function __construct($db) {
		$this->db = $db;
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

class APIBuilder {
	public $database;
	public $response_builder;
	public $authentication;

	public $require_token = 0;
	public $require_active = 0;
	public $require_admin = 0;

	private $token = "";
	private $token_get = "token";
	private $get_method = "get";
	private $auth_user;

	public function __construct() {
		$this->database = (new DBClass)->getConnection();
		$this->response_builder = new ResponseBuilder();
		$this->authentication = new Authentication($this->database);
	}

	public function retrieve($field) {
		return retrieve($this->get_method, $field);  // from functions.php, get or post
	}

	public function init() {
		$invalid_config = $this->require_admin && !$this->require_token;
		$invalid_config = $invalid_config || ($this->require_active && !$this->require_token);
		if ($invalid_config) {  // if requires active||admin, then it needs to require token
			echo $this->response_builder->generate_and_set(500, "APIBuilder serverside init failure.");
			return False;  // I won't just set require token to 1 in order to avoid ambigous situations
		}

		$this->token = $this->retrieve($this->token_get);
		if ($this->require_token) {
			$token_uid = $this->authentication->verify_token($this->token);
			if (!$token_uid) {
				echo $this->response_builder->generate_and_set(401, "Invalid or unprovided token.");
				return False;
			}
			$this->auth_user = new User($this->database);
			$this->auth_user->id = $token_uid;
			$this->auth_user->get_matching_user(True);

			if (intval($this->auth_user->active) == 0 && $this->require_active) {
				echo $this->response_builder->generate_and_set(401, "Inactive account.");
				return False;
			}
			if (intval($this->auth_user->admin) == 0 && $this->require_admin) {
				echo $this->response_builder->generate_and_set(403, "Administrator permission required.");
				return False;
			}
		}

		return True;
	}
}
?>