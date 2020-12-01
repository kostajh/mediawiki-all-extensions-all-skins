<?php

namespace PopupAlerts;

use Html;
use Parser;
use PPFrame;

class PopupAlerts {

	/**
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param array $args
	 *
	 * @return string
	 */
	public static function renderPopup( Parser $parser, PPFrame $frame, array $args ) {
		// Fallback immediately if popup does not contain any content
		if ( !count( $args ) ) {
			return '';
		}

		// Adding leading \n here as a workaround of `trim` at Parser::braceSubstitution()
		$content = "\n" . $frame->expand( $args[0] );
		$hash = hash( 'md5', $content );

		$expire = 0;
		if ( count( $args ) > 1 ) {
			$expire = (int)trim( $frame->expand( $args[1] ) );
		}

		$html = Html::rawElement( 'div', [
			'style' => 'display: none',
			'class' => 'popup-alerts-source',
			'data-hash' => $hash,
			'data-expire' => $expire
		], $content );

		$parser->getOutput()->addModules( 'ext.popupalerts.main' );

		return $html;
	}

}
