<?php
namespace MultiMaps;

/**
 * This class allows you to work with a collection of defined services
 *
 * @file MapServices.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 */

class MapServices {

	/**
	 * Returns the instance of a map service class.
	 * If the map service is not specified or is not available, returns the default service
	 * On error returns string with error message
	 * @global array $egMultiMaps_MapServices
	 * @param string|null $servicename
	 * @return MultiMaps\BaseMapService return class extends \MultiMaps\BaseService or string with error message
	 */
	public static function getServiceInstance( $servicename = null ) {
		global $egMultiMaps_MapServices;

		if ( is_array( $egMultiMaps_MapServices ) === false || count( $egMultiMaps_MapServices ) == 0 ) {
			return \wfMessage( 'multimaps-mapservices-must-not-empty-array', '$egMultiMaps_MapServices' )->escaped();
		}

		$errormessage = '';
		if ( is_string( $servicename ) ) {
			$classkey = array_search( strtolower( $servicename ), array_map( 'strtolower', $egMultiMaps_MapServices ) );
			if ( $classkey === false ) { // a user-specified service can not be found
				$classname = $egMultiMaps_MapServices[0];
				$errormessage = \wfMessage( 'multimaps-passed-unavailable-service', $servicename, implode( ', ', $egMultiMaps_MapServices ), $classname )->escaped();
			} else {
				$classname = $egMultiMaps_MapServices[$classkey];
			}
		} else {
			$classname = $egMultiMaps_MapServices[0];
		}

		$newclassname = "MultiMaps\\$classname";
		if ( !class_exists( $newclassname ) ) {
			if ( $errormessage != '' ) {
				$errormessage .= '<br />';
			}
			return $errormessage . \wfMessage( 'multimaps-unknown-class-for-service', $newclassname )->escaped();
		}

		$returnservice = new $newclassname();
		if ( !( $returnservice instanceof BaseMapService ) ) {
			return \wfMessage( 'multimaps-error-incorrect-class-for-service', $newclassname )->escaped();
		}

		if ( $errormessage != '' ) {
			$returnservice->pushErrorMessage( $errormessage );
		}

		return $returnservice;
	}

}
