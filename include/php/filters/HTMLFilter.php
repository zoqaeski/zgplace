<?php

class HTMLFilter extends Filter {

	/** @var The page DOM element used internally for filtering. */
	private $pageDOM;

	/**
	 * Constructs and then runs the HTMLFilter
	 * @param pageData The metadata of the page we wish to filter. All generated content gets written to here.
	 * @param menuMeta The metadata of the menu for the page. We only read some 
	 * fields in here, so it's not passed as a reference. It defaults to null as we read meta-comments from a menu file, and reading the menu meta for the menu pulled in by the menu ... yeah, loopiness.
	 */
	function __construct($pageData, $menuMeta=null) {
		$this->pageData =& $pageData;

		if($this->pageData['path'] != null) {

			$this->file_content = file_get_contents($this->pageData['path']);

			// Parse meta comments in top of file
			$this->parseMetaComment();

			// Create page DOM from the path to the file.
			$this->pageDOM = new simple_html_dom();
			$this->pageDOM->load($this->file_content);

			if($menuMeta != null) {
				$this->menuMeta = $menuMeta;
				$this->run();
			}
		} else {
			throw new RuntimeException("No file path specified.");
		}
	}


	/**
	 * Returns the pageDOM object
	 */
	public function getDOM() {
		return $this->pageDOM;
	}

	/**
	 * Applies the filter.
	 */
	protected function run() {
		// Get title
		$this->pageData['title'] = $this->getTitle();

		// Extract scripties
		$this->pageData['scripts'] = $this->findScriptElements();

		// Modify image src links
		$this->modifyImgs();

		// Generate a TOC based on heading hierarchy
		if($this->pageData['type'] != 'index' && $this->pageData['type'] != 'menu' && $this->pageData['maketoc'] == true) {
			$pageHeadings = $this->addAnchors();
			if(count($pageHeadings) > 1) {
				$h_ones = $this->pageDOM->find('h1');
				$toc_place = $h_ones[count($h_ones) - 1];
				$toc_place->outertext = $toc_place->outertext . $this->generateTOC($pageHeadings);
			}
		}

		// Ordered pages? No problems. Can has next/previous links
		if($this->menuMeta['ordered'] == true && $this->pageData['all_is_well'] ) {
			$this->buildTopicNavLinks($this->menuMeta['prev'], $this->menuMeta['next']);
		}

		// Filtering done, get content.
		$this->pageData['content'] = $this->pageDOM->find('body', 0)->innertext;

	}

	/**
	 * Returns the page title
	 */
	private function getTitle() {
		return $this->pageDOM->find('title', 0)->plaintext;
	}

	/**
	 * Constructs the next/previous links for ordered page sets.
	 * @param $prev the previous page link
	 * @param $next the next page link
	 */
	private function buildTopicNavLinks($prev, $next) {
		$tn_links = Utils::buildTopicNavLinks($prev, $next);

		$tn_place = $this->pageDOM->find("#body", 0);
		$tn_place->innertext = $tn_place->innertext . $tn_links['bottom'];
	}

	/**
	 * Finds <script> tags in some pages and extracts them. This is a potential
	 * security hole (if we allow users to create pages and scripts, so I'll need to
	 * patch it and devise a means to permit only scripts I have authorised.
	 */
	private function findScriptElements() {
		$scriptElements = $this->pageDOM->find('script');
		$scriptStr = "";
		foreach($scriptElements as $scriptElement) {
			$script = $scriptElement->outertext;
			$scriptStr = $scriptStr . $script;	
			$scriptElement->outertext = '';
		}

		return $scriptStr;
	}

	/**
	 * Modifies image src links to point to the appropriate location. 
	 * Images are stored in a mirrored hierarchy, so a page in 
	 * /content/topic/section/page would have images stored in 
	 * /img/topic/section/ .
	 */
	private function modifyImgs() {
		$imgs = $this->pageDOM->find('img');

		for($i = 0, $is = count($imgs); $i < $is; $i++) {
			if($imgs[$i]->src) {
				$url = $imgs[$i]->src;
				$imgs[$i]->src = Application::getPublicImgDir() . $this->pageData['sitedir'] . '/' . $url;
			}
		}
	}

	/**
	 * Finds all headings in a document and adds anchors to them. These are then turned into permalinks. 
	 */
	private function addAnchors() {
		// Ignore <h1> as it is the title of the page
		$headings = $this->pageDOM->find('h2, h3, h4, h5, h6');
		$htexts = array();

		for($h = 0, $hs = count($headings); $h < $hs; $h++) {
			$anchor = Utils::urlencode($headings[$h]->plaintext);
			$htexts[$h] = $headings[$h]->tag . ':' . $headings[$h]->plaintext;
			$headings[$h]->id = $anchor;
			$headings[$h]->innertext = $headings[$h]->plaintext.'<a class="permalink" href="#'.$anchor.'" title="Permalink to this section">§</a>';
		}
		return $headings;
	}

	/**
	 * Generates a table of contents from the headings within an HTML DOM
	 * @param $toc_elements The headings extracted from an HTML DOM.
	 */
	protected function generateTOC($toc_elements) {
		// Create two arrays, one to keep track of levels, the other to keep track of contents
		$curr_level = 0;
		$prev_level = 0;
		$next_level = 0;

		// Initialise the string we want
		$toc_string = '<div class="toc"><span><a href="#" id="toctoggle">Contents</a></span>';

		// Build the TOC by looping through the data
		for($te = 0, $tes = count($toc_elements); $te < $tes; $te++) {
			$curr_level = substr($toc_elements[$te]->tag, -1);
			if(($te + 1) != $tes) {
				$next_level = substr($toc_elements[$te + 1]->tag, -1);
			} else {
				$next_level = 0;
			}

			if($curr_level > $prev_level) {
				$toc_string .= "\n". Utils::padStr($curr_level).'<ol>';
			}

			if($curr_level <= $prev_level) {
				$toc_string .= "</li>";
			}

			$text_str = substr(strip_tags($toc_elements[$te]->plaintext), 0, -2);

			$toc_string .= "\n". Utils::padStr($curr_level + 1).'<li><a href="#'.$toc_elements[$te]->id.'" title="">'.$text_str."</a>";

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

		return $toc_string;
	}
}
