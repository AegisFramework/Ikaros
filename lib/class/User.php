<?php
/**
 * ==============================
 * User
 * ==============================
 */
	class User {

		/**
		 * Get User's real IP even if it's using a Proxy.
		 *
		 * @access public
		 * @return string | boolean - returns Ip if it's valid or false if it's invalid.
		 */
		public static function ip(){
			if (!empty($_SERVER["HTTP_CLIENT_IP"])){
				//check for ip from share internet
				$ip = $_SERVER["HTTP_CLIENT_IP"];

			}elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
				// Check for the Proxy User
		        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			}else{
		        $ip = $_SERVER["REMOTE_ADDR"];
			}
			return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
		}

		public static function agent(){
			if(empty($_SERVER['HTTP_USER_AGENT'])){
				return "";
			}else{
				return $_SERVER['HTTP_USER_AGENT'];
			}
		}
	}
?>