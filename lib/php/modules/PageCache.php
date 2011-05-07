<?php
/*
 * cache.php
 * ---------------
 * Class used to generate a Cache file to save on successive regeneration of content.
 *
 */

class PageCache {

	private static $hash_ctx;

	private static $hash_method = 'md5';

	public function __construct($hash_method='md5') {
		$valid_method = array_search($hash_method, hash_algos(), true);
		if($valid_method === false) {
			throw new InvalidArgumentException("Not a valid hash method");
		} else {
			self::$hash_ctx = hash_init($hash_method);
			self::$hash_method = $hash_method;
		}
	}

	public function updateHash($data) {
		return hash_update(self::$hash_ctx, serialize($data));
	}

	public function finaliseHash() {
		return hash_final(self::$hash_ctx);	
	}

	/**
	 * Updates the internal hashing context. Do not use this function on a binary file as the results may be unpredictable.
	 * @param $file_name The full path to the file that we wish to generate a hash of.
	 * @param $do_checks Whether we should do some basic sanity checks.
	 */
	public function updateHashOfFile($file_name, $do_checks = true) {
		$file_data = FileUtils::loadFile($file_name, false, $do_checks);
		if($file_data === false) {
			return false;
		}

		return $this->updateHash($file_data);
	}

	/**
	 * Generates a cached file of the data
	 * @param $data The data we wish to store in a cache.
	 * @param $file The complete name (including path) of the file to be used as a cache. Note that this file will be overwritten.
	 */
	public static function saveCacheFile($data, $file = null) {
		if($file === null) {
			$cfile = Application::getCacheDir() . self::makeHash($data);
		} else {
			$cfile = $file;
		}
		$cdata = serialize($data);
		//$cdata = print_r($data, true);
		return file_put_contents($cfile, $cdata);
	}

	/**
	 * Reads a cache file.
	 * @param $hash The hash of the file
	 * @param $cache_dir The directory in which to look
	 * @return The content of the file, if found.
	 */
	public static function loadCacheFile($hash, $cache_dir = null) {
		if($cache_dir === null) {
			$file_name = Application::getCacheDir() . $hash;
		} else {
			$file_name = $cache_dir . $hash;
		}

		return FileUtils::loadFile($file_name, true, true);
	}

	/**
	 * Logs the caches that have been made so we can perform garbage collection.
	 * @param $cache_entry The hash sum for the entry in cache.
	 * @param $original The path to the original file
	 * @param $cache_dir The path to the cache directory.
	 */
	public static function logCacheEntry($cache_entry, $original, $cache_dir=null, $human_readable=false) {
		$cache_dir = ($cache_dir == null) ? Application::getCacheDir() : $cache_dir;
		$log_file = $cache_dir . 'cache_log';

		$cache_log = self::loadCacheLog($log_file);
		if($cache_log === false) {
			$cache_log = array();
		}

		if(is_array($cache_log)) {
			$cache_log[$cache_entry] = $original;
		} else {
			throw new RuntimeException("Log file is not a valid format.");
		}

		return self::saveCacheLog($cache_log, $log_file, $human_readable);
	}

	/**
	 * Loads a cache log file.
	 * @param $log_file The path to the log file.
	 * @return array
	 */
	public static function loadCacheLog($log_file) {
		if(file_exists($log_file)) {
			if(is_readable($log_file)) {
				return unserialize(file_get_contents($log_file));
			} else {
				throw new RuntimeException("Log file cannot be opened for reading.");
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Saves a cache log to file.
	 * @param $cache_log The cache log array.
	 * @param $log_file The file to save.
	 */
	public static function saveCacheLog($cache_log, $log_file, $human_readable=false) {
		if(file_exists($log_file) && ! is_writeable($log_file)) {
			throw new RuntimeException("Log file cannot be opened for writing.");
			return false;
		} else {
			$log_file_human = $log_file . '.txt';
			file_put_contents($log_file_human, print_r($cache_log, true));
			return file_put_contents($log_file, serialize($cache_log));
		}
	}

	/**
	 * Removes old cache entries.
	 * @param $original The path to the original file.
	 * @return void
	 */
	public static function cleanCache($original, $cache_dir=null) {
		$cache_dir = ($cache_dir == null) ? Application::getCacheDir() : $cache_dir;
		$log_file = $cache_dir . 'cache_log';

		$cache_log = self::loadCacheLog($log_file);
		if($cache_log === false) {
			return false;
		} elseif(! is_array($cache_log)) {
			throw new RuntimeException("Log file is not a valid format.");
		}

		$old_entries = array_keys($cache_log, $original);
		if($old_entries === false || sizeof($old_entries) == 0) {
			return;
		} else {
			foreach($old_entries as $entry) {
				$old_entry = $cache_dir . $entry;
				$deleted = unlink($old_entry);
				if($deleted == true) {
					unset($cache_log[$entry]);
				}
			}
		}

		$cache_log_saved = self::saveCacheLog($cache_log, $log_file);
		if($cache_log_saved === false) {
			throw new RuntimeException("Unable to save cache log. Something went wrong.");
		}

		return;
	}

	/**
	 * Generates a hash of some data.
	 * @param $data The data to generate a hash of.
	 */
	public static function makeHash($data) {
		$pdhash = hash_init(self::$hash_method);
		hash_update($pdhash, serialize($data));
		//hash_update($pdhash, print_r($data, true));
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

