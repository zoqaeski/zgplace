<?php
/*
 * Sitemap.php
 * -----------
 * Class for generating a site map
 * TODO
 */

class Sitemap {

	protected $site_tree = array();
	protected $site_tree_texy;

	public function __construct($base_dir = null) {
		if($base_dir === null) {
			$base_dir = Application::getPublicContentDir();
		}

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir), RecursiveIteratorIterator::SELF_FIRST);
		foreach($files as $file) {
			$name = $file->getFilename();
			// Eliminate dot files
			if($name !== '..' && $name !== '.') {
				$path = $file->getPathname();
				$ext = '.' . $file->getExtension();
				// Eliminate non-linkable files. Basically only HTML and Texy files will be returned.
				$has_ext = array_search($ext, Application::getSourceFormats());
				if($has_ext !== false) {
					$path = substr($path, strlen($base_dir));
					$path = str_ireplace($ext, null, $path);
					$path = str_replace('/index', null, $path);
					// Eliminate indices, menus, and site maps
					$is_menu = strrpos($path, '/menu');
					$is_sitemap = strrpos($path, '/sitemap');
					if($is_menu === false && $is_sitemap === false && strlen($path) > 0) {
						// Add the rest.
						$this->site_tree[] = $path;
					}
				}
			}
		}

		$this->site_tree = array_unique($this->site_tree);
	}

	public function __toString() {
		foreach($this->site_tree as $leaf) {
			$str = $str . "\n" . $leaf;
		}
		//foreach($this->site_tree as $leaf) {
		//	$str = $str . "\n" . $leaf;
		//}
		return $str;
	}

	public function makeTexyStr() {
		foreach($this->site_tree as $node) {
			//TODO
			$link_text = substr($node, strrpos($node, '/') + 1);
			$link_text = ucwords(preg_replace(Application::getPathSearch(), Application::getPathReplace(), $link_text));
			$texy = '- "'. $link_text . '":[' . $node . ']';

			$this->site_tree_texy = $this->site_tree_texy . "\n" . $texy;
		}
	}

	public function getSiteTreeTexy() {
		return $this->site_tree_texy;
	}

}
