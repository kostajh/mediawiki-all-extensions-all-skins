<?php

class RandomAreaHooks {

	/**
	 * @param Parser $parser
	 */
	public static function init( $parser ) {
		$parser->setHook( 'randomArea', [ __CLASS__, 'renderRandomArea' ] );
	}

	/**
	 * @param string $input
	 * @param string[] $argv
	 * @param Parser $parser
	 * @return string
	 */
	public static function renderRandomArea( $input, $argv, $parser ) {
		$count = $argv['count'];
		if ( $count < 0 || !$count ) {
			$count = 1;
		}
		$nsPrefix = $argv['nsprefix'];
		$include = (bool)$argv['include'];
		$values = explode( "\n", $input );
		$valCount = count( $values ) - 1;
		if ( $valCount < $count ) {
			return $parser->internalParse( "Exception:
				<br />Array out of Bounce - >
				Only " . $valCount . " items available, count = " . $count );
		}
		$valueIndex = [];
		$randOut = "";
		$i = 0;
		while ( $i < $count ) {
			$randVal = rand( 0, $valCount );
			$randTemp = trim( $values[$randVal] );
			if ( strlen( $randTemp ) > 0 && !array_key_exists( $randVal, $valueIndex ) ) {
				if ( strlen( $nsPrefix ) > 1 ) {
					$randOutTemp = $nsPrefix . $randTemp;
				} else {
					$randOutTemp = $randTemp;
				}

				if ( $include ) {
					$randOut .= "{{:" . $randOutTemp . "}}";
				} else {
					$randOut .= $randOutTemp;
				}
				$valueIndex[$randVal] = true;
				$i++;
			}
		}
		return $parser->internalParse( $randOut );
	}
}
