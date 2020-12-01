<?php

namespace BlueSpice\VisualEditorConnector\ClientConfig;

use Config;
use MediaWiki\MediaWikiServices;

abstract class ConfigVariable extends ConfigBase {
	/** @var Config */
	protected $config;

	/**
	 * @return static
	 */
	public static function factory() {
		return new static(
			MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' )
		);
	}

	/**
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * @return mixed|null if variable not found
	 */
	public function getValue() {
		if ( $this->config->has( $this->getVariableName() ) ) {
			return $this->config->get( $this->getVariableName() );
		}

		return null;
	}

	/**
	 * Get server-side variable name
	 *
	 * @return string
	 */
	abstract public function getVariableName();
}
