<?php

class Prop {

	/**
	 * Main hook
	 *
	 * @param Parser &$parser Parser object
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'Prop', 'Prop::onFunctionHook' );
	}

	/**
	 * Parser function hook
	 *
	 * @param Parser &$parser Parser object
	 * @param string $name Name of the property
	 * @param string|null $value Value of the property
	 * @return string
	 */
	public static function onFunctionHook( Parser &$parser, $name, $value = null ) {
		$output = $parser->getOutput();
		if ( $value ) {
			$output->setProperty( $name, $value );
		} else {
			return $output->getProperty( $name );
		}
	}
}
