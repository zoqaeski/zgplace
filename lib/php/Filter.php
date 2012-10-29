<?php
/**
 * Filter abstract class
 */
abstract class Filter {

	/** @var The content of the file to be filtered. */
	protected $file_content;

	/** @var An array containing all the information about the page. */
	protected $pageData;

	/** @var An array containing all the information about the page's menu. */
	protected $menuMeta;

	/**
	 * Gets the metadata out of the special comment at the top of the page.
	 */
	protected final function parseMetaComment() {
		$lines = explode("\n", $this->file_content);

		if($lines[0] === "<!--" || $lines[1] === "<!--") {
			array_shift($lines);
			foreach($lines as $line) {
				if(strlen($line) > 0) {
					$mcline = array_shift($lines);
					if($mcline === "-->") {
						break;
					} else {
						$mcfound = preg_match(Application::getMetacommentPreg(), $mcline, $matches);
						if($mcfound != 0) {
							$matches[1] = strtolower($matches[1]);
							// PHP is rather fussy about booleans, so I needed a conversion function. 
							// 'true' and 'yes' → true
							// 'false' and 'no' → false
							$value = Utils::str_to_bool($matches[2]);
							$this->pageData[$matches[1]] = $value;
						}
					}
				}
			}
			$this->file_content = implode("\n", $lines);
		}
		return $this->pageData;
	}

	/** 
	 * Runs the filter.
	 */
	abstract protected function run();

	/**
	 * Generate a table of contents listing.
	 */
	abstract protected function generateTOC($toc_elements);

	/**
	 * Returns the pageData object
	 */
	public function getData() {
		return $this->pageData;
	}

	/**
	 * Constructs the next/previous links for ordered page sets.
	 * @param $prev the previous page link
	 * @param $next the next page link
	 */
	protected function buildTopicNavLinks($prev, $next) {
		if($prev != null) {
			$tn_prev = '<span class="tprev">« '. $prev .'</span>';
		} else {
			$tn_prev = null;
		}

		if($next != null) {
			$tn_next = '<span class="tnext">'. $next .' »</span>';
		} else {
			$tn_next = null;
		}

		$tn_top = '<span class="ttop"><a href="#content">Return to Top</a></span>';

		$tn_links['top'] = '<div class="tnavt">'. $tn_prev . $tn_next . '</div>';
		$tn_links['bottom'] = '<div class="tnavb">'. $tn_prev . $tn_top . $tn_next . '</div>';
		return $tn_links;
	}
}
?>
