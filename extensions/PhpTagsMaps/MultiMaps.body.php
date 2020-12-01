<?php
/**
 * Main classes of MultiMaps extension.
 *
 * @file MultiMaps.body.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 */

class MultiMaps {

	/**
	 * Render map on wikipage using appropriate service class
	 *
	 * @param Parser &$parser
	 * @return string
	 */
	public static function renderParserFunction_showmap( Parser &$parser ) {
		$params = func_get_args();
		array_shift( $params );

		$nameService = null;
		$matches = [];
		foreach ( $params as $value ) {
			if ( preg_match( '/^\s*service\s*=\s*(.+)\s*$/si', $value, $matches ) ) {
				$nameService = $matches[1];
				break;
			}
		}
		$service = \MultiMaps\MapServices::getServiceInstance( $nameService );

		if ( !( $service instanceof \MultiMaps\BaseMapService ) ) {
				return "<span class=\"error\"> $service </span>";
		}

		$service->parse( $params, false );
		$service->addDependencies( $parser );
		return $service->render();
	}

	/**
	 * Recursive search needle in array
	 * @param string $needle
	 * @param array $haystack
	 * @return mixed array key or false
	 */
	public static function recursive_array_search( $needle, $haystack ) {
		foreach ( $haystack as $key => $value ) {
			$current_key = $key;
			if ( $needle === $value or ( is_array( $value ) && self::recursive_array_search( $needle, $value ) !== false ) ) {
				return $current_key;
			}
		}
		return false;
	}

}
