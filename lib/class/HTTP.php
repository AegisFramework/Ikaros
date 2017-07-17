<?php

    /**
	 * ==============================
	 * HTTP
	 * ==============================
	 */

    class HTTP {

        /**
         * Set the response type header
         *
         * @param string $type | Content Type
         * @param string $charset | Charset for response (Default: utf-8)
         */
        public static function type($type, $charset = 'utf-8'){
            $contentType = "";
            switch($type){

                case "json":
                    header("Content-Type: application/json;charset=$charset");
                    break;

                case "html":
                    header("Content-Type: text/html;charset=$charset");
                    break;
            }

        }

        /**
         * Send error response
         *
         * Set the error header to the response and build it's custom error
         * page adding the debug information if it's enabled. After printing
         * the page it dies.
         *
         * @param int $code | HTTP Error Code
         * @param int $number | PHP Error Number
         * @param string $message | Error Description
         * @param string $file | File In Which The Error Is
         * @param int $line | Line Number
         *
         */
        public static function error($code, $number = null, $message = null, $file = null, $line = null){
			HTTP::type("html");
            $error = new Template();
            $error -> setContent(file_get_contents(__DIR__."/../../error/error.html"));
            if(Aegis::$debugging){
                $error -> data["description"] = "<p><b>OS:</b> ".PHP_OS."</p>";
                $error -> data["description"] .= "<p><b>PHP Version:</b> ".PHP_VERSION."</p>";
			    $error -> data["description"] .= "<p><b>Aegis Flavor:</b> ".Aegis::$flavor."</p>";
			    $error -> data["description"] .= "<p><b>Version:</b> ".Aegis::$version."</p>";
			    if($number != null){
			        $error -> data["description"] .= "<p><b>Error Code:</b> $number</p>";
    			    $error -> data["description"] .= "<p><b>Message:</b> $message</p>";
    			    $error -> data["description"] .= "<p><b>File:</b> $file</p>";
     			    $error -> data["description"] .= "<p><b>Line:</b> $line</p>";
			    }
            }
            switch($code){
                case 400:
					header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", true, 404);
					$error -> data["title"] = "Bad Request";
					$error -> data["message"] = "The request is invalid.";
					break;
				case 404:
					header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
					$error -> data["title"] = "Page Not Found";
					$error -> data["message"] = "Sorry, the page you are trying to access does not exist.";
					break;

				case 500:
					header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error", true, 500);
					$error -> data["title"] = "Server Error";
					$error -> data["message"] = "Sorry, it seems there's been an error. Please try later.";
					break;
			}
			if(Aegis::$debugging){
			    $error -> data["description"] = "<div>".$error -> data["description"]."</div>";
			}else{
			    $error -> data["description"] = "";
			}
			$error -> compile();
			echo $error;
			die();
        }

    }

?>