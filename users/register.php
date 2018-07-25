<?php
include_once "../config/database.php";
include_once "../config/functions.php";

json_headers();
http_response_code(404);

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
	public function generate($data) {
		$this->response["status"]["message"] = $this->error_msg;
		$this->response["status"]["code"] = $this->response_code;
		$this->response["data"] = $data;

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
class User {
	private $db;
	private $table = "users";

	public $active = 0;
	public $admin = 0;
	public $email = "";
	public $nick = "";
	public $password = "";
	public $register_ip = "";

	public function __construct($db) {
		$this->db = $db;
	}

	public function get_all() {
		$query = "SELECT * from $this->table";
		$statement = $this->db->prepare($query);
		$statement->execute();

		return $statement;
	}

	public function get_all_rows() {
		return $this->get_all()->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_matching() {
		$query = "SELECT * from $this->table WHERE email = :email OR nick = :nick";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':email', $this->email);
		$statement->bindParam(':nick', $this->nick);
		$statement->execute();
		return $statement;
	}

	public function add() {
		$valid = True;
		$valid = $valid && validate_email($this->email);
		$valid = $valid && validate_name($this->nick);
		$valid = $valid && validate_password($this->password);
		$valid = $valid && validate_ip($this->register_ip);

		if (!$valid) {
			return False;
		}

		$similiar = $this->get_matching();
		if ($similiar->rowCount() > 0) {  // username or email collision
			return False;
		}

		$this->password = password_hash($this->password, PASSWORD_DEFAULT);

		$query = "INSERT INTO $this->table (nick, password, email, register_ip) VALUES ";
		$query .= "(:nick, :password, :email, :register_ip)";

		$statement = $this->db->prepare($query);
		$statement->bindParam(':nick', $this->nick);
		$statement->bindParam(':password', $this->password);
		$statement->bindParam(':email', $this->email);
		$statement->bindParam(':register_ip', $this->register_ip);

		$statement->execute();
		return True;
	}

}

$database = new DBClass();
$db = $database->getConnection();

$fail_message = json_encode(["status" => "fail"]);
$dupochlast = new User($db);
$dupochlast->email = get("email");//"dupnik@o2.pl";
$dupochlast->nick = get("nick");//"maciej012";
$dupochlast->password = get("password");//"abcdef";
$dupochlast->register_ip = get_ip();
$rowy = $dupochlast->get_all_rows();

$auth = new Authentication($db);
$toczek = '$2y$10$RJWlAwzpwFie/5Lgim5hfOAoDvOxZXVtkGeVL5G8NrLCXNgfWlkQ2';
echo var_dump($auth->verify_token($toczek));
//echo json_encode($auth->get_all()->fetchAll(PDO::FETCH_ASSOC));
echo json_encode($rowy);
echo var_dump($dupochlast->add());
?>