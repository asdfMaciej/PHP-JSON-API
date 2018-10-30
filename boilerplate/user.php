<?php
namespace Boilerplate;
use \PDO;


class User {
	private $db;
	private $db_class;
	private $table;

	public $id = -1;
	public $active = False;
	public $admin = False;
	public $email = "";
	public $nick = "";
	public $password = "";
	public $register_ip = "";
	public $register_timestamp = 0;
	public $first_name = "";
	public $last_name = "";
	public $auth_token = "";

	public $teacher = False;
	public $class_id = -1;
	public $class_name = "";
	public $lessons_class_id = "";

	public function __construct($db) {
		$this->db_class = $db;
		$this->db = $this->db_class->getConnection();
		$this->table = $this->db_class->get_table_name("users");
	}

	public function get_all() {
		$class_table = $this->db_class->get_table_name("classes");
		$query = "
			SELECT us.*, cl.name AS class_name 
			FROM $this->table AS us
			LEFT JOIN $class_table AS cl
				ON cl.id = us.class_id
		";
		$statement = $this->db->prepare($query);
		$statement->execute();

		return $statement;
	}

	public function get_all_rows() {
		return $this->get_all()->fetchAll(PDO::FETCH_ASSOC);
	}

	private function change_row($key, $value, $noun) {  // it's a private function, so we don't escape $key
		$query = "UPDATE $this->table SET $key = :value WHERE id = :id";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':id', $this->id);
		$statement->bindParam(':value', $value);
		$statement->execute();

		if ($statement->rowCount() == 0) {
			return $noun . " has failed - invalid request provided.";
		}
		return True;
	}

	public function activate() {return $this->change_row("active", 1, "Activation");}
	public function deactivate() {return $this->change_row("active", 0, "Dectivation");}
	public function give_admin() {return $this->change_row("admin", 1, "Giving admin permission");}
	public function remove_admin() {return $this->change_row("admin", 0, "Removing admin permission");}

	public function get_matching_user($self_assign=False) {
		$class_table = $this->db_class->get_table_name("classes");
		$query = "
			SELECT us.*, cl.name AS class_name, cl.lessons_class_id 
				FROM $this->table AS us
			LEFT JOIN $class_table AS cl 
				ON cl.id = us.class_id
			WHERE us.email = :email OR us.nick = :nick OR us.id = :id
		";
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
			$this->register_timestamp = $row["register_timestamp"];
			$this->first_name = $row["first_name"];
			$this->last_name = $row["last_name"];
			$this->teacher = $row["teacher"];
			$this->class_id = $row["class_id"];
			$this->class_name = $row["class_name"];
			$this->lessons_class_id = $row["lessons_class_id"];
		}
		return True;
	}

	public function get_class_users($class_id) {
		$class_table = $this->db_class->get_table_name("classes");
		$query = "
			SELECT us.*, cl.name AS class_name, lessons_class_id
				FROM $class_table AS cl
			LEFT JOIN $this->table AS us 
				ON cl.id = us.class_id
			WHERE cl.id = :class_id
		";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':class_id', $class_id);
		$statement->execute();

		if ($statement->rowCount() == 0) {
			return [];
		}

		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_friends() {
		if ($this->id == -1) {
			return False;
		}

		$friends_col = $this->db_class->get_table_user_columns("friendships", True);
		$users_col = $this->db_class->get_table_user_columns("users", True);
		$friends_table = $this->db_class->get_table_name("friendships");
		$users_table = $this->table;

		$query = "SELECT "
				. "$friends_table.relationship, $friends_table.create_timestamp, "
				. implode(", ", $users_col)
				. " FROM $friends_table "
				. " INNER JOIN $users_table ON "
				. "$users_table.id = $friends_table.uid2 "
				. " WHERE $friends_table.uid1 = :uid "
				. " AND relationship = \"Friendship\"";

		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid', $this->id);	
		$statement->execute();

		return $statement;
	}

	public function register() {
		$valid = True;
		$valid = $valid && validate_email($this->email);
		$valid = $valid && validate_name($this->nick);
		$valid = $valid && validate_password($this->password);
		$valid = $valid && validate_ip($this->register_ip);
		$valid = $valid && validate_fname($this->first_name);
		$valid = $valid && validate_fname($this->last_name);

		if (!$valid) {
			return "Validation wasn't successful.";
		}

		$similiar = $this->get_matching_user();
		if ($similiar === True) {  // username or email collision
			return "There's already an user with the same username or email.";
		}

		$this->password = password_hash($this->password, PASSWORD_DEFAULT);
		$this->register_timestamp = date_timestamp_get(date_create());

		$query = "INSERT INTO $this->table "
				. "(nick, password, email, register_ip, register_timestamp, first_name, last_name)"
				. " VALUES "
				. "(:nick, :password, :email, :register_ip, :register_timestamp, :first_name, :last_name)";

		$statement = $this->db->prepare($query);
		$statement->bindParam(':nick', $this->nick);
		$statement->bindParam(':password', $this->password);
		$statement->bindParam(':email', $this->email);
		$statement->bindParam(':register_ip', $this->register_ip);
		$statement->bindParam(':register_timestamp', $this->register_timestamp);
		$statement->bindParam(':first_name', $this->first_name);
		$statement->bindParam(':last_name', $this->last_name);

		$statement->execute();

		$uid_q = "SELECT * FROM $this->table WHERE nick = :nick";
		$uid_s = $this->db->prepare($uid_q);
		$uid_s->bindParam(':nick', $this->nick);
		$uid_s->execute();

		$this->id = $uid_s->fetch(PDO::FETCH_ASSOC)["id"];
		return True;
	}

}
?>