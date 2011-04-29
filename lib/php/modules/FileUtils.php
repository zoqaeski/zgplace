<?php
/*
 * FileUtils.php
 * ---------------
 * Static class used to manage non-page files, like cache logs and the like.
 *
 */

class FileUtils {

	/**
	 * Static class.
	 */
	final public function __construct()
	{
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}

	/**
	 * Loads a file and returns the data contained within.
	 * @param $file_name The full name of the file, including its path.
	 * @param $is_serial_data Whether or not the file contains PHP variables encapsulated by serialize()
	 * @param $do_sanity_checks Whether or not we should do some basic sanity checks to ensure the file is readable.
	 * @return mixed This function returns one of the following: a string containing the file contents, a mixed variable representing whatever was encapsulated in the file, or the boolean FALSE if the file could not be opened.
	 */
	public static function loadFile($file_name, $is_serial_data = false, $do_sanity_checks = true) {
		if($do_checks) {
			// No need to have separate handlers here.
			if(file_exists($file_name) && is_readable($file_name)) {
				$file_data = file_get_contents($file_name);
			} else {
				return false;
			}
		} else {
			$file_data = file_get_contents($file_name);
		}

		if($is_serial_data) {
			return unserialize($file_data);
		} else {
			return $file_data;
		}
	}

	/**
	 * Loads a file and writes data to it.
	 * @param $file_name The full name of the file, including its path.
	 * @param $data The data to write to the file.
	 * @param $do_sanity_checks Whether or not we should do some basic sanity checks to ensure the file is readable.
	 * @return mixed This function returns one of the following: a string containing the file contents, a mixed variable representing whatever was encapsulated in the file, or the boolean FALSE if the file could not be opened.
	 */
	public static function saveFile($file_name, $data, $do_sanity_checks = true) {
//		if($do_checks) {
//			// No need to have separate handlers here.
//			if(file_exists($file_name) && is_readable($file_name)) {
//				$file_data = file_get_contents($file_name);
//			} else {
//				return false;
//			}
//		} else {
//			$file_data = file_get_contents($file_name);
//		}
//
//		if($is_serial_data) {
//			return unserialize($file_data);
//		} else {
//			return $file_data;
//		}
		return false;
	}
}

