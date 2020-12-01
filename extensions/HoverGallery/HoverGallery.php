<?php

class HoverGallery {

	/**
	 * @param OutputPage &$out
	 * @param Skin &$skin
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModules( 'ext.HoverGallery' );
	}

	/**
	 * @param Parser &$parser
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setHook( 'hovergallery', 'HoverGallery::render' );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 * @suppress SecurityCheck-DoubleEscaped
	 */
	public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
		$maxhoverwidth = 640;
		$maxhoverheight = 640;
		if ( array_key_exists( 'maxhoversize', $args ) ) {
			$maxhoverwidth = $args['maxhoversize'];
			$maxhoverheight = $args['maxhoversize'];
		}
		if ( array_key_exists( 'maxhoverwidth', $args ) ) {
			$maxhoverwidth = $args['maxhoverwidth'];
		}
		if ( array_key_exists( 'maxhoverheight', $args ) ) {
			$maxhoverheight = $args['maxhoverheight'];
		}

		$FILEURLS = [];
		$FILENAMES = array_filter( explode( PHP_EOL, trim( $input ) ) );
		foreach ( $FILENAMES as $filename ) {
			// Remove the options
			$filename = strtok( $filename, '|' );
			// Remove the File prefix
			$filename = substr(
				$filename,
				( $position = strpos( $filename, ':' ) ) === false ? 0 : $position + 1
			);
			// Get the filepath
			$filepath = $parser->recursiveTagParse( '{{filepath:' . $filename . '|nowiki}}' );
			$FILEURLS[] = $filepath;
		}

		$fileUrls = json_encode( $FILEURLS );
		$fileUrls = htmlspecialchars( $fileUrls, ENT_QUOTES );

		$gallery = '<gallery class="hovergallery"
		data-hovergallery-maxhoverwidth="' . $maxhoverwidth . '"
		data-hovergallery-maxhoverheight="' . $maxhoverheight . '"
		data-hovergallery-fileurls="' . $fileUrls . '"';

		// Add all the standard gallery parameters
		if ( array_key_exists( 'mode', $args ) ) {
			$gallery .= ' mode="' . $args['mode'] . '"';
		}
		if ( array_key_exists( 'caption', $args ) ) {
			$gallery .= ' caption="' . $args['caption'] . '"';
		}
		if ( array_key_exists( 'showfilename', $args ) ) {
			$gallery .= ' showfilename="' . $args['showfilename'] . '"';
		}
		if ( array_key_exists( 'showthumbnails', $args ) ) {
			$gallery .= ' showthumbnails="' . $args['showthumbnails'] . '"';
		}
		if ( array_key_exists( 'perrow', $args ) ) {
			$gallery .= ' perrow="' . $args['perrow'] . '"';
		}
		if ( array_key_exists( 'widths', $args ) ) {
			$gallery .= ' widths="' . $args['widths'] . '"';
		}
		if ( array_key_exists( 'heights', $args ) ) {
			$gallery .= ' heights="' . $args['heights'] . '"';
		}

		$gallery .= '>' . $input . '</gallery>';
		return $parser->recursiveTagParse( $gallery );
	}
}
