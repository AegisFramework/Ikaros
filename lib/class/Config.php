<?php

	/**
	* ==============================
	* Config
	* ==============================
	*/

	class Config {

		/**
		 * Get a property from the .conf file
		 *
		 * This method will check if the .conf file exists, if it does it will
		 * read it's contents, and search with a regex for the given expression
		 * if the expression exists, then it will return the right side of it
		 * which is the value of the property.
		 *
		 * @param string $porperty | Property To Retrieve
		 *
		 * @return string or null | Property Value Or Null If Non Existent
		 */
		public static function get($property){
			if(FileSystem::fileExists(__DIR__."/../../.conf")){
				$content = FileSystem::read(__DIR__."/../../.conf");
				preg_match('/'.$property.'\s=\s.*/', $content, $matches);
				if(count($matches) > 0){
					$expression = explode(" = ", $matches[0]);
					return $expression[1];
				}else{
					return null;
				}
			}
		}
	}
?>