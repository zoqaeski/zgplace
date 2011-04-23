<?php

// Application class
require_once(dirname(__FILE__) . '/../include/php/application.php');

try {
	$zgplace = new Application();
	// Options
	$zgplace->setCaching(false);

	// Run application
	print $zgplace->run();
}
catch (Exception $e) {
	echo $e->getMessage(), "\n";
}

?>
