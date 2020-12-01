<?php

use MediaWiki\MediaWikiServices;

class BlueSpiceExtendedFilelistHooks {

	/**
	 *
	 * @param string &$aSpecialPages
	 * @return bool
	 */
	public static function onSpecialPage_initList( &$aSpecialPages ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );

		if ( $config->get( 'EFLOverrideStandardFilelist' ) ) {
			$aSpecialPages['Listfiles'] = 'SpecialBlueSpiceExtendedFilelist';
		}
		return true;
	}

	/**
	 * Conditionally register modules
	 * @param ResourceLoader &$resourceLoader
	 * @return bool
	 */
	public static function onResourceLoaderRegisterModules( &$resourceLoader ) {
		$extPath = dirname( __DIR__ );
		if ( $resourceLoader->isModuleRegistered( 'mmv.bootstrap' ) ) {
			$resourceLoader->register(
				'ext.bluespice.extendedFilelist.plugin.mmv',
				[
					'scripts' => 'bluespice.extendedFilelist.plugin.mmv.js',
					'dependencies' => [
						'mmv.bootstrap'
					],
					'localBasePath' => $extPath . '/resources',
					'remoteExtPath' => 'BlueSpiceExtendedFilelist/resources'
				]
			);
		}
		return true;
	}
}
