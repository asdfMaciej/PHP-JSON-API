<?php
namespace Boilerplate;
use \PDO;


class Request {
	private $db;
	private $db_class;
	private $table;


	public function __construct($db) {
		$this->db_class = $db;
		$this->db = $this->db_class->getConnection();
		$this->table = $this->db_class->get_table_name("requests");
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

	public function get_sent($uid) {
		$query = "SELECT * from $this->table WHERE uid_receiver = :uid";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid', $uid);
		$statement->execute();

		return $statement;
	}

	public function get_received($uid) {
		$query = "SELECT * from $this->table WHERE uid_sender = :uid";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid', $uid);
		$statement->execute();

		return $statement;
	}

	public function check_if_sent($uid_sender, $uid_receiver, $relationship="Friendship") {
		$query = "SELECT * from $this->table WHERE "
				. "uid_sender = :uid1 AND uid_receiver = :uid2 AND relationship = :relationship";

		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid1', $uid_sender);
		$statement->bindParam(':uid2', $uid_receiver);
		$statement->bindParam(':relationship', $relationship);
		$statement->execute();

		return $statement->rowCount() != 0;
	}

	protected function get_profiles($uid, $user_column, $request_column) {  // ------- PROTECTED FUNCTION
		$requests_col = $this->db_class->get_table_user_columns("requests", True);
		$users_col = $this->db_class->get_table_user_columns("users", True);
		$requests_table = $this->table;
		$users_table = $this->db_class->get_table_name("users");

		$query = "SELECT "
				. "$requests_table.relationship, $requests_table.create_timestamp, "
				. "$requests_table.$request_column, "
				. implode(", ", $users_col)
				. " FROM $requests_table "
				. " INNER JOIN $users_table ON "
				. "$users_table.id = $requests_table.$request_column "
				. " WHERE $requests_table.$user_column = :uid ";

		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid', $uid);
		$statement->execute();

		return $statement;
	}

	public function get_received_profiles($uid) {
		return $this->get_profiles($uid, "uid_receiver", "uid_sender");
	}
	public function get_sent_profiles($uid) {
		return $this->get_profiles($uid, "uid_sender", "uid_receiver");
	}

	public function create_request($uid_sender, $uid_receiver, $relationship="Friendship") {
		/*
		TO-DO:
			Verify if IDs are legitimate
			Check if friendship already exists
			Perhaps disable mirror requests and insta create friendship
			^ would create cross-dependency though
		*/
		if ($uid_sender == $uid_receiver) {
			return "User IDs cannot be identical.";
		}

		$exists = $this->check_if_sent($uid_sender, $uid_receiver, $relationship);
		if ($exists) {
			return "This request already exists.";
		}

		$create_timestamp = date_timestamp_get(date_create());
		$query = "INSERT INTO $this->table "
				. "(uid_sender, uid_receiver, create_timestamp, relationship) VALUES "
				. "(:uid_sender, :uid_receiver, :create_timestamp, :relationship);";

		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid_sender', $uid_sender);
		$statement->bindParam(':uid_receiver', $uid_receiver);
		$statement->bindParam(':create_timestamp', $create_timestamp);
		$statement->bindParam(':relationship', $relationship);
		$statement->execute();

		return True;
	}

}
?>