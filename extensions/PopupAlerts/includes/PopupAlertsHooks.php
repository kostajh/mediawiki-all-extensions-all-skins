<?php

namespace PopupAlerts;

use MWException;
use Parser;

class PopupAlertsHooks {

	/**
	 * @param Parser $parser
	 *
	 * @throws MWException
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'popupalert', 'PopupAlerts\\PopupAlerts::renderPopup', Parser::SFH_OBJECT_ARGS );
	}

}
