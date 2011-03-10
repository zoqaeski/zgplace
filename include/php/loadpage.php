<?php 
// Useful thingies we need.
$error = false;
require("include/php/simple_html_dom.php");

function parseMenu($file) {
	$menuDOM = new simple_html_dom($file);
	$menudata = modifyLinks($menuDOM);
	$menudata = $menuDOM->find('ul.levelTwo');
	return $menudata[0]->outertext;
}

function parsePage($page_dom) {
	$pageData = $page_dom->find('body', 0)->innertext;
	return $pageData;
}

/*
 * Finds <script> tags in some pages and extracts them. This is a potential
 * security hole (if we allow users to create pages and scripts, so I'll need to
 * patch it and devise a means to permit only scripts I have authorised.
 */
function findScriptElements(&$page_dom) {
	$scriptElements = $page_dom->find('script');
	$scriptStr = "";
	foreach($scriptElements as $scriptElement) {
		$script = $scriptElement->outertext;
		$scriptStr = $scriptStr."\n". $script;	
	}

	return $scriptStr;
}

/*
 * Gets the metadata out of the special comment at the top of the page.
 */
function parseMetaComment(&$page_dom) {
	$comments = $page_dom->find('comment');
	$metacomment = str_replace(array("<!--", "-->"), "", $comments[0]->innertext);

	$mclines = explode("\n", $metacomment);
	foreach($mclines as $mcline) {
		if(strlen($mcline) > 0){
			preg_match("/^(\S+):\s(\S+)$/im", $mcline, &$matches);
			if($matches[1] != ""){
				$meta[strtolower($matches[1])] = $matches[2];
			}
		}
	}

	return $meta;
}

function modifyLinks(&$page_dom) {
	$links = $page_dom->find('a');

	for($l = 0, $ls = count($links); $l < $ls; $l++) {
		$url = $links[$l]->href;
		if(substr($url, 0, 15) == "index.php?page=") {
			$url = '/zgplace/' . substr($url, 15);
			$links[$l]->href = $url;
		}
	}

}

function modifySrcs(&$page_dom) {
	$srcs = $page_dom->find('img, script');

	for($s = 0, $ss = count($srcs); $s < $ss; $s++) {
		if($srcs[$s]->src) {
			$url = $srcs[$s]->src;
			$srcs[$s]->src = '/zgplace/' . $url;
		}
		//echo $url . '<br />';
		//$links[$l]->href = '';
	}

}

/*
 * Finds all headings in a document and adds anchors to them. We could possibly make them permalinks
 */
function addAnchors(&$page_dom) {
	// Ignore <h1> as it is the title of the page
	$headings = $page_dom->find('h2, h3, h4, h5, h6');
	$htexts = array();
	
	for($h = 0, $hs = count($headings); $h < $hs; $h++) {
		$anchor = anchorencode($headings[$h]->plaintext);
		$htexts[$h] = $headings[$h]->tag . ':' . $headings[$h]->plaintext;
		$headings[$h]->id = $anchor;
		$headings[$h]->innertext = $headings[$h]->plaintext.' <a class="permalink" href="#'.$anchor.'" title="Permalink to this section">§</a>';
	}
	return $headings;
}

/*
 * urlencode anchors
 * borrowed from WikiMedia
 */
function anchorencode($text) {
	$a = urlencode($text);
	$a = strtr($a, array('%' => '.', '+' => '_'));
	// Should we leave colons alone?
	//$a = str_replace('.3A', ':', $a);
	return $a;
}

function generateToc($toc_elements) {
	// Create two arrays, one to keep track of levels, the other to keep track of contents
	$curr_level = 0;
	$last_level = 0;
	$next_level = 0;

	// Initialise the string we want
	$toc_string = '<div class="toc"><span><a href="#" id="toctoggle">Contents</a></span>';
	
	// Build the TOC by looping through the data
	for($te = 0, $tes = count($toc_elements); $te < $tes; $te++) {
		$curr_level = substr($toc_elements[$te]->tag, -1);
		$next_level = substr($toc_elements[$te + 1]->tag, -1);


		if($curr_level > $prev_level) {
			$toc_string .= "\n".padStr($curr_level).'<ol>';
		}

		if($curr_level <= $prev_level) {
			$toc_string .= "</li>";
		}

		$text_str = substr(strip_tags($toc_elements[$te]->plaintext), 0, -3);

		$toc_string .= "\n".padStr($curr_level + 1).'<li><a href="#'.$toc_elements[$te]->id.'" title="">'.$text_str."</a>";

		$cn_level_diff = $curr_level - $next_level;

		if($next_level == 0) {
			$cn_level_diff--;
		}

		while($cn_level_diff > 0) {
			$toc_string .= "</li>";
			$toc_string .= "\n".padStr($curr_level)."</ol>";
			$cn_level_diff--;
		}

		$prev_level = $curr_level;

	}

	$toc_string .= "</div>\n";

	return $toc_string;
}

function padStr($level){
	$str = "";
	for(;$level > 0; $level--) {
		$str .= "\t";
	}
	return $str;
}

// Document Object Model parser(s)
$pageDOM = new simple_html_dom();

$page = $_GET['page'];

$title = 'Currently untitled';

if($page == '') {
	$page = "index";
}

// First, we check to see if the file exists and we can read it
if(file_exists("data/$page.html")) {
	$path = "data/$page.html";
} elseif(file_exists("data/$page/index.html")) {
	$path = "data/$page/index.html";
} else {
	// Otherwise we load my customised 404 page.
	$path = "include/errors/404.html";
	$error = true;
	header("HTTP/1.0 404 Not Found");
}

// Load the file into the parser
$pageDOM->load_file($path);

// Find the title
$title = $pageDOM->find('title', 0)->plaintext;


$metadata = parseMetaComment($pageDOM);

//$lines = explode("\n", file_get_contents($path));

// Figure out an appropriate section menu
// Contents, like indices, are stored in the same directory now.
$menufile = dirname($path)."/menu.html";

if(!$error && $metadata['type'] != 'index' && $menudata['type'] != 'menu') {
	$pageHeadings = addAnchors($pageDOM);
	if(count($pageHeadings) > 1) {
		$h_ones = $pageDOM->find('h1');
		$toc_place = $h_ones[count($h_ones) - 1];
		$toc_place->outertext = $toc_place->outertext . generateToc($pageHeadings);
	}
}

// Modify links and the like
//modifyLinks($pageDOM);
modifySrcs($pageDOM);

?>
