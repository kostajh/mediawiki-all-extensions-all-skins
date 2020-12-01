<?php

/**
 * Renders the Expiry special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-network.hk>
 * @version    $Id: SpecialReview.class.php 9608 2013-06-05 10:39:04Z sulbricht $
 * @package    BlueSpice_Extensions
 * @subpackage Reminder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\Special\ManagerBase;

/**
 * Expiry special page that renders the Expiry dialogues and lists
 * @package BlueSpice_Extensions
 * @subpackage Reminder
 */
class SpecialExpiry extends ManagerBase {

	/**
	 * Constructor of SpecialReview class
	 */
	public function __construct() {
		parent::__construct( 'Expiry', 'read' );
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-expiry-overview-grid';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.bluespice.expiry.special',
			'ext.bluespice.expiry.special.styles'
		];
	}
}
