<?php

	/**
	* ==============================
	* Schema
	* ==============================
	*/

	class Schema {

		// Schema Information
		public $charset;
		public $collation;
		public $engine;
		private $constrains;
		private $properties;

		// Table Information
		public $name;


		function __construct ($name, $engine, $charset, $collation) {
			$this -> properties = [];
			$this -> constrains = [];
			$this -> name = $name;
			$this -> charset = $charset;
			$this -> collation = $collation;
			$this -> engine = $engine;

		}

		public static function create ($name, $engine = "InnoDB", $charset = "utf8", $collation = "utf8_bin") {
			return new Schema ($name, $engine, $charset, $collation);
		}

		function __toString () {
			return "CREATE TABLE IF NOT EXISTS `".DB::name()."`.`{$this -> name}` ({$this -> buildProperties()} {$this -> buildConstrains()}) ENGINE={$this -> engine} CHARSET={$this -> charset} COLLATE={$this -> collation};";
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

		public function fields () {
			return array_keys($this -> properties);
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
			$this -> id = $name;
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
			return $this -> addConstrain ("FOREIGN KEY (`$name`) REFERENCES `".DB::name()."`.`$table`(`$property`) ON UPDATE $update ON DELETE $delete");
		}

		public static function drop ($name) {
			DB::query ("DROP TABLE IF EXISTS `$name`");
		}

		public static function truncate ($name) {
			DB::query("TRUNCATE TABLE `$name`");
		}

		public static function commit ($schema) {
			DB::query ($schema);
		}
	}

?>