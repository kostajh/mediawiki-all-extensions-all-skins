<?php

/**
 * Cache for Google Places calls
 *
 * @author Ike Hecht
 */
class GooglePlacesCache {

	/**
	 * Get this call from db cache
	 *
	 * @param string $APIKey
	 * @param string $placeID
	 * @return string|bool
	 */
	public static function getCache( $APIKey, $placeID ) {
		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'googleplaces', $APIKey, $placeID );
		$cached = $cache->get( $key );
		wfDebugLog( "GooglePlaces",
			__METHOD__ . ": got " . var_export( $cached, true ) .
			" from cache." );
		return $cached;
	}

	/**
	 * Store this call in cache
	 *
	 * @param string $APIKey
	 * @param string $placeID
	 * @param string $response
	 * @param int $cache_expire
	 * @return bool
	 */
	public static function setCache( $APIKey, $placeID, $response, $cache_expire = 0 ) {
		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'googleplaces', $APIKey, $placeID );
		wfDebugLog( "GooglePlaces",
			__METHOD__ . ": caching " . var_export( $response, true ) .
			" from Google." );
		return $cache->set( $key, $response, $cache_expire );
	}
}
