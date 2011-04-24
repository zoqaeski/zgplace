<?php

// Application class
require_once(dirname(__FILE__) . '/../lib/php/application.php');

//var_dump($_SERVER);

// Rather than a try/catch block here, let's rewrite it so it uses request/response handlers.
try {
	$zgplace = new Application();
	// Options
	$zgplace->setCaching(false);

	// Run application
	print $zgplace->run();
	// TODO
	// $response = $zgplace->run();
	// $response->send();
}
catch (Exception $e) {
	echo $e->getMessage(), "\n";
}

?>
