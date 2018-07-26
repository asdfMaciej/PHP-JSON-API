<?php
class User {
	private $db;
	private $table = "users";

	public $id = -1;
	public $active = 0;
	public $admin = 0;
	public $email = "";
	public $nick = "";
	public $password = "";
	public $register_ip = "";
	public $auth_token = "";

	public function __construct($db) {
		$this->db = $db;
	}

	public function get_all() {
		$query = "SELECT id, nick, email, register_ip, active, admin from $this->table";  // no password
		$statement = $this->db->prepare($query);
		$statement->execute();

		return $statement;
	}

	public function get_all_rows() {
		return $this->get_all()->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_matching_user($self_assign=False) {
		$query = "SELECT * from $this->table WHERE email = :email OR nick = :nick OR id = :id";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':email', $this->email);
		$statement->bindParam(':nick', $this->nick);
		$statement->bindParam(':id', $this->id);
		$statement->execute();

		if ($statement->rowCount() == 0) {
			return "No matching users.";
		}

		$row = $statement->fetch(PDO::FETCH_ASSOC);  // we assume no collisions
		if ($self_assign) {
			$this->active = $row["active"];
			$this->admin = $row["admin"];
			$this->email = $row["email"];
			$this->nick = $row["nick"];
			$this->password = $row["password"];
			$this->register_ip = $row["register_ip"];
			$this->id = $row["id"];
		}
		return True;
	}

	public function register() {
		$valid = True;
		$valid = $valid && validate_email($this->email);
		$valid = $valid && validate_name($this->nick);
		$valid = $valid && validate_password($this->password);
		$valid = $valid && validate_ip($this->register_ip);

		if (!$valid) {
			return "Validation wasn't successful.";
		}

		$similiar = $this->get_matching_user();
		if ($similiar === True) {  // username or email collision
			return "There's already an user with the same username or email.";
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
?>