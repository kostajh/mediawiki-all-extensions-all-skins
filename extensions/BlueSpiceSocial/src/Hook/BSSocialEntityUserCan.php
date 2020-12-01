<?php

namespace BlueSpice\Social\Hook;

use BlueSpice\Hook;
use BlueSpice\Social\Entity;

abstract class BSSocialEntityUserCan extends Hook {

	/**
	 *
	 * @var Entity
	 */
	protected $oEntity = null;

	/**
	 *
	 * @var \User
	 */
	protected $oUser = null;

	/**
	 *
	 * @var string
	 */
	protected $sPermission = null;

	/**
	 *
	 * @var \Title
	 */
	protected $oTitle = null;

	/**
	 *
	 * @var \Status
	 */
	protected $oStatus = null;

	/**
	 *
	 * @var string
	 */
	protected $sAction = null;

	/**
	 *
	 * @param Entity $oEntity
	 * @param \User $oUser
	 * @param string $sPermission
	 * @param \Title $oTitle
	 * @param \Status &$oStatus
	 * @param string $sAction
	 * @return bool
	 */
	public static function callback( $oEntity, $oUser, $sPermission, $oTitle, &$oStatus,
		$sAction ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$oEntity,
			$oUser,
			$sPermission,
			$oTitle,
			$oStatus,
			$sAction
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param Entity $oEntity
	 * @param \User $oUser
	 * @param string $sPermission
	 * @param \Title $oTitle
	 * @param \Status &$oStatus
	 * @param string $sAction
	 * @return boolean
	 */
	public function __construct( $context, $config, $oEntity, $oUser, $sPermission,
		$oTitle, &$oStatus, $sAction ) {
		parent::__construct( $context, $config );

		$this->oEntity = $oEntity;
		$this->oUser = $oUser;
		$this->sPermission = $sPermission;
		$this->oTitle = $oTitle;
		$this->oStatus = &$oStatus;
		$this->sAction = $sAction;
	}
}
