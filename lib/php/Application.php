<?php
/*
 * application.php
 * ---------------
 * This is the core application used to build my site. 
 *
 */

/*
 * TODO
 * - Add Request/Response handlers
 * - Make a configuration file
 */
use Symfony\Component\HttpFoundation\Request,
	Symfony\Component\HttpFoundation\Response;

//require_once(dirname(__FILE__) . '/modules/utils.php');
//require_once(dirname(__FILE__) . '/modules/cache.php');
require_once(dirname(__FILE__) . '/modules/simple_html_dom.php');
//require_once(dirname(__FILE__) . '/filter.php');
//require_once(dirname(__FILE__) . '/filters/HTMLFilter.php');
//require_once(dirname(__FILE__) . '/filters/TexyFilter.php');

class Application {

	/** @var The response we will send */
	protected $response;

	/** @var The request we are handling */
	protected $request;

	/** @var string The referring URI */
	private $uri;

	/** @var string The directory root where all public content is stored. */
	private static $public_dir = '../public';

	/** @var string The directory root where all pages are stored. */
	private static $public_content_dir = '../public/pages';

	/** @var string The directory root where generated pages may be cached. This directory must have write-access for PHP. */
	private static $cache_dir = '../cache/';

	/** @var bool Whether caching is enabled. */
	private static $use_cache_file;

	/** @var bool Whether we should force a reload of the page to clean the cache. */
	private static $force_cache_reload;

	// The following directories are relative to the document root, not this file path.
	/** @var string The document root. */
	private static $toplevel = '/';

	/** @var string The directory where all images are stored. */
	private static $public_img_dir = '/img';
	//private $public_script_dir = '/public/img/';

	/** @var array The source formats we have parsers for. Note that the order here is VERY important: the locatePage() function will return the file name matching the first format in this list. */
	private static $source_formats = array(
		'.texy',
		'.html');

	/** @var string The directory where error pages are stored. */
	private static $errors_dir = '../lib/errors/';

	/** @var string The directory where the HTML templates are stored */
	private static $template_dir = '../lib/html/';

	/** @var array Path search strings for dynamic replacement of certain characters. Useful in the breadcrumb function */
	private static $path_search = array(
		'/_/', 
		'/\buk\b/'
	);

	/** @var array Path replace strings for $path_search */
	private static $path_replace = array(
		' ', 
		'United Kingdom'
	);

	/** @var string The meta comment replace string */
	private static $metacomment_preg = "/^(\S+):\s([ \S]+)$/im";

	// TODO create a response and request class to handle these things.

	/** @var bool Whether we're on the home page. */
	private $is_home = false;

	/** @var bool Whether we're on the site map. */
	private $is_sitemap = false;

	/** @var object The site map generator. */
	private $sitemap;

	/** @var int Which view mode we are using, such as normal, print view, source view, etc. */
	private $view = 0;

	const NORMAL_VIEW = 0;
	const PRINT_VIEW = 1;
	const SOURCE_VIEW = 2;

	/** @var int Time for application to process. */
	private $time;

	/** @var object The cache generator. */
	private $cache;

	/**
	 * Creates a new Application.
	 * @param $request The request being made. TODO
	 */
	public function __construct($request) {
		$this->request = $request;
		$this->response = new Response();
		$this->time = -microtime(true);
	}

	private function processURI() {
		$uri = $this->request->getPathInfo();

		// Determining if we're on the home page.
		if(($uri === null) || ($uri === '/' ) || ($uri === '/index') || ($uri === '/home')) {
			$uri = '/index';
			$this->is_home = true;
		} elseif($uri === '/sitemap') {
			// TODO
			$this->is_sitemap = true;
			$this->sitemap = new Sitemap();
			$this->sitemap->makeTexyStr();
			//echo $this->sitemap;
		}

		$this->uri = $uri;
	}

	private function processGET() {
		$getvars = $this->request->query->all();

		// Dynamically enable/disable caching
		if(array_key_exists('cache', $getvars)) {
			self::$use_cache_file = Utils::str_to_bool($getvars['cache']);
			if(self::$use_cache_file == false) {
				self::$force_cache_reload = true;
			}
		}

		// Print view mode
		if(array_key_exists('print', $getvars)) {
			$this->view = self::PRINT_VIEW;
			self::$use_cache_file = false;
			self::$force_cache_reload = false;
		}

	}

	/**
	 * Runs the application.
	 * Almost ready to be split into separate handlers for each data type. Woooooo!
	 * @return string
	 */
	public function run() {
		$this->processURI();
		$this->processGET();
		$page = $this->uri;
		$page_data = array();

		$page_data = $this->locatePage($page, $page_data);

		if($this->is_sitemap) {
			$page_data['input'] = $this->sitemap->getSiteTreeTexy();
			// Caching the site map will require much more thought. Do-able, but not yet.
			// I think to implement this properly I'm going to have to store the list of pages somewhere.
			self::$force_cache_reload = true;
		}

		// If caching is enabled, check for a cached version of the (complete) page. 
		// Note that while this speeds up loading and page processing, if one of the 
		// components changes this may not be detected. I'll implement a cache management 
		// class at some point.
		// As an interim measure, use $force_cache_reload to achieve the same goal.
		if(self::$use_cache_file == true || self::$force_cache_reload == true) {
			$this->cache = new PageCache();
			$page_data['hash'] = $this->cache->makeHashOfFile($page_data['path'], false);
			//$this->cache->updateHashOfFile($page_data['path'], false);

			if(self::$force_cache_reload !== true) {
				$cached_data = $this->cache->loadCacheFile($page_data['hash']);
				if($cached_data === false) {
					$this->cache->cleanCache($page_data['path']);
				} else {
					$this->response->setContent($this->addTimeStamp($cached_data['generated_page']));
					return $this->response;
				}
			}
		}

		// Set the menu path
		if($page_data['type'] == 'index') {
			$page_data['sitedir'] = $page;
			$page_data['srcdir'] = dirname(self::$public_content_dir . $page . "/index");
		} else {
			$page_data['sitedir'] = dirname($page);
			$page_data['srcdir'] = dirname(self::$public_content_dir . $page);
		}

		// The topic is the top level directory inside content
		// This can also be set by a flag in the file if we want.
		$path_dirs = explode('/', $page);
		$page_data['topic'] = $path_dirs[1]; // $page now starts with a slash!

		// Initialise menu
		$menu_data = $this->getLocalMenu($page_data['srcdir'], $page_data['sitedir'], $page);

		// Do not make a TOC for error pages, index pages, and menus.
		// This can be reset for certain pages.
		if(! $page_data['all_is_well'] || $page_data['type'] == 'index' || $page_data['type'] == 'menu') {
			$page_data['maketoc'] = false;
		} else {
			$page_data['maketoc'] = true;		
		}

		// This is where the other parsers will go. They MUST return HTML.
		if($page_data['format'] == '.html') {
			$page_data = $this->parseHTML($page_data, $menu_data);
		} elseif($page_data['format'] == '.texy') {
			// The Texy parser needs some adjustments yet.
			$page_data = $this->parseTexy($page_data, $menu_data);
		} else {
			$page_data['all_is_well'] = false;
			$this->response->setStatusCode(500);
			//throw new RuntimeException("File format invalid.");
			$page_data['content'] = null;
		}

		// If we're printing, please do not generate breadcrumbs.
		if($this->view == self::PRINT_VIEW) {
			$menu_data['menu'] = null;
			$breadcrumbs = null;
		} else {
			$menu_data['menu'] = $this->menuGen($menu_data['menu'], $page_data['topic']);
			$breadcrumbs = $this->makeBreadCrumbs($page_data['title']);

			// Add a hash for the menu.
			//if(self::$use_cache_file === true) {
			//	$menu_data['hash'] = $this->cache->makeHash($menu_data);
			//}

		}

		$page_data['generated_page'] = $this->makePage($page_data, $menu_data, $breadcrumbs);

		// Include the menu? Works now. Please remember to not store objects.
		//$page_data['menu'] = $menu_data['menu'];

		// $page_data['generated_page'] contains the final page. 
		// So we can dump the content field. And the menu field.
		unset($page_data['content']);
		//unset($page_data['menu']);

		if($page_data['all_is_well'] === true && self::$use_cache_file == true || self::$force_cache_reload == true) {
			if($page_data['hash'] !== false) {
				$cache_file_name = self::$cache_dir . $page_data['hash'];
				$this->cache->saveCacheFile($page_data, $cache_file_name);
				$this->cache->logCacheEntry($page_data['hash'], $page_data['path'], null, true);
			} else {
				echo "WARNING: File didn't have hash. This entry is not logged.";
				$this->cache->saveCacheFile($page_data);
			}
		}

		$this->response->setContent($this->addTimeStamp($page_data['generated_page']));
		return $this->response;
	}

	/**
	 * HTML parsing function, used when our source format is HTML.
	 * @param $page_data The Page Data array.
	 * @param $menu_data The Menu Data array.
	 * @return array
	 */
	private function parseHTML($page_data, $menu_data) {
		// Create our HTML filter. This also runs it and performs its magic, 
		// but YOU don't need to know that. The potential security risk of 
		// sharing two objects between the Application and the HTMLFilter is 
		// lessened by not making any of the filter accessible to the outside 
		// world. We create it, it does its magic, and then it gets cleaned up. 
		$page_htmlfilter = new HTMLFilter($page_data, $menu_data);
		$page_data = $page_htmlfilter->getData();

		return $page_data;
	}

	/**
	 * Texy parsing function, used when our source format is Texy (http://www.texy.info)
	 * @param $page_data The Page Data array.
	 * @param $menu_data The Menu Data array.
	 * @return array
	 */
	private function parseTexy($page_data, $menu_data) {
		// MOSTLY IMPLEMENTED
		// Still need to figure out a few thingses
		$page_texyfilter = new TexyFilter($page_data, $menu_data);
		$page_data = $page_texyfilter->getData();
		return $page_data;
	}

	/**
	 * Helper function to locate our pages from sets of HTML, Texy, and other 
	 * source formats.
	 * @param $page The page we are requesting.
	 * @return array
	 */
	private function locatePage($page, $page_data) {
		$page_found = false;
		$page_readable = true;

		// Check for formats. The order in source_formats array is very important.
		foreach(self::$source_formats as $format) {
				$page_name = self::$public_content_dir . $page . $format;
				$index_name = self::$public_content_dir . $page . '/index' . $format;

				if(file_exists($page_name)) {
					if(is_readable($page_name)) {
						$page_data['path'] = $page_name;
						$page_data['type'] = 'page';
						$page_found = true;
					} else {
						$page_readable = false;
					}
				} elseif(file_exists($index_name)) {
					if(is_readable($index_name)) {
						$page_data['path'] = $index_name;
						$page_data['type'] = 'index';
						$page_found = true;
					} else {
						$page_readable = false;
					}
				} else {
					$page_found = false;
				}

			// Jump on first found
			if($page_found == true) {
				$page_data['format'] = $format;
				$page_data['all_is_well'] = true;
				break;
			} elseif($page_readable == false) {
				$page_data['all_is_well'] = false;
				break;
			}
		}

		// TODO: Add format error here.

		// Not found? Ok, we has 404. Damn.
		if($page_found == false) {		
			$this->response->setStatusCode(404);
			$page_data['path'] = self::$errors_dir . '404.texy';
			$page_data['format'] = '.texy';
			$page_data['type'] = 'error';
			$page_data['all_is_well'] = false;
		} elseif($page_readable == false) {
			$this->response->setStatusCode(403);
			$page_data['path'] = self::$errors_dir . '404.texy';
			//$page_data['path'] = self::$errors_dir . '403.html';
			$page_data['format'] = '.html';
			$page_data['type'] = 'error';
		}

		return $page_data;
	}

	/**
	 * Generates the local menu and stores it in a key-value array.
	 * @param $lm_path path to the local menu
	 * @param $ref_page referring page
	 * @return array
	 */
	private function getLocalMenu($ref_page_dir, $ref_page_site_dir, $ref_page) {
		// Menus are by default non-ordered. If we want a menu file, add 
		// ordered: true to the first comment in the file. Everything else is 
		// fairly self-explanatory.
		$lm_meta = array(
			'ordered' => false,
			'sitedir' => $ref_page_site_dir,
			'menu' => ''
		);

		$menu_found = false;
		$menu_format = null;

		foreach(self::$source_formats as $format) {
			$menu_name = $ref_page_dir . '/menu' . $format;

			if(file_exists($menu_name) && is_readable($menu_name)) {
				$lm_meta['path'] = $menu_name;
				$lm_meta['type'] = 'menu';
				$menu_found = true;
			} else {
				$page_readable = false;
				$menu_found = false;
			}

			// Jump on first found
			if($menu_found == true) {
				$menu_format = $format;
				break;
			}
		}

		if($menu_found) {

			if($menu_format === '.texy') {
				$lm_filter = new TexyFilter($lm_meta);
				$lm_meta = $lm_filter->getData();

				// Interim solution. Oh god it's bad. DO NOT USE unless you're 
				// prepared to put up with extra server load.
				// TODO Figure out how to split this function into distinct 
				// HTML and Texy modes.
				$lm_DOM = new simple_html_dom();
				$lm_DOM->load($lm_filter->getHTML());
				//$lm_texy = $lm_filter->getTexy();

			} elseif($menu_format === '.html') {
				$lm_filter = new HTMLFilter($lm_meta);
				$lm_meta = $lm_filter->getData();
				$lm_DOM = $lm_filter->getDOM();
			}

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
			$lm_meta['curr'] = $cl->outertext;

			if($lm_meta['ordered'] == true) {
				if($c > 0) {
					//$link_element = $lm_links[$c - 1]->outertext;
					$link_text = $lm_links[$c - 1]->innertext;
					$link_href = $lm_links[$c - 1]->href;
					$lm_meta['prev'] = '<a href="'. $link_href .'">Previous: '. $link_text .'</a>';
				} else {
					$lm_meta['prev'] = null;
				}

				if($c < $lm_size - 1) {
					//$lm_meta['next'] = $lm_links[$c + 1]->outertext;
					$link_text = $lm_links[$c + 1]->innertext;
					$link_href = $lm_links[$c + 1]->href;
					$lm_meta['next'] = '<a href="'. $link_href .'">Next: '. $link_text .'</a>';
				} else {
					$lm_meta['next'] = null;
				}

				// Do I want these?
				//$lm_meta['first'] = $lm_links[0]->outertext;
				//$lm_meta['last'] = $lm_links[$lm_size - 1]->outertext;

			}

			$lm_meta['menu'] = $lm_DOM->find('.levelTwo', 0)->outertext;
		} 
		return $lm_meta;
	}

	/**
	 * Generates the main menu by loading the page menu into the appropriate section.
	 * The main menu file is a HTML skeleton, which we import and extract just 
	 * the menu list <ul>. This is then turned into a HTML DOM object that is 
	 * browsed to find the appropriate section into which the local menu is 
	 * inserted. It's a somewhat wasteful way of importing the data but I'm not 
	 * entirely sure how to improve this.
	 * TODO Fixme so I use less resources.
	 * @param $local_menu The local menu resource
	 * @param $page_topic page topic, or section to put generate menu data.
	 * @return string
	 */
	private function menuGen($local_menu, $page_topic) {
		// Main menu
		$mm_file = file_get_contents(self::$public_content_dir . '/menu.html');
		preg_match('#<ul>(.*?)</ul>#s', $mm_file, $matches);
		$main_menu = str_get_html($matches[0]);

		// Determine if the page's topic is an entry in the main menu.
		if($page_topic != null) {
			$mm_topic = $main_menu->getElementById($page_topic);

			if($mm_topic != null) {
				$mm_topic->innertext = $mm_topic->innertext . $local_menu;
			}
		}

		return $main_menu->save();
	}

	/**
	 * Generates a breadcrumb-style trail.
	 * @param &$title The title of the page.
	 * @return string
	 */
	private function makeBreadCrumbs(&$title) {
		if($this->is_home == false) {
			$path = $this->uri;
			$path_array = explode('/', $path);
			$path_hrefs = array();

			for($p = sizeof($path_array) - 2; $p > 0; --$p) {
				$link_text = ucwords(preg_replace(self::$path_search, self::$path_replace, $path_array[$p]));
				$path = substr($path, 0, strlen($path) - strlen(strrchr($path, '/')));
				$path_hrefs[$p] = '<a href="' . $path . '">' . $link_text . '</a>';
			}

			$trail = '';

			foreach($path_hrefs as $ph) {
				$trail = $ph . ' » '. $trail;
			}

			$trail = '<p><a href="' . self::$toplevel .'">Home</a> » ' . $trail . $title . '</p>';
		} else {
			$trail = '<p>You are at the Home Page</p>';
		}

		return $trail;	
	}

	/**
	 * Constructs the final page from its constituent parts
	 * TODO Separate view handlers, so that the PRINT_VIEW mode is a different 
	 * function. Tidier code see :)
	 * @param $pagemd The page metadata, including its title, content, etc.
	 * @param $menumd The menu metadata, including its content and other information.
	 * @param $breadcrumbs The breadcrumbs link list
	 * @return string
	 */
	private function makePage($pagemd, $menumd, $breadcrumbs) {
		// The skeleton page is loaded
		if($this->view == self::PRINT_VIEW) {
			$page_file = file_get_contents(self::$template_dir . 'print.html');
		}
		else {
			$page_file = file_get_contents(self::$template_dir . 'layout.html');
		}

		// Insert the new title
		if($pagemd['title'] != null) {
			$page_file = preg_replace('#<title>(.+?)</title>#', '<title>'.$pagemd['title'].' @ $1</title>', $page_file);
		}

		if($pagemd['scripts'] != null) {
			$page_file = preg_replace('#<head>(.+?)</head>#s', "<head>$1\n".$pagemd['scripts'].'</head>', $page_file);
		}

		// Insert the menu
		if($this->view != self::PRINT_VIEW && $menumd['menu'] != null) {
			$page_file = str_replace('<!--[SIDEBAR]-->', $menumd['menu'], $page_file);
		}

		// Insert the breadcrumb trail
		if($this->view != self::PRINT_VIEW && $breadcrumbs != null) {
			$page_file = str_replace('<!--[BREADCRUMBS]-->', $breadcrumbs, $page_file);
		}

		// Set up the print-view links
		$print_box_href = self::$toplevel;
		if($this->view == self::PRINT_VIEW) {
			$print_box_href = $this->uri;
		}
		else {
			$print_box_href = $this->uri . '?print';
		}
		$page_file = str_replace('#PRINTBOXHREF', $print_box_href, $page_file);

		// Set up cache toggling
		if($this->view != self::PRINT_VIEW) {
			if(self::$use_cache_file == true) {
				$cache_view_href = $this->uri . '?cache=false';
				//$cache_view_text = 'Disable Cache';
			} else {
				$cache_view_href = $this->uri . '?cache=true';
				//$cache_view_text = 'Disable Cache';
			}
			$page_file = str_replace('#CACHEMODE', $cache_view_href, $page_file);
			//$page_file = str_replace('Refresh Cache', $cache_view_text, $page_file);
		}

		// Load the content
		if($pagemd['content'] != null) {
			$page_file = str_replace('<!--[CONTENT]-->', $pagemd['content'], $page_file);
		}

		return $page_file;
	}

	/**
	 * Adds a time stamp to the generated page.
	 * @param $page_file The generated page.
	 * @return string
	 */
	private function addTimeStamp($page_file) {
		// Finish our timer
		$this->time += microtime(true);
		$time_msg = 'Page generated in approximately ' . round($this->time, 4) . ' seconds.';
		$page_file = str_replace('<!--[TIME]-->', $time_msg, $page_file);
		return $page_file;
	}

	//---------------------------------[GETTERS]

	/**
	 * Returns the path to the public directory.
	 * @return string
	 */
	public function getPublicDir() {
		return self::$public_dir;
	}

	/**
	 * Returns the path to the public content directory.
	 * @return string
	 */
	public static function getPublicContentDir() {
		return self::$public_content_dir;
	}

	/**
	 * Returns the path to the cache directory.
	 * @return string
	 */
	public static function getCacheDir() {
		return self::$cache_dir;
	}

	/**
	 * Returns the path to the public images directory.
	 * @return string
	 */
	public static function getPublicImgDir() {
		return self::$public_img_dir;
	}

//	/**
//	 * Returns the path to the public images directory.
//	 * @return string
//	 */
//	public static function getPublicScriptDir() {
//		return self::$public_script_dir;
//	}

	/**
	 * Returns the path to the errors directory.
	 * @return string
	 */
	public function getErrorsDir() {
		return self::$errors_dir;
	}
	
	/**
	 * Returns an array of the source formats enabled. The order of the array is the order in which parsers will be tried.
	 * @return array
	 */
	public function getSourceFormats() {
		return self::$source_formats;
	}

	/**
	 * Returns the preg match string for meta comments.
	 * @return string
	 */
	public static function getMetacommentPreg() {
		return self::$metacomment_preg;
	}

	/**
	 * Returns the preg search string for path replacements
	 * @return string
	 */
	public static function getPathSearch() {
		return self::$path_search;
	}

	/**
	 * Returns the preg replace string for path replacements
	 * @return string
	 */
	public static function getPathReplace() {
		return self::$path_replace;
	}

	//--------------------------------[SETTERS]

	/**
	 * Returns the path to the public directory.
	 * @return string
	 */
	public function setPublicDir($dir) {
		self::$public_dir = $dir;
	}

	/**
	 * Returns the path to the public content directory.
	 * @return string
	 */
	public static function setPublicContentDir($dir) {
		self::$public_content_dir = $dir;
	}

	/**
	 * Sets the path to the cache directory.
	 * @param $dir The new cache directory path.
	 */
	public static function setCacheDir($dir) {
		self::$cache_dir = $dir;
	}

	/**
	 * Sets the path to the cache directory.
	 * @param $dir The new cache directory path.
	 */
	public static function setCaching($enabled=true) {
		self::$use_cache_file = $enabled;
	}

	/**
	 * Returns the path to the public images directory.
	 * @return string
	 */
	public static function setPublicImgDir($dir) {
		self::$public_img_dir = $dir;
	}

//	/**
//	 * Returns the path to the public images directory.
//	 * @return string
//	 */
//	public static function setPublicScriptDir() {
//		return self::$public_script_dir;
//	}

	/**
	 * Returns the path to the errors directory.
	 * @param $dir the new directory
	 */
	public function setErrorsDir($dir) {
		self::$errors_dir = $dir;
	}
	
//	/**
//	 * Returns an array of the source formats enabled. The order of the array is the order in which parsers will be tried.
//	 * @return array
//	 */
//	public function setSourceFormats() {
//		return self::$source_formats;
//	}

}
