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

	private static $servicesCache = [];

	/**
	 * Returns the instance of a map service class.
	 * If the map service is not specified or is not available, returns the default service
	 * On error returns string with error message
	 * @global array $egMultiMaps_MapServices
	 * @param string|null $serviceName
	 * @return BaseMapService|array Returns class extends \MultiMaps\BaseService or array of error messages
	 */
	public static function getServiceInstance( $serviceName = null ) {
		$services = self::getServicesList();
		if ( is_string( $services ) ) {
			return (array)$services;
		}

		$errorMessages = [];
		$lang = wfGetLangObj( true );
		if ( $serviceName ) {
			$lcServiceName = $lang->lc( $serviceName );
			if ( isset( $services[$lcServiceName] ) ) {
				$srv = $services[$lcServiceName];
			} else {
				$srv = self::getDefaultService( $services );
				$errorMessages[] = wfMessage(
					'multimaps-passed-unavailable-service',
					$serviceName,
					implode( ', ', array_keys( $services ) ),
					is_string( $srv ) ? $srv : $srv['originName']
				)->escaped();
			}
		} else {
			$srv = self::getDefaultService( $services );
		}

		if ( is_array( $srv ) ) {
			if ( !isset( $srv['service'] ) ) {
				$errorMessages[] = wfMessage(
					'multimaps-undefined-service-for-layout',
					$serviceName
				)->escaped();
				return $errorMessages;
			}

			$lcServiceName = $lang->lc( $srv['service'] );
			if ( !isset( $services[$lcServiceName] ) ) {
				$errorMessages[] = wfMessage(
					'multimaps-wrong-service-for-layout',
					$serviceName,
					$srv['service']
				)->escaped();
				return $errorMessages;
			}
			if ( is_array( $services[$lcServiceName] ) ) {
				$errorMessages[] = wfMessage(
					'multimaps-layout-defined-for-layout',
					$serviceName,
					$srv['service']
				)->escaped();
				return $errorMessages;
			}
			$className = $services[$lcServiceName];
			$layerKey = $srv['originName'];
		} else {
			$className = $srv;
			$layerKey = null;
		}

		$newClassName = "MultiMaps\\$className";
		if ( !class_exists( $newClassName ) ) {
			$errorMessages[] = wfMessage( 'multimaps-unknown-class-for-service', $newClassName )->escaped();
			return $errorMessages;
		}

		$returnService = new $newClassName( $layerKey );
		if ( !( $returnService instanceof BaseMapService ) ) {
			$errorMessages[] = wfMessage( 'multimaps-error-incorrect-class-for-service', $newClassName )->escaped();
			return $errorMessages;
		}

		if ( $errorMessages ) {
			foreach ( $errorMessages as $msg ) {
				$returnService->pushErrorMessage( $msg );
			}
		}

		return $returnService;
	}

	private static function getServicesList() {
		global $egMultiMaps_MapServices;

		if ( !self::$servicesCache ) {
			if ( !is_array( $egMultiMaps_MapServices ) || count( $egMultiMaps_MapServices ) == 0 ) {
				return wfMessage( 'multimaps-mapservices-must-not-empty-array', '$egMultiMaps_MapServices' )->escaped();
			}

			$lang = wfGetLangObj( true );
			foreach ( $egMultiMaps_MapServices as $key => $value ) {
				if ( is_int( $key ) ) {
					self::$servicesCache[$lang->lc( $value )] = $value;
				} elseif ( is_array( $value ) ) {
					self::$servicesCache[$lang->lc( $key )] = $value + [ 'originName' => $key ];
				}
			}
		}

		return self::$servicesCache;
	}

	private static function getDefaultService( $services ) {
		return array_shift( $services );
	}

}
