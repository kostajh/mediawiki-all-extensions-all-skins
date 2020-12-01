<?php

class UIFeedbackHooks {
	/**
	 * @param DatabaseUpdater $updater
	 */
	public static function createUIFeedbackTable( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'uifeedback',
			__DIR__ . '/../sql/table.sql', true );
	}

	/**
	 * @param OutputPage $out
	 */
	public static function uifeedbackBeforePageDisplay( $out ) {
		if ( $out->getUser()->isAllowed( 'read_uifeedback' ) ) {
			$out->addModules( [
				'ext.uiFeedback',
				'jquery.ui.draggable',
				'jquery.ui.resizable'
			] );
		}
	}
}
