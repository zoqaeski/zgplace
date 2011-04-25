<?php
/*
 * utils.php
 * ---------------
 * This is the core application used to build my site. 
 *
 */

class Utils {

	/**
	 * Static class.
	 */
	final public function __construct()
	{
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}

	/**
	 * Converts a string to a boolean if and only if the string contains a keyword such as 'yes', 'no', 'true', 'false'. Returns the string if it can't be converted.
	 * @param $str The string to test and convert.
	 * @return mixed String if it can't be converted.
	 */
	public static function str_to_bool(&$str) {
		$false_words = array(
			'false',
			'no');

		$true_words = array(
			'true',
			'yes');

		// Test for true
		foreach($true_words as $tw) {
			$pos = strpos($str, $tw);
			if($pos !== false) {
				$str = true;
				return true;
			}
		}

		// Test for false
		foreach($false_words as $fw) {
			$pos = strpos($str, $fw);
			if($pos !== false) {
				$str = false;
				return false;
			}
		}

		return $str;
	}

	/**
	 * Modified urlencode that replaces some characters.
	 * @param $str the string we want to encode.
	 */
	public static function urlencode($str) {
		$str = urlencode($str);
		$str = strtr($str, array('%' => '.', '+' => '_'));
		// Should we leave colons alone?
		//$a = str_replace('.3A', ':', $a);
		return $str;
	}

	/**
	 * Pads a string by prepending tabs.
	 * @param $level the number of tabs to insert.
	 */
	public static function padStr($level) {
		$str = "";
		for(;$level > 0; $level--) {
			$str .= "\t";
		}
		return $str;
	}
}
