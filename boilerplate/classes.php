<?php
namespace Boilerplate;
use \PDO;


class Classes {
	private $db;
	private $db_class;
	private $table;

	public $id;
	public $name;
	public $lessons_class_id;

	public function __construct($db) {
		$this->db_class = $db;
		$this->db = $this->db_class->getConnection();
		$this->table = $this->db_class->get_table_name("classes");
	}

	public function get_all() {
		$query = "SELECT * from $this->table";
		$statement = $this->db->prepare($query);
		$statement->execute();

		return $statement;
	}

	public function get_matching_class() {
		$class_table = $this->db_class->get_table_name("classes");
		$query = "
			SELECT * from $this->table
			WHERE id = :id
		";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':id', $this->id);
		$statement->execute();

		if ($statement->rowCount() == 0) {
			return "No matching classes.";
		}

		$row = $statement->fetch(PDO::FETCH_ASSOC);  // we assume no collisions
		$this->id = $row["id"];
		$this->name = $row["name"];
		$this->lessons_class_id = $row["lessons_class_id"];

		return True;
	}

	public function get_all_rows() {
		return $this->get_all()->fetchAll(PDO::FETCH_ASSOC);
	}

}
?>