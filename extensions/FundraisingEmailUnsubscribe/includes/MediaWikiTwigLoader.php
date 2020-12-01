<?php
/**
 * Mediawiki and Twig templating. It's... beautiful?
 *
 * -- License --
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

/**
 * Provides methods for Twig templates to integrate with Mediawiki.
 *
 * Twig Templates can in fact be localized! If the original file name is a.b; then this class will
 * look for files named a.lang.b where lang is every language code in the current languages fallback
 * list. It will also of course use the original a.b file if no localized file exists.
 */
class MediaWikiTwigLoader implements Twig_LoaderInterface {

	/** @var string */
	private $mTwigTemplatePath;

	/** @var IContextSource */
	private $mContext;

	/**
	 * @param string $twigTemplatePath
	 * @param IContextSource $context
	 */
	public function __construct( $twigTemplatePath, IContextSource $context ) {
		$this->mTwigTemplatePath = $twigTemplatePath;
		$this->mContext = $context;
	}

	/**
	 * Simple function; it returns a filesystem Twig template. The trick here is that the twig
	 * template can be localized. A localization
	 *
	 * @param string $name
	 *
	 * @return bool|mixed|string
	 * @throws MWException if the template $name can not be found
	 */
	public function getSource( $name ) {
		$lang = $this->mContext->getLanguage();

		// This is supposedly a file in the filesystem. We want to look for the language
		// variant first, followed by all the fallbacks, only then falling back to the original
		// title.
		$parts = explode( '.', $name );
		$langVariants = [ $lang->getCode() ];
		$langVariants = array_merge( $langVariants, $lang->getFallbackLanguages() );

		foreach ( $langVariants as $langCode ) {
			$localParts = $parts;
			array_splice( $localParts, -1, 0, $langCode );
			$paths[] = $this->mTwigTemplatePath . '/' . implode( '.', $localParts );
		}
		$paths[] = $this->mTwigTemplatePath . '/' . $name;

		// Obtain any of the files in the $paths array
		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				return file_get_contents( $path );
			}
		}

		// If we're here, it means that we couldn't find an appropriate template. Throw the
		// generic error response.
		throw new MWException( "Twig template '$name' could not be found to load." );
	}

	/**
	 * Obtains the cache key name. Really just the name of the page with the language code appended
	 * @param string $name
	 *
	 * @return string
	 */
	public function getCacheKey( $name ) {
		return $name . '-' . $this->mContext->getLanguage()->getCode();
	}

	/**
	 * Will force a refresh after $wgTwigCacheExpiry seconds
	 * @param string $name
	 * @param int $time
	 *
	 * @return bool
	 */
	public function isFresh( $name, $time ) {
		global $wgTwigCacheExpiry;
		return ( time() < ( $time + $wgTwigCacheExpiry ) );
	}
}
