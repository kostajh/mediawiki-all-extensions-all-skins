<?php

class PhabTaskGraphHooks {

	private static $graphNum = 0;

	/**
	 * Implement ParserFirstCallInit hook.
	 * See https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 * Declare parser function.
	 * @since 2.0
	 * @param Parser &$parser the Parser object
	 */
	public static function onParserFirstCallInit( &$parser ) {
		$parser->setFunctionHook( 'datemultilinegraph',
			[ self::class, 'drawDateMultiLineGraph' ] );
		$parser->setFunctionHook( 'datebarchart',
			[ self::class, 'drawDateBarChart' ] );
	}

	/**
	 * Handle datemultiplinegraph parser function.
	 * @since 2.0
	 * @param Parser $parser the Parser object
	 * @return string HTML div for the graph
	 */
	public static function drawDateMultiLineGraph( Parser $parser ) {
		$params = self::parseParams( func_get_args() );

		$output = $parser->getOutput();
		$output->addModules( 'ext.PTG_DateMultiLineGraph' );

		if ( isset( $params['width'] ) ) {
			$width = $params['width'];
		} else {
			$width = 800;
		}

		if ( isset( $params['height'] ) ) {
			$height = $params['height'];
		} else {
			$height = 400;
		}

		if ( isset( $params['xaxis'] ) ) {
			$xaxis = $params['xaxis'];
		} else {
			$xaxis = null;
		}

		if ( isset( $params['yaxis'] ) ) {
			$yaxis = $params['yaxis'];
		} else {
			$yaxis = null;
		}

		if ( isset( $params['delim'] ) ) {
			$delim = $params['delim'];
		} else {
			$delim = ',';
		}

		$rawdata = [];
		$lines = explode( PHP_EOL, $params['data'] );
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( strlen( $line ) > 0 ) {
				$items = array_map( 'trim', explode( $delim, $line, 3 ) );
				if ( count( $items ) == 3 ) {
					$date = date_create( $items[1] );
					if ( $date ) {
						$date = date_format( $date, 'Y-m-d' );
						$name = $items[0];
						$value = intval( $items[2] );
						if ( !isset( $rawdata[$name] ) ) {
							$rawdata[$name] = [];
						}
						$rawdata[$name][] = [ $date, $value ];
					}
				}
			}
		}

		$data = [];
		foreach ( $rawdata as $key => $value ) {
			$data[] = [
				'name' => $key,
				'data' => $value
			];
		}

		$graphName = 'PTG_DateMultiLineGraph_' . self::$graphNum++;
		$config = [
			'id' => $graphName,
			'width' => $width,
			'height' => $height,
			'xaxis' => $xaxis,
			'yaxis' => $yaxis,
			'data' => $data
		];
		self::addJsConfigVars( $output, 'PTG_DateMultiLineGraphConfig', $config );

		$html = Html::element( 'div',
				[
					'id' => $graphName
				] );

		return $html;
	}

	/**
	 * Handle datebarchart parser function.
	 * @since 2.0
	 * @param Parser $parser the Parser object
	 * @return string HTML div for the bar chart
	 */
	public static function drawDateBarChart( Parser $parser ) {
		$params = self::parseParams( func_get_args() );

		$output = $parser->getOutput();
		$output->addModules( 'ext.PTG_DateBarChart' );

		if ( isset( $params['width'] ) ) {
			$width = $params['width'];
		} else {
			$width = 800;
		}

		if ( isset( $params['height'] ) ) {
			$height = $params['height'];
		} else {
			$height = 400;
		}

		if ( isset( $params['xaxis'] ) ) {
			$xaxis = $params['xaxis'];
		} else {
			$xaxis = null;
		}

		if ( isset( $params['yaxis'] ) ) {
			$yaxis = $params['yaxis'];
		} else {
			$yaxis = null;
		}

		if ( isset( $params['delim'] ) ) {
			$delim = $params['delim'];
		} else {
			$delim = ',';
		}

		$data = [];
		$lines = explode( PHP_EOL, $params['data'] );
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( strlen( $line ) > 0 ) {
				$items = array_map( 'trim', explode( $delim, $line, 2 ) );
				if ( count( $items ) == 2 ) {
					$date = date_create( $items[0] );
					if ( $date ) {
						$date = date_format( $date, 'Y-m-d' );
						$value = intval( $items[1] );
						$data[] = [ $date, $value ];
					}
				}
			}
		}

		$graphName = 'PTG_DateBarChart_' . self::$graphNum++;
		$config = [
			'id' => $graphName,
			'width' => $width,
			'height' => $height,
			'xaxis' => $xaxis,
			'yaxis' => $yaxis,
			'data' => $data
		];
		self::addJsConfigVars( $output, 'PTG_DateBarChartConfig', $config );

		$html = Html::element( 'div',
				[
					'id' => $graphName
				] );

		return $html;
	}

	private static function parseParams( $params ) {
		array_shift( $params );
		$paramArray = [];
		foreach ( $params as $param ) {
			$split = explode( '=', $param, 2 );
			if ( count( $split ) > 1 ) {
				$paramArray[$split[0]] = $split[1];
			}
		}
		return $paramArray;
	}

	private static function addJsConfigVars( $output, $key, $value ) {
		$currentValue = $output->getJsConfigVars();
		if ( array_key_exists( $key, $currentValue ) ) {
			$currentValue[$key][] = $value;
			$output->addJsConfigVars( $key, $currentValue[$key] );
		} else {
			$output->addJsConfigVars( $key, [ $value ] );
		}
	}
}
