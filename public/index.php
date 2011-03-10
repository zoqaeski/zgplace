<?php

//require_once("../include/php/simple_html_dom.php");
//require_once("../include/php/htmlfilter.php");
//require_once("../include/php/modules/simple_html_dom.php");
//require_once("../include/php/filters/html.php");
require_once("../include/php/application.php");

try {
	// echo "Index in Public";
	//echo $_GET['page'];
	$zgplace = new Application($_GET);
	// The old method of creating the application and then running it has since been deprecated.
	//$zgplace->run();
	//echo $zgplace->getFinalPage();
}
catch (Exception $e) {
	echo $e->getMessage(), "\n";
}

?>
