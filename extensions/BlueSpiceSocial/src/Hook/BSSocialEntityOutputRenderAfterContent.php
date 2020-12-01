<?php

namespace BlueSpice\Social\Hook;

use BlueSpice\Hook;
use BlueSpice\Social\Renderer\Entity;

abstract class BSSocialEntityOutputRenderAfterContent extends Hook {

	/**
	 *
	 * @var Entity
	 */
	protected $oEntityOutput = null;

	/**
	 *
	 * @var array
	 */
	protected $aViews = null;

	/**
	 *
	 * @var string
	 */
	protected $sOut = null;

	/**
	 *
	 * @var mixed
	 */
	protected $mVal = null;

	/**
	 *
	 * @var string
	 */
	protected $sType = null;

	/**
	 *
	 * @param Entity $oEntityOutput
	 * @param array &$aViews
	 * @param string &$sOut
	 * @param mixed $mVal
	 * @param string $sType
	 * @return bool
	 */
	public static function callback( $oEntityOutput, &$aViews, &$sOut, $mVal, $sType ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$oEntityOutput,
			$aViews,
			$sOut,
			$mVal,
			$sType
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param Entity $oEntityOutput
	 * @param array &$aViews
	 * @param string &$sOut
	 * @param mixed $mVal
	 * @param string $sType
	 * @return boolean
	 */
	public function __construct( $context, $config, $oEntityOutput, &$aViews, &$sOut,
		$mVal, $sType ) {
		parent::__construct( $context, $config );

		$this->oEntityOutput = $oEntityOutput;
		$this->aViews = &$aViews;
		$this->sOut = &$sOut;
		$this->mVal = $mVal;
		$this->sType = $sType;
	}
}
