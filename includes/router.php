<?php
	class Router {
		static $controller = "";
		function url($relative, $complete = false)
		{
			if(isset($relative[0]) && $relative[0] == '/')
				return ($complete ? Settings::$Url : "") . Settings::$path . substr($relative, 1);
			else
				return ($complete ? Settings::$Url : "") . Settings::$path . Router::$controller . '/' . $relative;
		}
	}
?>