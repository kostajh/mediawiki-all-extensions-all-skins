<?php

class GoogleDocTag {

	/**
	 * @param Parser $parser
	 */
	public static function setParserHook( Parser $parser ) {
		$parser->setHook( 'gdoc', 'GoogleDocTag::embedDoc' );
		$parser->setHook( 'googledoc', 'GoogleDocTag::embedDoc' );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return bool
	 */
	public static function embedDoc( $input, array $args, Parser $parser, PPFrame $frame ) {
		if ( !array_key_exists( 'id', $args ) ) {
			return wfMessage( 'googledoctag-noid' )->parse();
		}
		$id = $args['id'];

		$width = array_key_exists( 'width', $args ) ? $args['width'] : '100%';
		$height = array_key_exists( 'height', $args ) ? $args['height'] : '1000px';

		return Html::element(
			'iframe',
			[
				'src' => 'https://docs.google.com/document/d/' . rawurlencode( $id ),
				'width' => $width,
				'height' => $height,
				'frameBorder' => 0
			],
			''
		);
	}
}
