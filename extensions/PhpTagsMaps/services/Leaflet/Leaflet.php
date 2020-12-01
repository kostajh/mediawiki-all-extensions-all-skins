<?php
namespace MultiMaps;

/**
 * This groupe contains all Leaflet related files of the MultiMaps extension.
 *
 * @defgroup Leaflet
 * @ingroup MultiMaps
 */

/**
 *
 *
 * @file Leaflet.php
 * @ingroup Leaflet
 *
 * @license GPL-2.0-or-later
 * @author Pavel Astakhov < pastakhov@yandex.ru >
 */
class Leaflet extends BaseMapService {

	function __construct() {
		parent::__construct();
		$this->classname = "leaflet";
		$this->resourceModules[] = 'ext.MultiMaps.Leaflet';

		$leafletPath = $GLOBALS['egMultiMapsScriptPath'] . '/services/Leaflet/leaflet';
		$this->headerItem .= \Html::linkedStyle( "$leafletPath/leaflet.css" ) .
			'<!--[if lte IE 8]>' . \Html::linkedStyle( "$leafletPath/leaflet.ie.css" ) . '<![endif]-->' .
			\Html::linkedScript( "$leafletPath/leaflet.js" );
	}
}
