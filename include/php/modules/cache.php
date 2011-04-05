<?php
/*
 * cache.php
 * ---------------
 * Class used to generate a Cache file to save on successive regeneration of content.
 *
 */

class Cache {
	/**
	 * Generates a cached file of the data
	 * @param $data The data we wish to store in a cache.
	 * @param $file The complete name (including path) of the file to be used as a cache. Note that this file will be overwritten.
	 */
	public static function saveCacheFile($data, $file=null) {
		if($file == null) {
			$cfile = Application::getCacheDir() . self::makeHash($data);
		} else {
			$cfile = $file;
		}
		$cdata = print_r($data, true);
		return file_put_contents($cfile, $cdata);
	}

	/**
	 * Reads a cache file.
	 * @param $hash The hash of the file
	 * @param $dir The directory in which to look
	 * @return The content of the file, if found.
	 */
	public static function loadCacheFile($hash, $dir) {
		$file_name = $dir . $hash;
		if(file_exists($file_name) && is_readable($file_name)) {
			return file_get_contents($file_name);
		} else {
			return false;
		}
	}

	/**
	 * Generates a hash of some data.
	 * @param $data The data to generate a hash of.
	 */
	public static function makeHash($data) {
		$pdhash = hash_init('md5');
		hash_update($pdhash, print_r($data, true));
		return hash_final($pdhash);
	}

	/**
	 * Generates a hash of a file. Do not use this function on a binary file as the results may be unpredictable.
	 * @param $file_name The full path to the file that we wish to generate a hash of.
	 * @param $do_checks Whether we should do some basic sanity checks.
	 * @returns The hash of the file.
	 */
	public static function makeHashOfFile($file_name, $do_checks=true) {
		$file_data = '';
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
		return self::makeHash($file_data);
	}

}

