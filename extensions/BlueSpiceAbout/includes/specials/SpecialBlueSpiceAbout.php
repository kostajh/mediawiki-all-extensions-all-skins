<?php

/**
 * Renders the BlueSpice About special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 *
 * @package    BlueSpiceAbout
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

class SpecialBlueSpiceAbout extends \BlueSpice\SpecialPage {

	/**
	 * Constructor of SpecialBlueSpiceAbout class
	 */
	public function __construct() {
		parent::__construct( 'BlueSpiceAbout', 'bluespiceabout-viewspecialpage' );
	}

	/**
	 * Renders special page output.
	 * @param string $sParameter Not used.
	 * @return bool Allow other hooked methods to be executed. Always true.
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$sLang = $this->getLanguage()->getCode();
		switch ( substr( $sLang, 0, 2 ) ) {
			case "de":
				$sUrl = "https://bluespice.com/de/ueber-bluespice/";
				break;
			default:
				$sUrl = "https://bluespice.com/about-bluespice/";
		}

		$sOutHTML = '<iframe src="' . $sUrl . '" id="bluespiceaboutremote" '
			. 'name="bluespiceaboutremote" style="width:100%;border:0px;min-height:1200px;"></iframe>';

		$oOutputPage = $this->getOutput();

		$oOutputPage->addHTML( $sOutHTML );

		return true;
	}

}
