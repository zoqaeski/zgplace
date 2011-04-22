<?php
/*
 * zgTexy.php
 * ---------------
 * zgplace modified Texy routines
 *
 */

require_once(dirname(__FILE__) . '/../modules/texy/texy.php');

class TexyFilter extends Filter {

	private $texy;
	private $html;
	private $generated_toc;

	/**
	 * Constructs and then runs the Texy Filter
	 * @param pageData The metadata of the page we wish to filter. All generated content gets written to here.
	 * @param menuMeta The metadata of the menu for the page. We only read some 
	 * fields in here, so it's not passed as a reference. It defaults to null as we read meta-comments from a menu file, and reading the menu meta for the menu pulled in by the menu ... yeah, loopiness.
	 */
	function __construct($pageData, $menuMeta=null) {
		$this->pageData =& $pageData;

		if($this->pageData['path'] != null) {
			// Setup Texy
			$this->configureTexy();
			$this->file_content = file_get_contents($this->pageData['path']);

			// Parse meta comments in top of file
			$this->parseMetaComment();

			if($menuMeta != null) {
				$this->menuMeta = $menuMeta;
				$this->run();
			}
		} else {
			throw new RuntimeException("No file path specified.");
		}
	}

	/**
	 * This is where we set our options for Texy.
	 */
	private function configureTexy() {
		$this->texy = new Texy();

		// Configuring Texy
		$this->texy->headingModule->top = 1;   // set headings top limit
		$this->texy->headingModule->generateID = true;
		$this->texy->headingModule->moreMeansHigher = false;
		$this->texy->headingModule->balancing = TexyHeadingModule::FIXED;
		$this->texy->headingModule->levels['#'] = 0;  // # means 0 + top (1) = 1 (h1)
		$this->texy->headingModule->levels['='] = 1;  // = means 1 + top (1) = 2 (h2)
		$this->texy->headingModule->levels['-'] = 2;  // - means 1 + top (1) = 3 (h3)
		$this->texy->headingModule->levels['*'] = 3;  // * means 1 + top (1) = 4 (h4)
		$this->texy->headingModule->levels['.'] = 4;  // . means 1 + top (1) = 5 (h5)

		$this->texy->imageModule->root = Application::getPublicImgDir() . $this->pageData['srcdir'];
		$this->texy->imageModule->linkedRoot = Application::getPublicImgDir() . $this->pageData['srcdir'];

		$this->texy->typographyModule->locale = 'en';

		$this->texy->listModule->bullets = array(
			//            first-rexexp          ordered?  list-style-type   next-regexp
			'*'  => array('\*\ ',               0, ''),
			'-'  => array('[\x{2013}-](?![>-])',0, ''),
			'+'  => array('\+\ ',               0, ''),
			'1.' => array('1\.\ ',/* not \d !*/ 1, '',             '\d{1,3}\.\ '),
			'1)' => array('\d{1,3}\)\ ',        1, ''),
			'I.' => array('I\.\ ',              1, 'upper-roman',  '[IVX]{1,4}\.\ '),
			'I)' => array('[IVX]+\)\ ',         1, 'upper-roman'),
			'i.' => array('i\.\ ',              1, 'lower-roman',  '[ivx]{1,4}\.\ '),
			'i)' => array('[ivx]+\)\ ',         1, 'lower-roman'),
			'a.' => array('[a-z]\.\ ',          1, 'lower-alpha',	'[a-z]{1,3}\.\ '),
			'a)' => array('[a-z]\)\ ',          1, 'lower-alpha'),
			'A.' => array('[A-Z]\.\ ',          1, 'upper-alpha',	'[A-Z]{1,3}\.\) '),
			'A)' => array('[A-Z]\)\ ',          1, 'upper-alpha')
		);
	}

	/**
	 * Runs the Texy Filter
	 */
	protected function run() {
		$this->html = $this->texy->process($this->file_content);

		if($this->pageData['type'] != 'index' && $this->pageData['type'] != 'menu' && $this->pageData['maketoc'] == true) {
			$toc_elements = $this->texy->headingModule->TOC;
			if(sizeof($toc_elements) > 1) {
				$this->generated_toc = $this->generateTOC($toc_elements);
			}
		}

		// Ordered pages? No problems. Can has next/previous links
		if($this->menuMeta['ordered'] == true && $this->pageData['all_is_well'] ) {
			$this->buildTopicNavLinks($this->menuMeta['prev'], $this->menuMeta['next']);
		}

		if($this->pageData['title'] == null) {
			$this->pageData['title'] = $this->texy->headingModule->title;
		}

		$this->pageData['content'] = $this->finish();

	}

	/**
	 * Constructs the next/previous links for ordered page sets.
	 * @param $prev the previous page link
	 * @param $next the next page link
	 */
	private function buildTopicNavLinks($prev, $next) {
		$tn_links = Utils::buildTopicNavLinks($prev, $next);
		$this->html = $this->html . $tn_links['bottom'];
	}

	private function generateTOC($toc_elements) {
		// Create two arrays, one to keep track of levels, the other to keep track of contents
		$curr_level = 0;
		$prev_level = 0;
		$next_level = 0;

		// Initialise the string we want
		$toc_string = '<div class="toc"><span><a href="#" id="toctoggle">Contents</a></span>';

		// Build the TOC by looping through the data
		for($te = 1, $tes = count($toc_elements); $te < $tes; $te++) {
			$curr_level = $toc_elements[$te]['level'];
			if(($te + 1) != $tes) {
				$next_level = $toc_elements[$te + 1]['level'];
			} else {
				$next_level = 0;
			}

			if($curr_level > $prev_level) {
				$toc_string .= "\n". Utils::padStr($curr_level).'<ol>';
			}

			if($curr_level <= $prev_level) {
				$toc_string .= "</li>";
			}

			$toc_anchor = Utils::urlencode($toc_elements[$te]['title']);

			$toc_string .= "\n". Utils::padStr($curr_level + 1).'<li><a href="#'.$toc_anchor.'" title="">'.$toc_elements[$te]['title']."</a>";

			$cn_level_diff = $curr_level - $next_level;

			if($next_level == 0) {
				$cn_level_diff--;
			}

			while($cn_level_diff > 0) {
				$toc_string .= "</li>";
				$toc_string .= "\n". Utils::padStr($curr_level)."</ol>";
				$cn_level_diff--;
			}

			$prev_level = $curr_level;

		}

		$toc_string .= "</div>\n";

		//echo $toc_string;
		return $toc_string;
	}

	/**
	 * Finishing touches 
	 */
	private function finish() {
		$html_array = explode("\n", $this->html);

		if($this->generated_toc != null) {
			for($i = sizeof($html_array) - 1; $i > 0; --$i) {
				if(strrpos($html_array[$i], '</h1>') !== false) {
					$html_array[$i] = $html_array[$i] . "\n" . $this->generated_toc;
					break;
				}
			}
		}

		array_unshift($html_array, '<div id="body">');
		$html_array[] = '</div>';
		return implode("\n", $html_array);
	}

}
