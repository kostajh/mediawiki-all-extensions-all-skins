<?php

/**
 * Hooks for PaginateText extension
 *
 * @file
 * @ingroup Extensions
 */
class PaginateTextHooks {
	/**
	 * Flag to indicate that this page needs pagination and should have the appropriate modules loaded
	 *
	 * @var bool
	 */
	public static $loadJS = false;

	/**
	 * Add the tag hook
	 *
	 * @param Parser &$parser
	 * @return bool
	 */
	public static function addTag( Parser &$parser ) {
		$parser->setHook( 'paginatetext', 'PaginateTextHooks::getTagOutput' );

		return true;
	}

	/**
	 * Substitute the tag with the input plus the controls
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string HTML
	 */
	public static function getTagOutput( $input, array $args, Parser $parser, PPFrame $frame ) {
		if ( !isset( $args['height'] ) ) {
			$args['height'] = '600px';
		}

		self::$loadJS = true;

		$options = [ 'id' => 'paginate-text', 'style' => "height: {$args['height']};" ];
		$output = $parser->recursiveTagParse( $input, $frame );
		return Html::rawElement( 'div', $options, $output ) . self::getControls();
	}

	/**
	 * Add the JS & CSS modules
	 *
	 * @param OutputPage &$out
	 * @param Skin &$skin Unused
	 * @return bool
	 */
	public static function addModules( OutputPage &$out, Skin &$skin ) {
		if ( self::$loadJS ) {
			$out->addModules( 'ext.PaginateText' );
		}

		return true;
	}

	/**
	 * Get the paginate controls
	 *
	 * @return string HTML
	 */
	public static function getControls() {
		$output = Html::openElement( 'div', [ 'class' => 'controls' ] );
		$output .= Html::rawElement( 'a',
				[ 'class' => [ 'tp-control-arrow-left', 'unactive' ] ], '<' );
		$output .= Html::rawElement( 'a', [ 'class' => 'tp-control-arrow-right' ], '>' );
		$output .= Html::rawElement( 'ul', [ 'class' => 'tp-pager' ] );
		$output .= Html::closeElement( 'div' );
		return $output;
	}
}
