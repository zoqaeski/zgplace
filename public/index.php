<?php

use Symfony\Component\HttpFoundation\Request,
	Symfony\Component\HttpFoundation\Response;

// FINALLY got it to work.
spl_autoload_register(function ($className) {
	$phplib = dirname(__FILE__) . '/../lib/php/';
	$className = str_replace('\\', '/', $className);

	$possibilities = array(
		$phplib . 'modules/' . $className . '.php',
		$phplib . 'filters/' . $className . '.php',
		$phplib . 'modules/' . __NAMESPACE__ . '.php',
		$phplib . $className . '.php',
	);
	
	foreach($possibilities as $file) {
		//echo "Testing $file <br />";
		if(file_exists($file)) {
			//echo "<b>Found $file </b><br />";
			require_once($file);
			return true;
		}
	}
	return false;
}, true);


$request = Request::createFromGlobals();
$zgplace = new Application($request);

// Options
//$zgplace->setCaching(true);

// Run application
$response = $zgplace->run();
$response->send();

?>
