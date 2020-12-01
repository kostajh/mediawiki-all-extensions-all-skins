<?php
namespace MultiMaps;

use Html;

/**
 * This groupe contains all Google related files of the MultiMaps extension.
 *
 * @defgroup Google
 * @ingroup MultiMaps
 */

/**
 *
 *
 * @file Google.php
 * @ingroup Google
 *
 * @license GPL-2.0-or-later
 * @author Pavel Astakhov < pastakhov@yandex.ru >
 */
class Google extends BaseMapService {

	function __construct() {
		parent::__construct();
		$this->classname = "google";
		$this->resourceModules[] = 'ext.MultiMaps.Google';

		$urlArgs = [];
		global $egMultiMapsGoogleApiKey;
		if ( $egMultiMapsGoogleApiKey ) {
			$urlArgs['key'] = $egMultiMapsGoogleApiKey;
		}
		$this->headerItem .= Html::linkedScript( '//maps.googleapis.com/maps/api/js?' . wfArrayToCgi( $urlArgs ) ) . "\n";
	}

}
