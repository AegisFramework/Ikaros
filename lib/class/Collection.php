<?php

	/**
	 * ==============================
	 * Collection
	 * ==============================
	 */

	class Collection implements Iterator {

		private $array;

		function __construct ($array = null) {
			if ($array === null) {
				$this -> array = array ();
			} else {
				if (is_array ($array)) {
					$this -> array = $array;
				} else if (is_string ($array)) {
					$this -> array = json_decode($array, true);
				} else {
					throw Exception ("Collection expected an array variable or a JSON object for it's construction, received variable of type: ". gettype ($array));
				}
			}
		}

		public function array () {
			return $this -> array;
		}

		public function remove ($index) {
			unset ($this -> array [$index]);
		}

		public function rewind () {
			reset ($this -> array);
		}

		public function current () {
			return current ($this -> array);
		}

		public function key () {
			return key ($this -> array);
		}

		public function next () {
			return next ($this -> array);
		}

		public function valid () {
			$key = $this -> key ();
			return $key !== null && $key !== false;
		}

		public function copy () {
			return new Collection ($this -> array);
		}

		public function hasKey ($key) {
			return array_key_exists ($key, $this -> array);
		}

		public function keys () {
			return array_keys ($this -> array);
		}

		public function search ($needle) {
			return array_search ($needle, $this -> array);
		}

		public function contains ($needle) {
			return in_array ($needle, $this -> array);
		}

		public function first () {
			return $this -> get (0);
		}

		public function last () {
			return $this -> get ($this -> length () - 1);
		}

		public function get ($index) {
			if ($this -> hasKey ($index)) {
				return $this -> array[$index];
			} else {
				return null;
			}
		}

		public function set ($index, $value) {
			$this -> array[$index] = $value;
		}

		public function length () {
			return count ($this -> array);
		}

		public function all () {
			return $this -> array;
		}

		public function push () {
			array_push ($this -> array);
		}

		public function merge ($array) {
			array_merge($this -> array, $array);
		}

		public function prepend ($value) {
			array_unshift($this -> array, $value);
		}

		public function pop () {
			return array_pop ($this -> array);
		}

		function json () {
			return json_encode($this -> array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		}

		function __toString () {
			return $this -> json ();
		}

		function __destroy () {
			$this -> array = null;
		}

	}

?>