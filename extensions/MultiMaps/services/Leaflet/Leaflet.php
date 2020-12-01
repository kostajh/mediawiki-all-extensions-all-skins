<?php
namespace MultiMaps;

use Html;

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

	/**
	 * @param string|null $layerKey
	 */
	function __construct( $layerKey = null ) {
		global $egMultiMapsScriptPath;

		parent::__construct();

		$this->classname = "leaflet";
		$this->resourceModules[] = 'ext.MultiMaps.Leaflet';

		$this->setLayerByKey( $layerKey );

		$leafletPath = $egMultiMapsScriptPath . '/services/Leaflet/leaflet';
		$this->headerItem .= Html::linkedStyle( "$leafletPath/leaflet.css" ) .
			'<!--[if lte IE 8]>' . Html::linkedStyle( "$leafletPath/leaflet.ie.css" ) . '<![endif]-->' .
			Html::linkedScript( "$leafletPath/leaflet.js" );
	}

	/**
	 * @param string $layerKey
	 */
	private function setLayerByKey( $layerKey ) {
		global $egMultiMaps_MapServices;

		if ( $layerKey ) {
			$leafletLayer = $egMultiMaps_MapServices[$layerKey];
			if ( isset( $leafletLayer['source'] ) && isset( $leafletLayer['attribution'] ) ) {
				$this->properties['tileLayer'] = $leafletLayer['source'];
				$this->properties['attribution'] = $leafletLayer['attribution'];
				return;
			} else {
				$this->pushErrorMessage( wfMessage( 'multimaps-leaflet-undefined-parameters-for-layer', $layerKey )->escaped() );
			}
		}

		// Default layer
		$this->properties['tileLayer'] = '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
		$this->properties['attribution'] = '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors';
	}
}
