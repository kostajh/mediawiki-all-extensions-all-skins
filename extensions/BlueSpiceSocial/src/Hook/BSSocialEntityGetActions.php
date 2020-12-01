<?php

namespace BlueSpice\Social\Hook;

use BlueSpice\Hook;
use BlueSpice\Social\Entity;

abstract class BSSocialEntityGetActions extends Hook {

	/**
	 *
	 * @var Entity
	 */
	protected $oEntity = null;

	/**
	 *
	 * @var array
	 */
	protected $aActions = null;

	/**
	 *
	 * @param Entity $oEntity
	 * @param array &$aActions
	 * @return bool
	 */
	public static function callback( $oEntity, &$aActions ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$oEntity,
			$aActions
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param Entity $oEntity
	 * @param array &$aActions
	 */
	public function __construct( $context, $config, $oEntity, &$aActions ) {
		parent::__construct( $context, $config );

		$this->oEntity = $oEntity;
		$this->aActions = &$aActions;
	}
}
