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

	public function delete() {
		$query = "DELETE FROM $this->table WHERE id = :id";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':id', $this->id);
		$statement->execute();
		return True;
	}

	public function update_by_id() {
		$valid = True;
		$valid = $valid && validate_post_title($this->title);
		$valid = $valid && validate_post_text($this->text);
		$valid = $valid && filter_var($this->class_id, FILTER_VALIDATE_INT) !== false;
		$valid = $valid && filter_var($this->id, FILTER_VALIDATE_INT) !== false;
		if (!$valid) {
			return "Złe zapytanie.";
		}

		$this->edit_timestamp = date_timestamp_get(date_create());
		$this->text_formatted = markdown($this->text);
		if ($this->text_formatted === 0) {
			return "Za długi post [>100 linijek]";
		}

		$query = "UPDATE $this->table "
			. "SET edit_timestamp = :edit_timestamp, "
			. "text = :text, text_formatted = :text_formatted, "
			. "title = :title, class_id = :class_id "
			. "WHERE id = :id";

		$statement = $this->db->prepare($query);
		$statement->bindParam(':class_id', $this->class_id);
		$statement->bindParam(':id', $this->id);
		$statement->bindParam(':title', $this->title);
		$statement->bindParam(':text', $this->text);
		$statement->bindParam(':edit_timestamp', $this->edit_timestamp);
		$statement->bindParam(':text_formatted', $this->text_formatted);

		$statement->execute();
		return True;
	}

	public function create_new() {
		$valid = True;
		$valid = $valid && validate_post_title($this->title);
		$valid = $valid && validate_post_text($this->text);
		$valid = $valid && filter_var($this->author_id, FILTER_VALIDATE_INT) !== false;
		$valid = $valid && filter_var($this->class_id, FILTER_VALIDATE_INT) !== false;

		if (!$valid) {
			return "Złe zapytanie.";
		}

		$this->create_timestamp = date_timestamp_get(date_create());
		$this->edit_timestamp = 0;
		$this->text_formatted = markdown($this->text);
		if ($this->text_formatted === 0) {
			return "Za długi post [>100 linijek]";
		}

		$query = "INSERT INTO $this->table "
				. "(`author_id`, `class_id`, `create_timestamp`, `edit_timestamp`, "
				. "`title`, `text`, `text_formatted`)"
				. " VALUES "
				. "(:author_id, :class_id, :create_timestamp, :edit_timestamp, "
				. ":title, :text, :text_formatted)";

		$statement = $this->db->prepare($query);
		$statement->bindParam(':author_id', $this->author_id);
		$statement->bindParam(':class_id', $this->class_id);
		$statement->bindParam(':title', $this->title);
		$statement->bindParam(':text', $this->text);
		$statement->bindParam(':create_timestamp', $this->create_timestamp);
		$statement->bindParam(':edit_timestamp', $this->edit_timestamp);
		$statement->bindParam(':text_formatted', $this->text_formatted);

		$statement->execute();

		$uid_q = "SELECT * FROM $this->table WHERE `text` = :text ORDER BY id DESC";
		$uid_s = $this->db->prepare($uid_q);
		$uid_s->bindParam(':text', $this->text);
		$uid_s->execute();

		$this->id = $uid_s->fetch(PDO::FETCH_ASSOC)["id"];
		return True;
	}

	public function get_post($_author_id=-1) {  // TO-DO: zmienic author_id todo
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
		if ($_author_id !== -1) {
			$query .= " AND ps.author_id = :author_id";
		}
		$statement = $this->db->prepare($query);
		$statement->bindParam(':id', $this->id);
		if ($this->class_id !== -1) {
			$statement->bindParam(':class_id', $this->class_id);
		}
		if ($_author_id !== -1) {
			$statement->bindParam(':author_id', $_author_id);
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

	public function get_class_posts($after=0, $limit=0) {
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
			ORDER BY ps.id desc
		";
		if ($limit) {
			$query .= " LIMIT :limit ";
		}
		$query .= " OFFSET :after ";
		$statement = $this->db->prepare($query);
		$statement->bindParam(':class_id', $this->class_id);
		$statement->bindParam(':after', $after, PDO::PARAM_INT);
		if ($limit) {
			$statement->bindParam(':limit', $limit, PDO::PARAM_INT);
		}
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