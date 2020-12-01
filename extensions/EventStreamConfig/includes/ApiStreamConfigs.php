<?php

namespace MediaWiki\Extension\EventStreamConfig;

use ApiBase;
use ApiResult;
use MediaWiki\MediaWikiServices;

/**
 * Enables requesting whitelisted Mediawiki configs via the API.
 * Usage:
 *
 * Get stream config settings
 *   GET /w/api.php?format=json&action=streamconfigs
 *
 * Get stream config settings for specified streams
 *   GET /w/api.php?format=json&action=streamconfigs&streams=my-stream1|my-stream2
 *
 * Get stream config settings for specified streams including all settings, not just ones
 *   for client side usage.
 *   GET /w/api.php?format=json&action=streamconfigs&streams=my-stream1|my-stream2&all_settings
 */
class ApiStreamConfigs extends ApiBase {
	// 10 minutes
	private const CACHE_MAX_AGE = 600;

	/**
	 * List of stream config settings that should be returned from JSON-formatted API results as
	 * JSON arrays ([]) rather than objects ({}).
	 */
	private const STREAM_CONFIG_LIST_SETTINGS = [
		StreamConfig::TOPICS_SETTING,
		StreamConfig::TOPIC_PREFIXES_SETTING,
	];

	/**
	 * API query param used to specify target streams to get from Config
	 */
	private const API_PARAM_STREAMS = 'streams';

	/**
	 * API query param used to restrict stream config entry results to
	 * those that have settings that match these constraints.
	 * This is expected to be given as a multi array of key=val pairs.  E.g.
	 *   constraints=event_service_name=eventgate-main|other_setting=other_value
	 * This would be parsed into
	 * @code
	 *   [
	 *       'event_service_name' => 'eventgate-main',
	 *       'other_setting' => 'other_value'
	 *   ]
	 * @endcode
	 *
	 * And be used to filter for stream config entries that have these settings.
	 */
	private const API_PARAM_CONSTRAINTS = 'constraints';

	/**
	 * By default, StreamConfigs#get will only return settings for streams
	 * that are not blacklisted in StreamConfig::INTERNAL_SETTINGS.
	 * Specifying the all_settings parameter will have it return
	 * all settings in the stream's config (like schema_title, etc.).
	 */
	private const API_PARAM_ALL_SETTINGS = 'all_settings';

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->getMain()->setCacheMode( 'public' );
		$this->getMain()->setCacheMaxAge( self::CACHE_MAX_AGE );

		$targetStreams = $this->getParameter( self::API_PARAM_STREAMS );
		$includeAllSettings = $this->getParameter( self::API_PARAM_ALL_SETTINGS );
		$constraints = $this->getParameter( self::API_PARAM_CONSTRAINTS );

		$settingsConstraints = $constraints ? self::multiParamToAssocArray( $constraints ) : null;

		$streamConfigs = MediaWikiServices::getInstance()->getService(
			'EventStreamConfig.StreamConfigs'
		);

		$result = $streamConfigs->get(
			$targetStreams,
			$includeAllSettings,
			$settingsConstraints
		);

		// Recursively set all array values to be interpreted as associative arrays, so that they
		// are returned as JSON objects ({}) from a JSON-formatted response. Exclude keys which
		// are known to have list-typed values.
		self::conditionallySetArrayTypeRecursive( $result, 'assoc',
			self::STREAM_CONFIG_LIST_SETTINGS );

		$this->getResult()->addValue( null, "streams", $result );
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParams() {
		return [
			self::API_PARAM_STREAMS => [
				ApiBase::PARAM_ISMULTI => true,
			],
			self::API_PARAM_CONSTRAINTS => [
				ApiBase::PARAM_ISMULTI => true,
			],
			self::API_PARAM_ALL_SETTINGS => [
				ApiBase::PARAM_TYPE => 'boolean',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=streamconfigs&' . self::API_PARAM_STREAMS .
			'=mediawiki.page-view|mediawiki.button-click' =>
					'apihelp-streamconfigs-example-1',

			'action=streamconfigs&' . self::API_PARAM_STREAMS .
			'=mediawiki.button-click&' . self::API_PARAM_ALL_SETTINGS  =>
					'apihelp-streamconfigs-example-2',

			'action=streamconfigs&' . self::API_PARAM_STREAMS .
			'=mediawiki.button-click&' . self::API_PARAM_ALL_SETTINGS .
			'&constraints=event_service_name=eventgate-main|sample_rate=0.5' =>
					'apihelp-streamconfigs-example-3',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Extension:EventStreamConfig';
	}

	/**
	 * @inheritDoc
	 */
	public function getCustomPrinter() {
		return new ApiFormatJsonVersion2( $this->getMain(), 'json' );
	}

	/**
	 * Parses a MULTI PARAM value into an assoc array.
	 * Example:
	 *   my_param=key1=val1|key2=val2
	 * If $this->getParameter( 'my_param' ) is passed, this function will return
	 * @code
	 *   [
	 *       'key1' => 'val1',
	 *       'key2' => 'val2',
	 *   ]
	 * @endcode
	 *
	 * @param array $multiParamArray List of key=val string pairs
	 * @param string $separator Separator to use when splitting key,value pairs.  Default: =
	 * @return array
	 */
	private static function multiParamToAssocArray(
		array $multiParamArray,
		string $separator = '='
	) {
		return array_reduce(
			$multiParamArray,
			function ( $carry, $elementString ) use ( $separator ) {
				list( $key, $val ) = explode( $separator, $elementString );
				$carry[$key] = $val;
				return $carry;
			},
			[]
		);
	}

	/**
	 * A clone of ApiResult::setArrayTypeRecursive, updated to exclude specific keys.
	 * @param array &$arr
	 * @param string $type
	 * @param array $excludeKeys
	 */
	private static function conditionallySetArrayTypeRecursive(
		array &$arr,
		string $type,
		array $excludeKeys = []
	): void {
		ApiResult::setArrayType( $arr, $type );
		foreach ( $arr as $k => &$v ) {
			if ( !ApiResult::isMetadataKey( $k ) && is_array( $v ) &&
				!in_array( $k, $excludeKeys ) ) {
				self::conditionallySetArrayTypeRecursive( $v, $type, $excludeKeys );
			}
		}
	}
}
