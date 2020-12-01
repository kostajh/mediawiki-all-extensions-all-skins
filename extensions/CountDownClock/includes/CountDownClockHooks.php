<?php
class CountDownClockHooks {

	/**
	 * Register the parser functions.
	 *
	 * @param Parser &$parser
	 * @return null
	 */
	public static function onParserSetup( &$parser ) {
		// Set {{#countDownClock:}}
		$parser->setFunctionHook( 'countDownClock', [ __CLASS__, 'renderTime' ] );
	}

	/**
	 * Render the output of {{#countDownClock:}}
	 *
	 * @param Parser $parser
	 * @param string $param1
	 * @return string HTML string
	 */
	public static function renderTime( $parser, $param1 = '' ) {
		$endTimeForcountDownClock = $param1;

		$d = DateTime::createFromFormat( 'Y-m-d H:i:s', $endTimeForcountDownClock );
		$isValidTime = $d && $d->format( 'Y-m-d H:i:s' ) == $endTimeForcountDownClock;

		if ( $isValidTime === false ) {

			$output = Html::element(
				'time',
				[ 'id' => 'countDownClock', 'style' => 'color:red;' ],
				wfMessage( 'countDownClock-invalidtime' )->text()
			);

			return [ $output, 'isHTML' => true ];
		}

		// Add Javascript Module
		$parser->getOutput()->addModules( 'ext.countDownClock' );

		// Pass endTime to Javascript
		$parser->getOutput()->addJsConfigVars( 'endTime', $endTimeForcountDownClock );

		// Create the time element
		$output = Html::element(
			'time',
			[ 'id' => 'countDownClock' ]
		);

		return [ $output, 'isHTML' => true ];
	}

}
