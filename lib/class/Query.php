<?php

	/**
	 * ==============================
	 * Query
	 * ==============================
	 */

	class Query {

		private $object;
		private $query;
		private $bindings;
		private $results;

		function __construct ($query = "", $bindings = [], $results = []) {
			$this -> query = $query;
			$this -> bindings = $bindings;
			$this -> results = new Collection ($results);
		}

		public function object () {
			return $this -> object;
		}

		public function results () {
			return $this -> results;
		}

		private function append ($command, $data = [], $bind = true, $ticks = true) {
			$this -> query .= $command . " ";

			if ($bind === true) {
				if (is_string ($data) || is_numeric ($data)) {
					$this -> query .= "?";
					array_push ($this -> bindings, $data);
				} else if (is_array ($data)) {
					if (count ($data) > 0) {
						foreach ($data as $d) {
							$this -> query .= "?, ";
							array_push ($this -> bindings, $d);
						}
					}
				}
			} else {
				if (is_string ($data) || is_numeric ($data)) {
					if ($ticks) {
						$this -> query .= "`$data`";
					} else {
						$this -> query .= "$data";
					}

				} else if (is_array ($data)) {
					if (count ($data) > 0) {
						foreach ($data as $d) {
							if ($ticks) {
								$this -> query .= "`$d`, ";
							} else {
								$this -> query .= "$d, ";
							}
						}
					}
				}
			}


			$this -> query = rtrim ($this -> query, ", "). " ";
			return $this;
		}

		public function insert () {
			return $this -> append ("INSERT");
		}

		public function into ($table) {
			return $this -> append ("INTO", $table, false);
		}

		public function values ($values) {
			$this -> append ("(", array_keys ($values), false);
			$this -> append (") VALUES");
			$this -> append ("(", array_values ($values));
			return $this -> append (")");
		}

		public function update ($table) {
			return $this -> append ("UPDATE", $table, false);
		}

		public function set ($values) {
			$this -> append ("SET");
			foreach ($values as $key => $value) {
				$this -> query .= "`$key`=?, ";
				array_push ($this -> bindings, $value);
			}
			$this -> query = rtrim ($this -> query, ", "). " ";
			return $this;
		}

		public function delete () {
			return $this -> append ("DELETE");
		}

		public function show () {
			return $this -> query;
		}

		public function select ($data) {
			return $this -> append ("SELECT", $data, false);
		}

		public function as ($data) {
			return $this -> append ("AS", $data, false);
		}

		public function from ($data) {
			return $this -> append ("FROM", $data, false);
		}

		public function where ($data) {
			return $this -> append ("WHERE", $data, false);
		}

		public function and ($data) {
			return $this -> append ("AND", $data);
		}

		public function or ($data) {
			return $this -> append ("OR", $data, false);
		}

		public function like ($data) {
			return $this -> append ("LIKE", "'$data'", false, false);
		}

		public function equals ($data) {
			return $this -> append ("=", $data);
		}

		public function lessThan ($data) {
			return $this -> append ("<", $data);
		}

		public function moreThan ($data) {
			return $this -> append (">", $data);
		}

		public function lessOrEqualThan ($data) {
			return $this -> append ("<=", $data);
		}

		public function moreorEqualThan ($data) {
			return $this -> append (">=", $data);
		}

		public function limit ($data) {
			return $this -> append ("LIMIT", $data, false, false);
		}

		public function commit () {
			$sth = DB::query ($this -> query, $this -> bindings);
			$this -> object = $sth;
			$this -> results = new Collection ($sth -> fetchAll (PDO::FETCH_ASSOC));
			return $this;
		}

		function __toString () {
			return trim ($this -> query) . ";";
		}

		function __destruct () {
		    $this -> object = null;
			$this -> results = null;
		}
	}
?>