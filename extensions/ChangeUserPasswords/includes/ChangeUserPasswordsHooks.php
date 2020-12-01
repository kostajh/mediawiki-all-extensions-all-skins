<?php
/**
 * Hooks for ChangeUserPasswords extension
 *
 * @file
 * @ingroup Extensions
 */

class ChangeUserPasswordsHooks {

	/**
	 * @param ALTree &$adminLinksTree
	 */
	public static function addToAdminLinks( ALTree &$adminLinksTree ) {
		$generalSection = $adminLinksTree->getSection( wfMessage( 'adminlinks_general' )->text() );
		$extensionsRow = $generalSection->getRow( 'extensions' );

		if ( $extensionsRow === null ) {
			$extensionsRow = new ALRow( 'extensions' );
			$generalSection->addRow( $extensionsRow );
		}

		$extensionsRow->addItem( ALItem::newFromSpecialPage( 'ChangeUserPasswords' ) );
	}
}
