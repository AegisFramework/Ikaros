<?php

	/**
	* ==============================
	* Schema
	* ==============================
	*/

	class Schema {

		public $db;
		public $charset;
		public $collation;
		public $engine;
		public $name;
		private $properties;
		private $constrains;

		function __construct(&$db, $name, $engine, $charset, $collation) {
			$this -> properties = [];
				$this -> constrains = [];
			$this -> name = $name;
			$this -> db = $db;
			$this -> charset = $charset;
			$this -> collation = $collation;
			$this -> engine = $engine;
		}

		public static function create (&$db, $name, $engine = "InnoDB", $charset = "utf8", $collation = "utf8_bin") {
			return new Schema ($db, $name, $engine, $charset, $collation);
		}

		function __toString () {
			return "CREATE TABLE `{$this -> db -> getName()}`.`{$this -> name}` ({$this -> buildProperties()} {$this -> buildConstrains()}) ENGINE={$this -> engine} CHARSET={$this -> charset} COLLATE={$this -> collation}";
		}

		private function buildProperties () {
			$query = "";
			foreach ($this -> properties as $name => $value) {
				$query .= "`$name` ";
				foreach ($value as $rule) {
					$query .= "$rule ";
				}
				$query .= ",";
			}
			return rtrim($query, ",");
		}

		private function buildConstrains () {
			$query = "";
			if (count ($this -> constrains) > 0) {
				$query = ",";
				foreach ($this -> constrains as $value) {
					$query .= "$value,";
				}

			}
			return rtrim($query, ",");
		}

		private function addRule ($name, $rule) {
			if (!array_key_exists($name, $this -> properties)) {
				$this -> properties[$name] = [];
			}

			if (!in_array($rule, $this -> properties[$name])) {
				array_push ($this -> properties[$name], $rule);
			}
			return $this;
		}

		public function addConstrain ($constrain) {
			if (!in_array($constrain, $this -> constrains)) {
				array_push ($this -> constrains, $constrain);
			}
			return $this;
		}

		public function default ($name, $value) {
			return $this -> addRule ($name, "DEFAULT $value");
		}

		public function string ($name, $size) {
			return $this -> addRule ($name, "VARCHAR($size)");
		}

		public function bigInt ($name, $size) {
			return $this -> addRule ($name, "BIGINT($size)");
		}

		public function text ($name, $size) {
			return $this -> addRule ($name, "TEXT($size)");
		}

		public function int ($name, $size) {
			return $this -> addRule ($name, "INT($size)");
		}

		public function decimal ($name, $size) {
			return $this -> addRule ($name, "DECIMAL($size)");
		}

		public function float ($name, $size) {
			return $this -> addRule ($name, "FLOAT($size)");
		}

		public function date ($name) {
			return $this -> addRule ($name, "DATE");
		}

		public function dateTime ($name) {
			return $this -> addRule ($name, "DATETIME");
		}

		public function boolean ($name) {
			return $this -> addRule ($name, "BOOLEAN");
		}

		public function null ($name) {
			return $this -> addRule ($name, "NULL");
		}

		public function notNull ($name) {
			return $this -> addRule ($name, "NOT NULL");
		}

		public function primary ($name) {
			return $this -> addRule ($name, "PRIMARY KEY");
		}

		public function unique ($name) {
			return $this -> addRule ($name, "UNIQUE");
		}

		public function increment ($name) {
			return $this -> addRule ($name, "AUTO_INCREMENT");
		}

		public function index ($name) {
			return $this -> addRule ($name, "INDEX");
		}

		public function foreign ($name, $table, $property, $update = "CASCADE", $delete = "RESTRICT") {
			return $this -> addConstrain ("FOREIGN KEY (`$name`) REFERENCES `{$this -> db -> getName()}`.`$table`(`$property`) ON UPDATE $update ON DELETE $delete");
		}

		public static function drop (&$db, $name) {
			$db -> query ("DROP TABLE IF EXISTS `$name`");
		}

		public static function commit ($schema) {
			$schema -> db -> query ($schema);
		}
	}

?>