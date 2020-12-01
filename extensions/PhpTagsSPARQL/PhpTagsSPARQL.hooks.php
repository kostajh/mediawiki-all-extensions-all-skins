<?php


/**
 * PhpTags SPARQL MediaWiki Hooks.
 *
 * @file PhpTagsSPARQLHooks.hooks.php
 * @ingroup PhpTags
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @licence GNU General Public Licence 2.0 or later
 */
class PhpTagsSPARQLHooks {

	/**
	 * Check on version compatibility
	 * @return boolean
	 */
	public static function onParserFirstCallInit() {
		$extRegistry = ExtensionRegistry::getInstance();
		$phpTagsLoaded = $extRegistry->isLoaded( 'PhpTags' );
		//if ( !$extRegistry->isLoaded( 'PhpTags' ) ) { use PHPTAGS_VERSION for backward compatibility
		if ( !($phpTagsLoaded || defined( 'PHPTAGS_VERSION' )) ) {
			throw new MWException( "\n\nYou need to have the PhpTags extension installed in order to use the PhpTags SPARQL extension." );
		}
		if ( $phpTagsLoaded ) {
			$neededVersion = '5.8.0';
			$phpTagsVersion = $extRegistry->getAllThings()['PhpTags']['version'];
			if ( version_compare( $phpTagsVersion, $neededVersion, '<' ) ) {
				throw new MWException( "\n\nThis version of the PhpTags SPARQL extension requires the PhpTags extension $neededVersion or above.\n You have $phpTagsVersion. Please update it." );
			}
		}
		if ( !$phpTagsLoaded || PHPTAGS_HOOK_RELEASE != 8 ) {
			throw new MWException( "\n\nThis version of the PhpTags SPARQL extension is outdated and not compatible with current version of the PhpTags extension.\n Please update it." );
		}
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public static function onPhpTagsRuntimeFirstInit() {
		$version = ExtensionRegistry::getInstance()->getAllThings()['PhpTags SPARQL']['version'];
		\PhpTags\Hooks::addJsonFile( __DIR__ . '/PhpTagsSPARQL.json', $version );
		return true;
	}

}
