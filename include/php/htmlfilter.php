<?php

require_once("simple_html_dom.php");

class HTMLFilter {

	var $pageDOM;

	var $metadata;

	function HTMLFilter(&$pageDOM) {
		$this->pageDOM =& $pageDOM;
	}

	function getTitle() {
		return $this->pageDOM->find('title', 0)->plaintext;
	}

	/*
	 * Parses an HTML page DOM
	 * @param $srcdir The directory to files that are sourced, such as images
	 */
	function applyFilter($srcdir) {

		// Generate a TOC based on heading hierarchy
		if($this->metadata['type'] != 'index' && $this->metadata['type'] != 'menu') {
			$pageHeadings = $this->addAnchors();
			if(count($pageHeadings) > 1 && $this->metadata['toc'] != 'false') {
				$h_ones = $this->pageDOM->find('h1');
				$toc_place = $h_ones[count($h_ones) - 1];
				$toc_place->outertext = $toc_place->outertext . $this->generateTOC($pageHeadings);
			}
		}

		// Modify links and the like
		$this->modifyLinks($pageDOM);
		$this->modifyImgs($srcdir);

		return $pageData;
	}

	/*
	 * Finds <script> tags in some pages and extracts them. This is a potential
	 * security hole (if we allow users to create pages and scripts, so I'll need to
	 * patch it and devise a means to permit only scripts I have authorised.
	 */
	function findScriptElements() {
		$scriptElements = $this->pageDOM->find('script');
		$scriptStr = "";
		foreach($scriptElements as $scriptElement) {
			$script = $scriptElement->outertext;
			$scriptStr = $scriptStr . $script;	
			$scriptElement->outertext = '';
		}

		return $scriptStr;
	}

	/*
	 * Gets the metadata out of the special comment at the top of the page.
	 */
	function parseMetaComment() {
		$comment = $this->pageDOM->find('comment', 0);
		$metacomment = str_replace(array("<!--", "-->"), "", $comment->innertext);

		$mclines = explode("\n", $metacomment);
		foreach($mclines as $mcline) {
			if(strlen($mcline) > 0){
				preg_match("/^(\S+):\s(\S+)$/im", $mcline, &$matches);
				if($matches[1] != ""){
					$this->metadata[strtolower($matches[1])] = $matches[2];
				}
			}
		}

		return $this->metadata;
	}

	/*
	 * Modifies links that were part of the old scheme to the absolute paths. This is deprecated.
	 */
	function modifyLinks() {
		$links = $this->pageDOM->find('a');

		for($l = 0, $ls = count($links); $l < $ls; $l++) {
			$url = $links[$l]->href;
			if(substr($url, 0, 8) == "/zgplace") {
				echo $url . ' → ';
				$url = '' . substr($url, 8);
				echo $url . '<br/>';
				$links[$l]->href = $url;
			}
		}

	}

	/*
	 * Modifies source references that were part of the old scheme to the absolute paths.
	 */
	function modifyImgs($srcdir) {
		$imgs = $this->pageDOM->find('img');

		for($i = 0, $is = count($imgs); $i < $is; $i++) {
			if($imgs[$i]->src) {
				$url = $imgs[$i]->src;
				// $imgs[$i]->src = '/zgplace/public/img/' . $srcdir . '/' . $url;
				$imgs[$i]->src = '/public/img/' . $srcdir . '/' . $url;
			}
			//echo $url . '<br />';
			//$links[$l]->href = '';
		}

	}

	/*
	 * Finds all headings in a document and adds anchors to them. We could possibly make them permalinks
	 */
	function addAnchors() {
		// Ignore <h1> as it is the title of the page
		$headings = $this->pageDOM->find('h2, h3, h4, h5, h6');
		$htexts = array();

		for($h = 0, $hs = count($headings); $h < $hs; $h++) {
			$anchor = $this->anchorencode($headings[$h]->plaintext);
			$htexts[$h] = $headings[$h]->tag . ':' . $headings[$h]->plaintext;
			$headings[$h]->id = $anchor;
			$headings[$h]->innertext = $headings[$h]->plaintext.'<a class="permalink" href="#'.$anchor.'" title="Permalink to this section">§</a>';
		}
		return $headings;
	}

	/*
	 * urlencode anchors
	 * borrowed from WikiMedia
	 */
	private function anchorencode($text) {
		$a = urlencode($text);
		$a = strtr($a, array('%' => '.', '+' => '_'));
		// Should we leave colons alone?
		//$a = str_replace('.3A', ':', $a);
		return $a;
	}

	/*
	 * Generates a table of contents from the headings within an HTML DOM
	 */
	function generateTOC($toc_elements) {
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

	private function padStr($level){
		$str = "";
		for(;$level > 0; $level--) {
			$str .= "\t";
		}
		return $str;
	}
}
?>
