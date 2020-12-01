<?php

/**
 * Special page for InterWikiLinks for MediaWiki
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    BlueSpice_InterWikiLinks
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
class SpecialInterWikiLinks extends \BlueSpice\SpecialPage {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( 'InterWikiLinks', 'interwikilinks-viewspecialpage' );
	}

	/**
	 *
	 * @param string $par URL parameters to special page.
	 */
	public function execute( $par ) {
		parent::execute( $par );
		$outputPage = $this->getOutput();

		$outputPage->addModules( 'bluespice.insertLink.interWikiLinks' );

		$outputPage->addModules( 'ext.bluespice.interWikiLinks' );
		$outputPage->addHTML( '<div id="InterWikiLinksGrid" class="bs-manager-container"></div>' );
	}

}
