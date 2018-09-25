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
		"logs" => "logs",
		"requests" => "requests"
	];

	protected $table_user_columns = [
		"users" => [
			"id", "nick", "email", "active", "first_name", "last_name"
		],
		"friendships" => [
			"id", "uid1", "uid2", "create_timestamp", "relationship"
		],
		"authentication" => [
			"uid", "token", "expire"
		],
		"logs" => [
			"id", "uid", "timestamp"
		],
		"requests" => [
			"id", "uid_sender", "uid_receiver", "create_timestamp", "relationship"
		]
	];

	protected $table_columns = [
		"users" => [
			"id", "nick", "password", "email", "register_ip", 
			"active", "admin", "first_name", "last_name", "register_timestamp"
		],
		"friendships" => [
			"id", "uid1", "uid2", "create_timestamp", "relationship"
		],
		"authentication" => [
			"uid", "token", "expire"
		],
		"logs" => [
			"id", "uid", "api_call", "ip", "success", "timestamp"
		],
		"requests" => [
			"id", "uid_sender", "uid_receiver", "create_timestamp", "relationship"
		]
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

	public function get_table_name($table) {
		return $this->table_names[$table];
	}

	public function get_table_columns($table, $prefix=False) {
		$ret = $this->table_columns[$table];
		if ($prefix) {
			foreach ($ret as &$val) {
				$val = $table . "." . $val; // for joining tables
			}
		}
		return $ret;
	}

	public function get_table_user_columns($table, $prefix=False) {
		$ret = $this->table_user_columns[$table];
		if ($prefix) {
			foreach ($ret as &$val) {
				$val = $table . "." . $val;
			}
		}
		return $ret;
	}
}
?>