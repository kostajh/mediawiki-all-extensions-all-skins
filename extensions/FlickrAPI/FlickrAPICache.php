<?php

/**
 * Custom db cache for Flickr API calls
 *
 * @author Ike Hecht
 */
class FlickrAPICache {

	/**
	 * Get this call from db cache
	 *
	 * @param string $reqhash The cache key.
	 * @return string|bool
	 */
	public static function getCache( $reqhash ) {
		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'flickrapi', $reqhash );
		$cached = $cache->get( $key );
		wfDebugLog( "FlickrAPI", __METHOD__ . ": got " . var_export( $cached, true ) .
			" from cache." );
		return $cached;
	}

	/**
	 * Store this call in cache.
	 *
	 * @param string $reqhash The cache key.
	 * @param string $response The response to cache.
	 * @param int $cache_expire Either an interval in seconds or a unix timestamp for expiry.
	 * @return bool
	 */
	public static function setCache( $reqhash, $response, $cache_expire ) {
		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'flickrapi', $reqhash );
		wfDebugLog( "FlickrAPI",
			__METHOD__ . ": caching " . var_export( $response, true ) .
			" from Flickr." );
		return $cache->set( $key, $response, $cache_expire );
	}
}
