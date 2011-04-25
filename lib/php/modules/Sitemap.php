<?php
/*
 * Sitemap.php
 * -----------
 * Class for generating a site map
 * TODO
 */

class Sitemap {

	protected $site_tree = array();

	public function __construct($base_dir = null) {
		if($base_dir === null) {
			$base_dir = Application::getPublicContentDir();
		}

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir), RecursiveIteratorIterator::SELF_FIRST);
		foreach($files as $file) {
			$path = $file->getPathname();
			$path = substr($path, strlen($base_dir));
			$this->site_tree[] = $path;
		}
	}

	public function __toString() {
		foreach($this->site_tree as $leaf) {
			$str = $str . "\n" . $leaf;
		}
		return $str;
	}

}
