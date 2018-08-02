<?php
class DBClass {
	/*
		TO-DO:
		Store the credentials somewhere else.
		Don't have to worry about that so far, because it's on localhost.
	*/
	public $connection;

	protected $table_names = [
		"users" => "users",
		"friendships" => "friendships",
		"authentication" => "sessions",
		"logs" => "logs"
	];

	private $host = "localhost";
	private $username = "root";
	private $password = "";
	private $database = "website";

	public function getConnection() {
		$this->connection = null;
		try {
			$call = "mysql:host=" . $this->host . ";dbname=" . $this->database;
			$this->connection = new PDO($call, $this->username, $this->password);
			$this->connection->exec("set names utf8");
		} catch(PDOException $exception) {
			echo "Error: " . $exception->getMessage();
		}

		return $this->connection;
	}

	public function get_table_name($name) {
		return $this->table_names[$name];
	}
}
?>