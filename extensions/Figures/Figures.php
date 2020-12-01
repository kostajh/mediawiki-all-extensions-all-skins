<?php

class Figures {

	/**
	 * @param Parser $parser
	 */
	public static function onParserSetup( Parser $parser ) {
		$parser->setFunctionHook( 'figure', 'Figures::parseFigureParserExtension' );
		$parser->setFunctionHook( 'xref', 'Figures::parseXrefParserExtension' );
	}

	/**
	 * @param Parser $parser
	 * @return array
	 */
	public static function parseFigureParserExtension( $parser ) {
		$options = self::extractOptions( array_slice( func_get_args(), 1 ) );
		$figure_label = $options['label'];
		$figure_content = $options['content'];
		$figure_content = $parser->recursiveTagParse( $figure_content );

		$output = '<figure id="' . str_replace( ' ', '_', $figure_label ) . '" xreflabel="' . $figure_label . '">' . $figure_content . '</figure>';
		return [ $output, 'noparse' => true, 'isHTML' => true ];
	}

	/**
	 * @param Parser $parser
	 * @return array
	 */
	public static function parseXrefParserExtension( $parser ) {
		$options = self::extractOptions( array_slice( func_get_args(), 1 ) );
		$figure_label = $options['label'];
		$figure_page = $options['page'];
		$figure_page_link = Title::newFromText( $figure_page )->getFullURL();

		$output = '<a class="xref" href="' . $figure_page_link . '#' . str_replace( ' ', '_', $figure_label ) . '">' . $figure_label . '</a>';
		return [ $output, 'noparse' => true, 'isHTML' => true ];
	}

	/**
	 * @param string[] $options
	 * @return string[]
	 */
	public static function extractOptions( array $options ) {
		$results = [];

		foreach ( $options as $option ) {
			$pair = explode( '=', $option, 2 );
			if ( count( $pair ) === 2 ) {
				$name = trim( $pair[0] );
				$value = trim( $pair[1] );
				$results[$name] = $value;
			}

			if ( count( $pair ) === 1 ) {
				$name = trim( $pair[0] );
				$results[$name] = true;
			}
		}
		return $results;
	}

}
