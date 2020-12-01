<?php

class PwnedPasswords {

	const HASH_LENGTH = 40;
	const PREFIX_LENGTH = 4;

	function __construct() {
		# TODO: Read these values from config
		$this->url = "https://pwnedpasswords.wikimedia.es/4";
		$this->cache = __DIR__ . "/cache";
		$this->binary = true;
	}

	/**
	 * @param string $password
	 * @return bool
	 */
	public function checkPassword( $password ) {
		return $this->checkPasswordHash( sha1( $password ) );
	}

	/**
	 * Checks if a sha1 hash corresponds to a password
	 * that appeared on a data breach.
	 * This function is useful mainly for testing, generally you
	 * should use checkPassword() instead.
	 * @param string $sha1
	 * @return bool
	 */
	public function checkPasswordHash( $sha1 ) {
		$hash = strtoupper( $sha1 );
		$prefix = substr( $hash, 0, self::PREFIX_LENGTH );
		$tail = substr( $hash, self::PREFIX_LENGTH );

		$data = $this->fetchFile( $prefix );

		return $this->findHashTail( $prefix, $data, $tail ) !== false;
	}

	/**
	 * Fetches the list of hashes for this hash prefix
	 * @param string $prefix
	 * @return array
	 */
	protected function fetchFile( $prefix ) {
		# The files are actually stored in two levels
		$prefix = substr( $prefix, 0, 2 ) . "/$prefix";
		if ( $this->cache ) {
			$cacheFile = $this->cache . "/$prefix";
			if ( file_exists( $cacheFile ) ) {
				$data = file_get_contents( $cacheFile );
				if ( $data ) {
					return $data;
				}
			}
		}

		$data = file_get_contents( $this->url . "/$prefix" );
		if ( $data && $this->cache ) {
			// Create the cache folder if needed
			if ( !is_dir( dirname( $cacheFile ) ) ) {
				mkdir( dirname( $cacheFile ), 0766, true );
			}
			file_put_contents( $cacheFile, $data );
		}

		return $data;
	}

	/**
	 * Performs a binary search for the given hash tail in
	 * the file whose contents are provided in $data
	 * @param string $filename
	 * @param array $data
	 * @param string $hashTail
	 * @return bool|string
	 */
	protected function findHashTail( $filename, $data, $hashTail ) {
		$tailLength = self::HASH_LENGTH - self::PREFIX_LENGTH;

		if ( $this->binary ) {
			return self::binarySearch( $filename, $data, hex2bin( $hashTail ), $tailLength / 2 ) !== false;
		}

		if ( $data[$tailLength] == ':' ) {
			// The hashes have appearance counts,
			// so we will have to perform a linear search
			$n = strpos( $data, "${hashTail}:" );

			return $n !== false;
		}

		if ( $data[$tailLength] == '\n' ) {
			// File has been preprocessed by leaving
			// just the hash tails with UNIX end-lines
			//
			// $ cut -c 1-35 <partial file>

			return self::binarySearch( $filename, $data, "$hashTail\n", $tailLength + 1 ) !== false;
		}

		return false;
	}

	/**
	 * @param string $filename
	 * @param string $data
	 * @param string $needle
	 * @param int $blockSize
	 * @return string|bool
	 */
	protected static function binarySearch( $filename, $data, $needle, $blockSize ) {
		$size = strlen( $data );
		if ( $size % $blockSize ) {
			throw new Exception( "File $filename is $size bytes, not a multiple of blockSize" );
		}

		$count = $size / $blockSize;
		$start = 0;
		$end = $count;

		// Invariant: if present, $needle is in [$start, $end)
		while ( $start < $end ) {
			$pos = $start + ( ( $end - $start ) >> 1 ); // Integer division by 2
			$n = substr_compare( $data, $needle, $pos * $blockSize, strlen( $needle ) );

			if ( $n === 0 ) {
				// Found!
				return substr( $data, $pos * $blockSize + strlen( $needle ), $blockSize - strlen( $needle ) );
			}

			if ( $n < 0 ) {
				$start = $pos + 1;
			} elseif ( $n > 0 ) {
				$end = $pos;
			} else {
				throw new Exception( "Getting out of an impossible infinite loop" );
			}
		}
		return false;
	}
}
