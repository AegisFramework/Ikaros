<?php
	/**
	 * ==============================
	 * Session
	 * ==============================
	 */

	class Session {

		private static $id;
		private static $meta;

		public static function start () {
			session_set_cookie_params(365 * 24 * 60 * 60);

			ini_set ('session.gc_maxlifetime', 3600);

			session_start ();

			session_regenerate_id ();

			if (!isset($_SESSION['active'])) {
		    	$_SESSION['active'] = false;
			}
		}

		/**
		 * Regenerate session's id.
		 *
		 * @access public
		 * @return void
		 */
		public static function regenerate () {
			session_regenerate_id (true);
		}


		/**
		 * End and destroy the session, it's variables and cookie.
		 *
		 * @access public
		 * @return void
		 */
		public static function end () {
			unset ($_SESSION);
			session_unset ();
			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params ();
				setcookie (session_name (), "", time () - (365 * 24 * 60 * 60), $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
			}
			session_destroy ();
		}

		/**
		 * Check if User meta-data is still the same.
		 *
		 * @access public
		 * @param mixed $meta - Associative array with Ip and User Agent
		 * @return boolean
		 */
		public static function check ($data) {
			foreach ($data as $key => $value) {
				if (self::get ($key) !== $value) {
					self::end ();
					HTTP::error (403);
					return false;
				}
			}
		}

		public static function set ($key, $value) {
			$_SESSION[$key] = $value;
		}

		public static function get ($key) {
			if (array_key_exists ($key, $_SESSION)) {
				return $_SESSION[$key];
			} else {
				return null;
			}
		}

	}
?>