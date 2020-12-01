<?php

/**
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 */
// TODO: check include path
// ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).'/../../');

define( "MEDIAWIKI", "test" );
include_once __DIR__ . '/../../Settings.php';
include_once __DIR__ . '/../../includes/BaseMapService.php';
include_once __DIR__ . '/../../includes/GeoCoordinate.php';
include_once __DIR__ . '/../../includes/mapelements/BaseMapElement.php';
include_once __DIR__ . '/../../includes/mapelements/Line.php';
include_once __DIR__ . '/../../includes/mapelements/Polygon.php';
