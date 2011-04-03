<?php

class HTMLFilter {

	private $pageDOM;
	private $pageMeta;
	private $menuMeta;

	/**
	 * Constructs and then runs the HTMLFilter
	 * @param pageDOM The DOM of the page we wish to filter
	 * @param pageMeta The metadata of the page we wish to filter. All generated content gets written to here.
	 * @param menuMeta The metadata of the menu for the page. We only read some 
	 * fields in here, so it's not passed as a reference. It defaults to null as we read meta-comments from a menu file, and reading the menu meta for the menu pulled in by the menu ... yeah, loopiness.
	 */
	function __construct(&$pageDOM, &$pageMeta, $menuMeta=null) {
		$this->pageDOM =& $pageDOM;
		$this->pageMeta =& $pageMeta;

		// Parse meta comments in top of file
		$this->parseMetaComment();

		if($menuMeta != null) {
			$this->menuMeta = $menuMeta;
			$this->run();
		}
	}

	/**
	 * Applies the filter.
	 */
	private function run() {
		// Get title
		$this->pageMeta['title'] = $this->getTitle();

		// Extract scripties
		$this->pageMeta['scripts'] = $this->findScriptElements();

		// Modify image src links
		$this->modifyImgs();

		// Generate a TOC based on heading hierarchy
		if($this->pageMeta['type'] != 'index' && $this->pageMeta['type'] != 'menu' && $this->pageMeta['maketoc'] == true) {
			$pageHeadings = $this->addAnchors();
			if(count($pageHeadings) > 1) {
				$h_ones = $this->pageDOM->find('h1');
				$toc_place = $h_ones[count($h_ones) - 1];
				$toc_place->outertext = $toc_place->outertext . $this->generateTOC($pageHeadings);
			}
		}

		// Ordered pages? No problems. Can has next/previous links
		if($this->menuMeta['ordered'] == true) {
			$this->buildTopicNavLinks($this->menuMeta['prev'], $this->menuMeta['next']);
		}

		//echo "HTMLFilter::run() just executed.";
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
		if($prev != null) {
			$tn_prev = '<span class="tprev">« '. $prev .'</span>';
		}

		if($next != null) {
			$tn_next = '<span class="tnext">'. $next .' »</span>';
		}

		$tn_top = '<span class="ttop"><a href="#content">Return to Top</a></span>';

		$tn_links_top = '<div class="tnavt">'. $tn_prev . $tn_next . '</div>';
		$tn_links_bottom = '<div class="tnavb">'. $tn_prev . $tn_top . $tn_next . '</div>';

		$tn_place = $this->pageDOM->find("#body", 0);
		$tn_place->innertext = $tn_place->innertext . $tn_links_bottom;
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
	 * Gets the metadata out of the special comment at the top of the page.
	 * TODO: Type conversion.
	 */
	private function parseMetaComment() {
		$comment = $this->pageDOM->find('comment', 0);
		$metacomment = str_replace(array("<!--", "-->"), "", $comment->innertext);

		$mclines = explode("\n", $metacomment);
		foreach($mclines as $mcline) {
			if(strlen($mcline) > 0) {
				preg_match("/^(\S+):\s(\S+)$/im", $mcline, &$matches);
				if($matches[1] != "") {
					//echo 'Before: ' . $matches[1] . ' = ' . $this->pageMeta[strtolower($matches[1])] . '<br />';
					$this->pageMeta[strtolower($matches[1])] = $matches[2];
					//echo 'After: ' . $matches[1] . ' = ' . $this->pageMeta[strtolower($matches[1])] . '<br />';
				}
			}
		}

		return $this->pageMeta;
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
				$imgs[$i]->src = '/public/img/' . $this->pageMeta['srcdir'] . '/' . $url;
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
			$anchor = $this->anchorEncode($headings[$h]->plaintext);
			$htexts[$h] = $headings[$h]->tag . ':' . $headings[$h]->plaintext;
			$headings[$h]->id = $anchor;
			$headings[$h]->innertext = $headings[$h]->plaintext.'<a class="permalink" href="#'.$anchor.'" title="Permalink to this section">§</a>';
		}
		return $headings;
	}

	/**
	 * urlencode anchors
	 * borrowed from WikiMedia
	 * @param $text the text to encode.
	 */
	private function anchorEncode($text) {
		$a = urlencode($text);
		$a = strtr($a, array('%' => '.', '+' => '_'));
		// Should we leave colons alone?
		//$a = str_replace('.3A', ':', $a);
		return $a;
	}

	/**
	 * Generates a table of contents from the headings within an HTML DOM
	 * @param $toc_elements The headings extracted from an HTML DOM.
	 */
	private function generateTOC($toc_elements) {
		// Create two arrays, one to keep track of levels, the other to keep track of contents
		$curr_level = 0;
		$prev_level = 0;
		$next_level = 0;

		// Initialise the string we want
		$toc_string = '<div class="toc"><span><a href="#" id="toctoggle">Contents</a></span>';

		// Build the TOC by looping through the data
		for($te = 0, $tes = count($toc_elements); $te < $tes; $te++) {
			$curr_level = substr($toc_elements[$te]->tag, -1);
			$next_level = substr($toc_elements[$te + 1]->tag, -1);

			if($curr_level > $prev_level) {
				$toc_string .= "\n".$this->padStr($curr_level).'<ol>';
			}

			if($curr_level <= $prev_level) {
				$toc_string .= "</li>";
			}

			$text_str = substr(strip_tags($toc_elements[$te]->plaintext), 0, -2);

			$toc_string .= "\n".$this->padStr($curr_level + 1).'<li><a href="#'.$toc_elements[$te]->id.'" title="">'.$text_str."</a>";

			$cn_level_diff = $curr_level - $next_level;

			if($next_level == 0) {
				$cn_level_diff--;
			}

			while($cn_level_diff > 0) {
				$toc_string .= "</li>";
				$toc_string .= "\n".$this->padStr($curr_level)."</ol>";
				$cn_level_diff--;
			}

			$prev_level = $curr_level;

		}

		$toc_string .= "</div>\n";

		return $toc_string;
	}

	/**
	 * Pads a string with tabs to the specified level.
	 * @param $level number of tabs to prepend.
	 */
	private function padStr($level){
		$str = "";
		for(;$level > 0; $level--) {
			$str .= "\t";
		}
		return $str;
	}
}
?>
