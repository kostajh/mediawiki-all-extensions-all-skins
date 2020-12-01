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
class Yandex extends BaseMapService {

	function __construct() {
		parent::__construct();
		$this->classname = "yandex";
		$this->resourceModules[] = 'ext.MultiMaps.Yandex';

		$urlArgs = [];
		$urlArgs['load'] = 'package.standard,package.geoObjects';
		$urlArgs['lang'] = 'ru-RU';
		$this->headerItem .= \Html::linkedScript( 'https://api-maps.yandex.ru/2.0-stable/?' . wfArrayToCgi( $urlArgs ) ) . "\n";
	}

}
