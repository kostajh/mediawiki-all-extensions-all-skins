<?php
namespace MultiMaps;

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
		$urlArgs['sensor'] = 'false';
		$urlArgs['v'] = '3.10';
		$this->headerItem .= \Html::linkedScript( 'https://maps.googleapis.com/maps/api/js?' . wfArrayToCgi( $urlArgs ) ) . "\n";
	}

}
