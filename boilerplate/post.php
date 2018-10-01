<?php
namespace Boilerplate;
use \PDO;


class Post {
	private $db;
	private $db_class;
	private $table;

	public $id;
	public $author_id;
	public $class_id;
	public $create_timestamp;
	public $title;
	public $text;
	public $text_formatted;
	public $edit_timestamp;
	public $deleted;

	public function __construct($db) {
		$this->db_class = $db;
		$this->db = $this->db_class->getConnection();
		$this->table = $this->db_class->get_table_name("posts");
	}

	public function get_all() {
		$query = "SELECT * from $this->table";
		$statement = $this->db->prepare($query);
		$statement->execute();

		return $statement;
	}

	public function get_post() {
		$users = $this->db_class->get_table_name("users");
		$classes = $this->db_class->get_table_name("classes");
		$query = "
			SELECT ps.*,
				us.teacher AS author_teacher,
				us.admin AS author_admin,
				us.first_name AS author_fname,
				us.last_name AS author_lname,
				us.nick AS author_nick,
				cl.name AS class_name
			FROM $this->table AS ps
			LEFT JOIN $users AS us
				ON us.id = ps.author_id
			LEFT JOIN $classes AS cl
				ON cl.id = ps.class_id
			WHERE 
				ps.id = :id 
		";
		if ($this->class_id !== -1) {
			$query .= " AND ps.class_id = :class_id";
		}
		$statement = $this->db->prepare($query);
		$statement->bindParam(':id', $this->id);
		if ($this->class_id !== -1) {
			$statement->bindParam(':class_id', $this->class_id);
		}
		$statement->execute();

		if ($statement->rowCount() == 0) {
			return "No matching posts.";
		}

		$row = $statement->fetch(PDO::FETCH_ASSOC);  // we assume no collisions
		$this->id = $row["id"];
		$this->author_id = $row["author_id"];
		$this->class_id = $row["class_id"];
		$this->create_timestamp = $row["create_timestamp"];
		$this->title = $row["title"];
		$this->text = $row["text"];
		$this->text_formatted = $row["text_formatted"];
		$this->edit_timestamp = $row["edit_timestamp"];
		$this->deleted = $row["deleted"];
		$this->author_teacher = $row["author_teacher"];
		$this->author_admin = $row["author_admin"];
		$this->author_fname = $row["author_fname"];
		$this->author_lname = $row["author_lname"];
		$this->author_nick = $row["author_nick"];
		$this->class_name = $row["class_name"];


		return True;
	}

	public function get_class_posts() {
		$users = $this->db_class->get_table_name("users");
		$classes = $this->db_class->get_table_name("classes");
		$query = "
			SELECT 
				ps.*,
				us.teacher AS author_teacher,
				us.admin AS author_admin,
				us.first_name AS author_fname,
				us.last_name AS author_lname,
				us.nick AS author_nick,
				cl.name AS class_name
			FROM $this->table AS ps
			LEFT JOIN $users AS us
				ON us.id = ps.author_id
			LEFT JOIN $classes AS cl
				ON cl.id = ps.class_id
			WHERE 
				ps.class_id = :class_id AND
				ps.deleted = 0
		";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':class_id', $this->class_id);
		$statement->execute();

		if ($statement->rowCount() == 0) {
			return [];
		}

		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_all_rows() {
		return $this->get_all()->fetchAll(PDO::FETCH_ASSOC);
	}

}
?>