<?php

namespace MediaWiki\Extension\FlexiSkin;

interface IFlexiSkinSubscriber {

	/**
	 * Get all RL modules that use Flexiskin variables
	 * @return array
	 */
	public function getAffectedRLModules() : array;
}
