<?php

	/**
	 * ==============================
	 * Component
	 * ==============================
	 */


	class Component {

		protected static $name;
		protected static $id;
		protected static $schema;
		protected static $block = [];
		protected static $ignore = [];
		protected static $invisible = [];
		protected static $logical;
		protected static $secure = [];
		protected static $defaults = [];
		protected static $hash = [];

		public static function schema () {
			return static::$schema;
		}

		public static function fields () {
			return static::$schema -> fields ();
		}

		public static function protected ($id) {
			return in_array ($id, static::$block);
		}

		public static function defaults ($object) {
			foreach (static::$defaults as $key => $value) {
				if (!in_array ($key, $object)) {
					$object[$key] = $value;
				} else if (empty ($object[$key])) {
					$object[$key] = $value;
				}
			}
			return $object;
		}

		public static function hasFields ($fields) {
			if (is_string ($fields)) {
				return in_array ($fields, static::fields ());
			} else if (is_array ($fields)) {
				foreach ($fields as $field) {
					if (!in_array ($field, static::fields ())) {
						return false;
					}
				}
				return true;
			} else {
				throw new Exception ("Component `hasField` function expected an array or string variable, received variable of type: ". gettype ($fields), 1);
			}
		}

		public static function exists ($keys) {
			if (count ($keys) > 0) {
				$query = new Query ();
				$query 	-> select (static::$id)
						-> from (static::$name);

				$first = true;
				foreach ($keys as $key => $value) {
					if ($first) {
						$query -> where ($key)
							   -> equals ($value);
						$first = false;
					} else {
						$query -> and ($key)
							   -> equals ($value);
					}

				}
				$query -> commit ();

				$found = $query -> results () -> first ();

				return $found !== null;
			}

		}

		public static function encrypt ($object) {
			foreach ($object as $key => $value) {
				if (!empty ($value)) {
					if (in_array ($key, static::$secure)) {
						$object[$key] = Crypt::encrypt ($value);
					} else if (in_array ($key, static::$hash)) {
						$object[$key] = Password::hash ($value);
					}
				} else {
					if (in_array ($key, static::$hash)) {
						unset ($object[$key]);
					}
				}
			}
			return $object;
		}

		public static function decrypt ($object) {
			if (is_array ($object)) {
				foreach ($object as $key => $value) {
					if (in_array ($key, static::$secure) && !empty ($value)) {
						$object[$key] = Crypt::decrypt ($value);
					}
				}
			}
			return $object;
		}

		public static function all ($fields = null) {

			$searchResults = [];

			$query = new Query ();
			$query 	-> select (static::$id)
					-> from (static::$name);

			if (is_string(static::$logical)) {
				$query -> where (static::$logical)
					   -> equals (1);
			} else {
				if (is_array (static::$ignore)) {
					if (count (static::$ignore) > 0) {
						$query -> where (static::$id)
							   -> notEquals (static::$ignore[0]);

						unset (static::$ignore[0]);
					}
				}
			}

			if (is_array (static::$ignore)) {
				if (count (static::$ignore) > 0) {
					foreach (static::$ignore as $ignored) {
						$query -> and (static::$id)
							   -> notEquals ($ignored);
					}
				}
			}

			$query -> commit ();

			$results = $query -> results ();
			foreach ($results as $result) {
				array_push ($searchResults, static::get($result[static::$id], $fields));
			}
			return $searchResults;
		}

		public static function get ($id, $fields = null, $callback = null, $arguments = []) {
			if ($fields === null || empty ($fields)) {
				$fields = static::fields ();
			}

			$query = new Query ();
			$query 	-> select ($fields)
					-> from (static::$name)
					-> where (static::$id)
					-> equals ($id)
					-> commit ();

			$record = $query -> results () -> first ();

			if ($record === null) {
				return null;
			} else {

				$record = static::decrypt($record);
				$record = static::type ($record);

				if (is_string ($fields)) {
					$result = $record[$fields];
				} else if (is_array ($fields)) {
					foreach ($fields as $field) {
						if (in_array ($field, static::$hash)) {
							unset ($record[$field]);
						}
					}
					$result =  $record;
				}
				if (is_callable ($callback)) {
					$result = call_user_func_array($callback, array_merge([static::$id => $id, "object" => $result], ["arguments" => $arguments]));
				}
				return $result;
			}
		}

		public static function create ($object, $callback = null, $arguments = []) {
			if (!empty ($object)) {
				$keys = array_keys ($object);
				if (static::hasFields ($keys)) {
					$object = static::defaults ($object);
					$object = static::encrypt ($object);
					$query = new Query ();
					$query 	-> insert ()
							-> into (static::$name)
							-> values ($object)
							-> commit ();

					if (is_callable ($callback)) {
						return call_user_func_array($callback, array_merge([static::$id => DB::last (), "object" => $object], ["arguments" => $arguments]));
					}
				} else {
					throw new Exception ("Tried to create an object in ". static::$name. " with at least one non-existing field.<p><b>Existing Fields:</b> ". print_r (static::fields (), true) ."</p>". "<p><b>Received Fields:</b> ". print_r ($keys, true) ."</p>", 1);
				}
			} else {
				throw new Exception ("Empty array provided to create an object in ". static::$name. " with", 1);
			}
		}

		public static function update ($id, $object, $callback = null, $arguments = []) {
			if (!empty ($object)) {
				if (!static::protected ($id)) {
					$keys = array_keys ($object);
					if (static::exists ([static::$id => $id])) {
						if (static::hasFields ($keys)) {
							$object = static::encrypt ($object);
							$query = new Query ();
							$query 	-> update (static::$name)
									-> set ($object)
									-> where (static::$id)
									-> equals ($id)
									-> commit ();
							if (is_callable ($callback)) {
								return call_user_func_array($callback, array_merge([static::$id => $id, "object" => $object], ["arguments" => $arguments]));
							}
						} else {
							throw new Exception ("Tried to update an object in ". static::$name. " with at least one non-existing field.<p><b>Existing Fields:</b> ". print_r (static::fields (), true) ."</p>". "<p><b>Received Fields:</b> ". print_r ($keys, true) ."</p>", 1);
						}
					} else {
						throw new Exception ("Tried to update a non-existent element in ". static::$name. "<p><b>Key:</b> ". static::$id ."</p>". "<p><b>Value:</b> ". $id ."</p>", 1);
					}
				}
			} else {
				throw new Exception ("Empty array provided to update an object in ". static::$name. " with", 1);
			}
		}

		public static function delete ($id, $callback = null, $arguments = []) {
			if (!static::protected ($id)) {
				if (static::exists ([static::$id => $id])) {
					$query = new Query ();
					$query 	-> delete ()
							-> from (static::$name)
							-> where (static::$id)
							-> equals ($id)
							-> commit ();
					if (is_callable ($callback)) {
						return call_user_func_array($callback, array_merge([static::$id => $id], ["arguments" => $arguments]));
					}
				} else {
					throw new Exception ("Tried to delete a non-existent element in ". static::$name. "<p><b>Key:</b> ". static::$id ."</p>". "<p><b>Value:</b> ". $id ."</p>", 1);
				}
			}
		}

		public static function type ($object) {
			if (is_array ($object)) {
				foreach ($object as $key => $value) {
					if (is_numeric ($value)) {
						if (strpos($value, '.') !== false) {
							$object[$key] = floatval ($value);
						} else {
							$object[$key] = intval ($value);
						}
					} else if ($value === null) {
							$object[$key] = "";
					}
				}
			}
			return $object;
		}

		public static function activate ($id) {
			static::update ($id, [static::$logical => 1]);
		}

		public static function deactivate ($id) {
			static::update ($id, [static::$logical => 0]);
		}

		public static function __callStatic($name, $arguments) {
			if (method_exists (get_called_class (), $name)) {
			} else if (static::$schema -> hasField ($name)) {
				return self::get ($arguments[0], $name);
			} else {
				throw new Exception ("Tried to call non-existent static method `$name` in " . static::$name, 1);
			}
		}
	}
?>