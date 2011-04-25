<?php 
$page = $_GET['page'];
$section = $_GET['section'];

$title = 'Currently untitled';

// HTML tags that we don't want to include
$badtags = "/(<(\/?)(\?|html|head|title|meta|base|link|body)(.*?)>)|(<!DOCTYPE(.*?))/";

if($page == '') {
	$page = "index";
}

// First, we check to see if the file exists and we can read it
if(file_exists("data/$page.html")) {
	$uri = "data/$page.html";
} elseif(file_exists("data/$page/index.html")) {
	$uri = "data/$page/index.html";
} else {
	// Otherwise we load my customised 404 page.
	$uri = "include/errors/404.html";
}

$lines = explode("\n", file_get_contents($uri));

function parseMeta($lines){
	$cStart = -1;
	$cEnd = -1;
	
	$meta = array();

	for($ln = 0; $ln < count($lines); $ln++) {
		if(preg_match("/^<!--/", $lines[$ln]) && $cStart < 0){
			$cStart = $ln; // should be 0 or 1 really.
		} else if(preg_match("/^-->/", $lines[$ln]) && $cEnd < 0) {
			$cEnd = $ln;
		}
	}
	
	if($cEnd > 0 && $cStart >= 0 && $cStart < 2){
		$comments = array_slice($lines, $cStart, $cEnd - $cStart);
		if(count($comments) > 0){
			for($i = 0, $matches; $i < count($comments); $i++){
				if(strlen($comments[$i]) > 0){
					preg_match("/^(\S+):\s(\S+)$/im", $comments[$i], &$matches);
					if($matches[1] != ""){
						$meta[strtolower($matches[1])] = $matches[2];
					}
				}
			}
		}
	}
	$meta['tocfile'] = $meta['topic'] .':'. $meta['subtopic'] .':'. $meta['section'];
	return $meta;
}


$metadata = parseMeta($lines);

$subtypes = array('subtopic', 'section');
$sublevels = array('topLevel', 'secondLevel');

function arrayToList($arr, $meta, $subTypeIdx=-1){
	$subTypeIdx++;
	$out = "<ul>";
	foreach($arr as $key => $value){
		$out .= $sublevels[0];
		$out .= '<li class="'.$sublevels[$subTypeIdx+1].'"><a href="index.php'.(strtolower($key) == "home" ? "" : "?page=".$base.strtolower($key)).'">'.$key.'</a>';
		
			if(is_array($value) && !empty($value)){
				$out .= arrayToList($value, $meta, $subTypeIdx, $base.strtolower($key).'/');
			}
		$out .= '</li>';
	}
	$out .= '</ul>';
	return $out;
}

function renderSidebar($sidebar, $metadata){
	echo arrayToList($sidebar, $metadata);
}

$position = 0;
$found = false;

for(; $position < count($lines) && !$found; $position++) {
	if(preg_match("/<title>/", $lines[$position])) {
		$title = trim(strip_tags($lines[$position]));
		$found = true;
	}
}

if(!$found) {
	$position = 0;
}

/*print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";*/
?>

