<?php
namespace Boilerplate;
use \PDO;

class Friendship {
	private $db;
	private $db_class;
	private $table;

	public function __construct($db) {
		$this->db_class = $db;
		$this->db = $this->db_class->getConnection();
		$this->table = $this->db_class->get_table_name("friendships");
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

	public function get_user_friendships($uid) {
		$query = "SELECT * from $this->table WHERE uid1 = :uid";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid', $uid);
		$statement->execute();

		return $statement;
	}

	public function check_if_friends($uid1, $uid2, $relationship="Friendship") {
		$query = "SELECT * from $this->table WHERE "
				. "uid1 = :uid1 AND uid2 = :uid2 AND relationship = :relationship";

		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid1', $uid1);
		$statement->bindParam(':uid2', $uid2);
		$statement->bindParam(':relationship', $relationship);
		$statement->execute();

		return $statement->rowCount() != 0;
	} 

	public function create_friendship($uid1, $uid2, $relationship="Friendship") {
		/*
		TO-DO:
			Verify if IDs are legitimate
			yeah, I probably should check it there
		*/
		if ($uid1 == $uid2) {
			return "User IDs cannot be identical.";
		}

		$exists = $this->check_if_friends($uid1, $uid2, $relationship);
		if ($exists) {
			return "This friendship already exists.";
		}

		$create_timestamp = date_timestamp_get(date_create());
		$query = "INSERT INTO $this->table "
				. "(uid1, uid2, create_timestamp, relationship) VALUES ";
		$queries = [
			$query . "(:uid1, :uid2, :create_timestamp, :relationship)",
			$query . "(:uid2, :uid1, :create_timestamp, :relationship)"
		];

		foreach ($queries as $q) {
			$statement = $this->db->prepare($q);
			$statement->bindParam(':uid1', $uid1);
			$statement->bindParam(':uid2', $uid2);
			$statement->bindParam(':create_timestamp', $create_timestamp);
			$statement->bindParam(':relationship', $relationship);
			$statement->execute();
		}
		return True;

	}

	public function remove_friendship($uid1, $uid2, $relationship="Friendship") {
		/*
		TO-DO:
			Again, verify the IDs. Figure whether I should import User from there
		*/
		if ($uid1 == $uid2) {
			return "User IDs cannot be identical.";
		}

		$exists = $this->check_if_friends($uid1, $uid2, $relationship);
		if (!$exists) {
			return "This friendship doesn't exist.";
		}

		$query = "DELETE FROM $this->table WHERE "
				. "((uid1 = :uid1 AND uid2 = :uid2) OR (uid1 = :uid2 AND uid2 = :uid1)) "
				. "AND relationship = :relationship";  // remove both pairs of a given relationship
		$statement = $this->db->prepare($query);
		$statement->bindParam(':uid1', $uid1);
		$statement->bindParam(':uid2', $uid2);
		$statement->bindParam(':relationship', $relationship);
		$statement->execute();

		return True;
	}

}
?>