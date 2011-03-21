<?php

/*
 * TODO
 * - Devise a breadcrumb maker (should be easy now)
 * - Add return to top links?
 * - Separate HTMLFilter into Filter module and add test support for other inputs
 */

require_once("modules/simple_html_dom.php");
require_once("filters/html.php");

class Application {

	// The _GET variables
	private $getvars;

	/*
	// We store the final page in here. This should be a string.
	public $final_page;
	*/

	// The site name/toplevel index directory.
	private $toplevel = '/';

	// The directories where all pages are stored.
	private $public_dir = '../public/';
	private $public_html_dir = '../public/html/';

	// Errors are here.
	private $errors_dir = '../include/html/errors/';

	// Whether we have a 404 error.
	public $is_error_404 = false;
	//public $error_type = false;

	/*
	 * Creates a new Application.
	 * @param $getvars The _GET variables.
	 */
	public function __construct($getvars) {
		$this->getvars = $getvars;
		echo $this->run();
	}

	/*
	 * Runs the application.
	 */
	private function run() {
		$page = (string) $this->getvars['page'];
		$is_printview = (boolean) $this->getvars['print'];
		$is_index = false;
		$is_menu = false;
		$is_home = false;

		if(($page == '') || ($page == 'index') || ($page == 'home')) {
			$page = 'index';
			$is_home = true;
		}

		// First, we check to see if the file exists and we can read it
		if(file_exists($this->public_html_dir . $page . '.html')) {
			$path = $this->public_html_dir . $page. '.html';
		} elseif(file_exists($this->public_html_dir . $page . '/index.html')) {
			$path = $this->public_html_dir . $page . '/index.html';
			$is_index = true;
		} else {
			$this->is_error_404 = true;
			header('HTTP/1.1 404 Not Found');
			// Otherwise we load my customised 404 page.
			$path = $this->errors_dir . '404.html';
		}

		if($is_index) {
			$srcdir = $page;
		} else {
			$srcdir = dirname($page);
		}

		// The page topic is the toplevel directory.
		$path_dirs = explode('/', $page);
		$page_topic = $path_dirs[0];

		$pageDOM = new simple_html_dom($path);

		$page_htmlfilter = new HTMLFilter($pageDOM);

		// Get metadata
		$page_meta = $page_htmlfilter->parseMetaComment();
		
		// Get title
		$page_title = $page_htmlfilter->getTitle();

		// Generate content
		if( ! $this->is_error_404) {
			$page_content = $page_htmlfilter->applyFilter($srcdir);
		}

		// Load scripts
		$page_scripts = $page_htmlfilter->findScriptElements();

		$page_content = $pageDOM->find('body', 0)->innertext;

		if($is_printview) {
			// Make print page
			return $this->makepage($page_title, null, null, $page_content, null, true);
		} else {
			// Generate menu
			if($is_index) {
				$page_menu_path = dirname($this->public_html_dir . $page . "/index.html") . "/menu.html";
			} else {
				$page_menu_path = dirname($this->public_html_dir . $page) . "/menu.html";
			}

			$menu = $this->menugen($page_menu_path, $page_topic);

			// Make breadcrumb trail
			$breadcrumbs = $this->makebreadcrumbs($page_title, $is_home);

			// Make normal page
			return $this->makepage($page_title, $menu, $breadcrumbs, $page_content, $page_scripts, false);
		}
	}

	/*
	 * Generates the main menu by loading the page menu into the appropriate section.
	 * FIXED: We simplified the parsing of the menu by eliminating one massive DOM traversal object, as I know exactly what the main menu file looks like (I could strip it of the extra HTML but I won't: WHY?). Parsing the sub-menus however requires a DOM as I don't know whether sub-menus will have more than one level of <ul>
	 * @param $vm_path path to page menu
	 * @param $v_topic page topic
	 */
	private function menugen($vm_path, $v_topic) {
		// Main menu
		$mm_file = file_get_contents($this->public_html_dir . 'menu.html');
		preg_match('#<ul>(.*?)</ul>#s', $mm_file, $matches);
		$main_menu = str_get_html($matches[0]);
		
		// Local menu
		$vm_DOM = new simple_html_dom($vm_path);
		$vm_data = $vm_DOM->find('ul.levelTwo', 0)->outertext;

		// Determine if the page's topic is an entry in the main menu.
		if($v_topic != null) {
			$mm_topic = $main_menu->getElementById($v_topic);

			if($mm_topic != null) {
				$mm_topic->innertext = $mm_topic->innertext . $vm_data;
			}
		}

		return $main_menu->save();
	}

	/*
	 * Generates a breadcrumb-style trail.
	 * @param &$title The title of the page.
	 */
	private function makebreadcrumbs(&$title, $is_home) {
		if($is_home == false) {
			$path = $this->getvars['page'];
			$path_array = explode('/', $path);
			$path_hrefs = array();
			$ltsearch = array('/_/', '/\buk\b/');
			$ltreplace = array(' ', 'United Kingdom');

			for($p = sizeof($path_array) - 2; $p >= 0; $p--) {
				$link_text = ucwords(preg_replace($ltsearch, $ltreplace, $path_array[$p]));
				$path = substr($path, 0, strlen($path) - strlen(strrchr($path, '/')));
				$path_hrefs[$p] = '<a href="' .$this->toplevel . $path . '">' . $link_text . '</a>';
			}

			$trail = '';

			foreach($path_hrefs as $ph) {
				$trail = $ph . ' » ' . $trail;
			}

			$trail = '<p><a href="' . $this->toplevel .'">Home</a> » ' . $trail . $title . '</p>';
		} else {
			$trail = '<p>You are at the Home Page</p>';
		}
		//$trail .= $path_array[sizeof($path_array) - 1];

		return $trail;	
	}

	/*
	 * Constructs the final page from its constituent parts
	 * FIXED: Removed the reliance on a DOM parser for this and rely on simple string replaces. I haven't been able to discover if this minor change made a significant difference to the load time and computational load of each page request, but I suspect it has sped things up a little. 
	 * @param $title The page title, to be inserted before the zgplace part.
	 * @param $menu The constructed menu
	 * @param $breadcrumbs The constructed menu
	 * @param $content The page content, already filtered.
	 * @param $is_print Whether we want to hide the menus and page header.
	 */
	private function makepage($title, $menu, $breadcrumbs, $content, $scripts, $is_print) {
		// The skeleton page is loaded
		if($is_print == false) {
			$page_file = file_get_contents("../include/html/layout.html");
		}
		else {
			$page_file = file_get_contents("../include/html/print.html");
		}

		// Insert the new title
		if($title != null) {
			$page_file = preg_replace('#<title>(.+?)</title>#', '<title>'.$title.' @ $1</title>', $page_file);
		}

		if($scripts != null) {
			$page_file = preg_replace('#<head>(.+?)</head>#s', "<head>$1\n".$scripts.'</head>', $page_file);
		}

		// Insert the menu
		if($is_print == false || $menu != null) {
			$page_file = str_replace('<!--[SIDEBAR]-->', $menu, $page_file);
		}

		// Insert the breadcrumb trail
		if($is_print == false || $breadcrumbs != null) {
			$page_file = str_replace('<!--[BREADCRUMBS]-->', $breadcrumbs, $page_file);
		}

		// Set up the print-view links
		$print_box_href = "$this->toplevel";
		if($is_print) {
			$print_box_href = $print_box_href . $this->getvars['page'];
		}
		else {
			$print_box_href = $print_box_href . "print/" . $this->getvars['page'];
		}
		$page_file = str_replace('#PRINTBOXHREF', $print_box_href, $page_file);

		// Load the content
		if($content != null) {
			$page_file = str_replace('<!--[CONTENT]-->', $content, $page_file);
		}

		return $page_file;
	}
}
?>
