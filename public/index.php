<?php

// Application class
require_once(dirname(__FILE__) . '/../include/php/application.php');

try {
	$zgplace = new Application($_GET);
}
catch (Exception $e) {
	echo $e->getMessage(), "\n";
}

?>
