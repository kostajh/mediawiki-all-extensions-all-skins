<?php

/**
 * This script removes the empty translationnotifications-lang-* properties.
 * They became default and should not be stored.
 *
 * @author Amir E. Aharoni
 * based on Narayam/maintenance/FixNarayamDisablePref.php
 *
 * @copyright Copyright © 2012
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . "/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

class FixTranslationNotificationsEmptyLangPrefs extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->requireExtension( 'TranslationNotifications' );
	}

	public function execute() {
		$dbw = wfGetDB( DB_MASTER );

		$langPropertyPrefix = 'translationnotifications-lang-';
		$this->output( "Deleting empty {$langPropertyPrefix}* property values\n" );

		$propertyLikePattern = $dbw->buildLike( $langPropertyPrefix, $dbw->anyString() );

		$dbw->delete(
			'user_properties',
			[
				"up_property $propertyLikePattern",
				'up_value' => '',
			],
			__METHOD__
		);
	}
}

$maintClass = FixTranslationNotificationsEmptyLangPrefs::class;
require_once RUN_MAINTENANCE_IF_MAIN;
