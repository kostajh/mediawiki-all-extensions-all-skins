<?php
namespace PhpTagsObjects;

use DateTime;
use PhpTags\GenericObject;
use PhpTags\Hooks;
use PhpTags\Renderer;

/**
 * The WikiWCache class manages the cache of page rendering.
 *
 * @author pastakhov
 */
class WikiWCache extends GenericObject {

	/**
	 * Set a flag in this page indicating that the content is dynamic and shouldn't be cached.
	 */
	public static function s_disableCache() {
		Renderer::disableParserCache();
	}

	/**
	 * Gets the number of seconds after which cache of this page should expire.
	 * @return int
	 */
	public static function q_cacheExpiry() {
		return Renderer::getParser()->getOutput()->getCacheExpiry();
	}

	/**
	 * Sets the number of seconds after which cache of this page should expire.
	 * @param int $value
	 */
	public static function d_cacheExpiry( $value ) {
		Renderer::getParser()->getOutput()->updateCacheExpiry( $value );
	}

	/**
	 * Gets time when this page was generated.
	 * @return string
	 */
	public static function c_CACHE_TIME_STRING() {
		return Renderer::getParser()->getOutput()->getCacheTime();
	}

	/**
	 * Gets time when this page was generated.
	 * @return GenericObject
	 */
	public static function c_CACHE_TIME() {
		return Hooks::getObjectWithValue(
				'DateTime',
				new DateTime( self::c_CACHE_TIME_STRING() )
			);
	}

}
