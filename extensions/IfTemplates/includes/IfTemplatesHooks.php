<?php

class IfTemplatesHooks {

	/**
	 * @param Parser &$parser
	 */
	public static function ifTemplatesSetupParserFunction( &$parser ) {
		$parser->setFunctionHook( 'iftemplates', 'IfTemplates::iftemplatesObj', Parser::SFH_OBJECT_ARGS );
	}
}
