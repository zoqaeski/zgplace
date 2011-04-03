<?php
/*
 * application.php
 * ---------------
 * This is the core application used to build my site. 
 *
 */

/*
 * TODO
 * - Rewrite page builder so that it takes fewer variables.
 * - Separate HTMLFilter into Filter module and add test support for other inputs
 */

require_once(dirname(__FILE__) . '/modules/simple_html_dom.php');
require_once(dirname(__FILE__) . '/filters/html.php');

class Application {

	// The _GET variables
	private $getvars;

	// The site name/toplevel index directory.
	private $toplevel = '/';

	// The directory root where all pages are stored.
	private $public_dir = '../public/';
	private $public_content_dir = '../public/content/';

	// The source formats we have parsers for
	private $source_formats = array(
		'md',
		'html'); // HTML is last as it is the fallback option

	// The page types we can read
	private $page_type_is = array(
		'index' => false,
		'menu' => false,
		'home' => false);
		
	// Errors are here.
	private $errors_dir = '../include/errors/';

	// Whether we have a 404 error.
	public $is_error_404 = false;
	private $is_error = array(
		'404' => false);
	//public $error_type = false;

	// View modes
	private $views = array(
		0 => 'normal', 
		1 => 'print', 
		2 => 'source');

	const NORMAL_VIEW = 0;
	const PRINT_VIEW = 1;
	const SOURCE_VIEW = 2;

	// Timers
	private $starttime = 0;

	/*
	 * Creates a new Application.
	 * @param $getvars The _GET variables.
	 */
	public function __construct($getvars) {
		$this->starttime = microtime(true);
		$this->getvars = $getvars;
		echo $this->run();
	}

	/*
	 * Runs the application.
	 * Almost ready to be split into separate handlers for each data type. Woooooo!
	 */
	private function run() {
		$page = (string) $this->getvars['page'];
		$view = $this->getvars['view'];

		// Determining if we're on the home page.
		if(($page == '') || ($page == 'index') || ($page == 'home')) {
			$page = 'index';
			$this->page_type_is['home'] = true;
		}

		$page_data = $this->locatePage($page);

		// Set the menu path
		// Note that menu pages are ALWAYS html. I can't think of a way to read 
		// in other formats, despite the inherent simplicity of the actual menu 
		// pages.
		if($this->page_type_is['index']) {
			$page_data['srcdir'] = $page;
			$page_menu_path = dirname($this->public_content_dir . $page . "/index.html") . "/menu.html";
		} else {
			$page_data['srcdir'] = dirname($page);
			$page_menu_path = dirname($this->public_content_dir . $page) . "/menu.html";
		}

		// Initialise menu
		$menu_data = $this->getLocalMenu($page_menu_path, $page);

		// Create page DOM and initialise its metadata.
		if($page_data['format'] == 'html') {
			$pageDOM = new simple_html_dom($page_data['path']);
		} else {
			// This is where the other parsers will go.
			$pageDOM = null;
		}

		// Do not make a TOC for error pages.
		if($this->is_error['404']) {
			$page_data['maketoc'] = false;
		} else {
			$page_data['maketoc'] = true;		
		}

		// The topic is the top level directory inside content
		$path_dirs = explode('/', $page);
		$page_data['topic'] = $path_dirs[0];
		
		// Create our HTML filter. This also runs it and performs its magic, but YOU don't need to know that.
		// The potential security risk of sharing two objects between the Application and the HTMLFilter is lessened by not making any of the filter accessible to the outside world. We create it, it does its magic, and then it gets cleaned up. 
		$page_htmlfilter = new HTMLFilter($pageDOM, $page_data, $menu_data);

		// Filtering done, get content.
		$page_data['content'] = $pageDOM->find('body', 0)->innertext;

		// If we're printing, please do not generate breadcrumbs.
		if($view == self::PRINT_VIEW) {
			$menu_data['menu'] = null;
			$breadcrumbs = null;
		} else {
			$menu_data['menu'] = $this->menugen($menu_data['content'], $page_data['topic']);
			$breadcrumbs = $this->makebreadcrumbs($page_data['title']);
		}

		//echo 'Number of elements in our Page Data array: ' . sizeof($page_data) . '<br />';
		//echo 'Number of elements in our Menu Data array: ' . sizeof($menu_data);

		return $this->makepage($page_data, $menu_data, $breadcrumbs, $view);
	}

	/*
	 * Helper function to locate our pages from sets of HTML, markdown, and other source formats.
	 * @param $page The page we are requesting.
	 */
	private function locatePage($page) {
		$page_data = array();
		$page_found = false;

		foreach($this->source_formats as $format) {
			// Check for formats
			if(file_exists($this->public_content_dir . $page . '.' . $format)) {
				$page_data['path'] = $this->public_content_dir . $page. '.' . $format;
			} elseif(file_exists($this->public_content_dir . $page . '/index.' . $format)) {
				$page_data['path'] = $this->public_content_dir . $page . '/index.' . $format;
				$this->page_type_is['index'] = true;
			} else {
				$page_data['path'] = null;
			}

			// Jump on first found
			if($page_data['path'] != null) {
				$page_data['format'] = $format;
				break;
			}
		}

		// Not found? Ok, we has 404. Damn.
		if($page_data['path'] == null) {		
			$this->is_error['404'] = true;
			header('HTTP/1.1 404 Not Found');
			$page_data['path'] = $this->errors_dir . '404.html';
			$page_data['format'] = 'html';
		}

		return $page_data;
	}

	/*
	 * Generates the local menu and stores it in a key-value array.
	 * @param $lm_path path to the local menu
	 * @param $ref_page referring page
	 */
	private function getLocalMenu($lm_path, $ref_page) {
		$lm_DOM = new simple_html_dom($lm_path);
		$lm_meta = array();
		$lm_filter = new HTMLFilter($lm_DOM, $lm_meta);

		$lm_links = $lm_DOM->find('a');
		$lm_size = sizeof($lm_links);

		for($found = false, $c = 0; ! $found && $c < $lm_size;) {
			$f = strpos($lm_links[$c], $ref_page);
			if($f === false) {
				$found = false;
				++$c;
			} else {
				$found = true;
			}
		}

		$cl = $lm_links[$c];
		$cl->innertext = '→ ' . $cl->innertext;
		$lm_meta['curr'] = $cl;

		if($lm_meta['ordered'] == true) {
			if($c > 0) {
				$lm_meta['prev'] = $lm_links[$c - 1]->outertext;
			}

			if($c < $lm_size - 1) {
				$lm_meta['next'] = $lm_links[$c + 1]->outertext;
			}

			// Do I want these?
			$lm_meta['first'] = $lm_links[0]->outertext;
			$lm_meta['last'] = $lm_links[$lm_size - 1]->outertext;

		}

		$lm_meta['content'] = $lm_DOM->find('.levelTwo', 0)->outertext;
		return $lm_meta;
	}

	/*
	 * Generates the main menu by loading the page menu into the appropriate section.
	 * The main menu file is a HTML skeleton, which we import and extract just the menu list <ul>. This is then turned into a HTML DOM object that is browsed to find the appropriate section into which the local menu is inserted. It's a somewhat wasteful way of importing the data but I'm not entirely sure how to improve this.
	 * TODO Fixme so I use less resources.
	 * @param $lm_path path to page menu
	 * @param $page_topic page topic, or section to put generate menu data.
	 */
	private function menugen($lm_data, $page_topic) {
		// Main menu
		$mm_file = file_get_contents($this->public_content_dir . 'menu.html');
		preg_match('#<ul>(.*?)</ul>#s', $mm_file, $matches);
		$main_menu = str_get_html($matches[0]);

		// Determine if the page's topic is an entry in the main menu.
		if($page_topic != null) {
			$mm_topic = $main_menu->getElementById($page_topic);

			if($mm_topic != null) {
				$mm_topic->innertext = $mm_topic->innertext . $lm_data;
			}
		}

		return $main_menu->save();
	}

	/*
	 * Generates a breadcrumb-style trail.
	 * @param &$title The title of the page.
	 */
	private function makebreadcrumbs(&$title) {
		if($this->page_type_is['home'] == false) {
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

		return $trail;	
	}

	/*
	 * Constructs the final page from its constituent parts
	 * TODO Separate view handlers, so that the PRINT_VIEW mode is a different function. Tidier code see :)
	 * @param $pagemd The page metadata, including its title, content, etc.
	 * @param $menumd The menu metadata, including its content and other information.
	 * @param $breadcrumbs The breadcrumbs link list
	 * @param $is_print Whether we want to hide the menus and page header.
	 */
	private function makepage($pagemd, $menumd, $breadcrumbs, $view) {
		// The skeleton page is loaded
		if($view == self::PRINT_VIEW) {
			$page_file = file_get_contents("../include/html/print.html");
		}
		else {
			$page_file = file_get_contents("../include/html/layout.html");
		}

		// Insert the new title
		if($pagemd['title'] != null) {
			$page_file = preg_replace('#<title>(.+?)</title>#', '<title>'.$pagemd['title'].' @ $1</title>', $page_file);
		}

		if($pagemd['scripts'] != null) {
			$page_file = preg_replace('#<head>(.+?)</head>#s', "<head>$1\n".$pagemd['scripts'].'</head>', $page_file);
		}

		// Insert the menu
		if($view != self::PRINT_VIEW && $menumd['menu'] != null) {
			$page_file = str_replace('<!--[SIDEBAR]-->', $menumd['menu'], $page_file);
		}

		// Insert the breadcrumb trail
		if($view != self::PRINT_VIEW && $breadcrumbs != null) {
			$page_file = str_replace('<!--[BREADCRUMBS]-->', $breadcrumbs, $page_file);
		}

		// Set up the print-view links
		$print_box_href = "$this->toplevel";
		if($view == self::PRINT_VIEW) {
			$print_box_href = $print_box_href . $this->getvars['page'];
		}
		else {
			$print_box_href = $print_box_href . "print/" . $this->getvars['page'];
		}
		$page_file = str_replace('#PRINTBOXHREF', $print_box_href, $page_file);

		// Load the content
		if($pagemd['content'] != null) {
			$page_file = str_replace('<!--[CONTENT]-->', $pagemd['content'], $page_file);
		}

		// Finish our timer
		$endtime = microtime(true);
	   	$time = $endtime - $this->starttime;
		$time_msg = 'Page generated in approximately ' . round($time, 4) . ' seconds.';
		$page_file = str_replace('<!--[TIME]-->', $time_msg, $page_file);

		return $page_file;
	}

	private function makenormalpage($pagemd, $menumd, $breadcrumbs) {
	
	}

	private function makeprintpage($pagemd) {
	
	}

	private function makesourcepage($pagemd, $menumd, $breadcrumbs) {
	
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
	private function makepageold($title, $menu, $breadcrumbs, $content, $scripts, $is_print) {
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
