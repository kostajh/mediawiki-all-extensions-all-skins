<?php

namespace BlueSpice\Social\Hook;

use BlueSpice\Hook;

abstract class BSSocialModuleDepths extends Hook {

	/**
	 *
	 * @var \OutputPage
	 */
	protected $oOutput = null;

	/**
	 *
	 * @var \Skin
	 */
	protected $oSkin = null;

	/**
	 *
	 * @var array
	 */
	protected $aConfig = null;

	/**
	 *
	 * @var array
	 */
	protected $aScripts = null;

	/**
	 *
	 * @var array
	 */
	protected $aStyles = null;

	/**
	 *
	 * @var array
	 */
	protected $aVarMsgKeys = null;

	/**
	 *
	 * @param \OutputPage $oOutput
	 * @param \Skin $oSkin
	 * @param array &$aConfig
	 * @param array &$aScripts
	 * @param array &$aStyles
	 * @param array &$aVarMsgKeys
	 * @return bool
	 */
	public static function callback( $oOutput, $oSkin, &$aConfig, &$aScripts, &$aStyles,
		&$aVarMsgKeys ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$oOutput,
			$oSkin,
			$aConfig,
			$aScripts,
			$aStyles,
			$aVarMsgKeys
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \OutputPage $oOutput
	 * @param \Skin $oSkin
	 * @param array &$aConfig
	 * @param array &$aScripts
	 * @param array &$aStyles
	 * @param array &$aVarMsgKeys
	 */
	public function __construct( $context, $config, $oOutput, $oSkin, &$aConfig,
		&$aScripts, &$aStyles, &$aVarMsgKeys ) {
		parent::__construct( $context, $config );

		$this->oOutput = $oOutput;
		$this->oSkin = $oSkin;
		$this->aConfig = &$aConfig;
		$this->aScripts = &$aScripts;
		$this->aStyles = &$aStyles;
		$this->aVarMsgKeys = &$aVarMsgKeys;
	}
}
