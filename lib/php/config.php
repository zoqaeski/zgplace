<?php
/*
 * config.php
 * ----------
 * Configuration file for the application
 */

class Config {
//	NOTHING TO DO
//	// The _GET variables
//	private $getvars;
//
//	// The directory root where all pages are stored.
//	public static $public_dir = '../public/';
//	public static $public_content_dir = '../public/content/';
//
//	// The following directories are relative to the document root, not this file path.
//	public static $public_img_dir = '/public/img/';
//	//public static $public_script_dir = '/public/img/';
//
//	// The source formats we have parsers for. Note that the order here is VERY important: the locatePage() function will return the file name matching the first format in this list.
//	private $source_formats = array(
//		'.html',
//		'.texy',
//		'.txt',
//		''); // Unspecified format. Potential to add guessing trick here; can PHP do MIME?
//
//	// The page types we can read
//	private $page_type_is = array(
//		'index' => false,
//		'menu' => false,
//		'home' => false);
//		
//	// Errors are here.
//	private $errors_dir = '../include/errors/';
//
//	// Whether we have a 404 error.
//	private $is_error = false;
//   	private $error_type = array(
//		'404' => false, // Not Found
//		'415' => false, // Unsupported media type
//		'418' => false, // I'm a teapot
//		'500' => false, // Internal Server Error
//		'501' => false  // Not Implemented
//	);
//
//	// View modes
//	private $views = array(
//		0 => 'normal', 
//		1 => 'print', 
//		2 => 'source');
//
	/**
	 * Static class.
	 */
	final public function __construct()
	{
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}
}
