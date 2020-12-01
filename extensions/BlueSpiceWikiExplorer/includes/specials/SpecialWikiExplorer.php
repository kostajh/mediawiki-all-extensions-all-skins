<?php

/**
 * Renders the WikiExplorer special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-network.hk>
 * @version    $Id: SpecialExtendedStatistics.class.php 7886 2012-12-21 00:14:23Z mglaser $
 * @package    BlueSpice_Extensions
 * @subpackage WikiExplorer
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\Special\ExtJSBase;

/**
 * WikiExplorer special page that renders the list itself
 * @package BlueSpice_Extensions
 * @subpackage WikiExplorer
 */
class SpecialWikiExplorer extends ExtJSBase {

	/**
	 * Constructor of SpecialExtendedStatistics
	 */
	public function __construct() {
		parent::__construct( 'WikiExplorer' );
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'superlist_grid';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		$modules = [
			'ext.bluespice.wikiExplorer'
		];
		if ( isset( $GLOBALS['wgResourceModules']['ext.bluespice.responsibleEditors.superList'] ) ) {
			// Attach SuperList plugin if in context
			$modules[] = 'ext.bluespice.responsibleEditors.superList';
		}

		return $modules;
	}
}
