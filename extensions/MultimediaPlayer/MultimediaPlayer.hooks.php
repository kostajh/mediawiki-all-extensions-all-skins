<?php

/**
 * Hooks for the MultimediaPlayer
 *
 * @author Ike Hecht
 */
class MultimediaPlayerHooks {

	/**
	 * Set hooks
	 *
	 * @param Parser $parser
	 * @return boolean
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setHook( 'multimediacontainer', array( __CLASS__, 'renderContainer' ) );
		$parser->setFunctionHook( 'multimediaitem', array( __CLASS__, 'renderMultimediaItem' ) );

		return true;
	}

	/**
	 * Add a new MultimediaPlayerContainer to the player and return its output
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string HTML for this container
	 */
	public static function renderContainer( $input, array $args, Parser $parser, PPFrame $frame ) {
		$wgMultimediaPlayer = MultimediaPlayer::Singleton();
		$wgMultimediaPlayer->setMultimediaPlayerContainer( new MultimediaPlayerContainer );
		return $wgMultimediaPlayer->getContainerOutput();
	}

	/**
	 * Add a new MultimediaPlayerItem to the player and return its output
	 *
	 * @param Parser $parser
	 * @param string $source Name of the source
	 * @param string $id ID of this item - to replace the $1 in the source
	 * @param string $linkText Text to display in the list
	 * @return string HTML for this item
	 */
	public static function renderMultimediaItem( Parser $parser, $source = '', $id = '', $linkText = '' ) {
		$wgMultimediaPlayer = MultimediaPlayer::Singleton();
		$output = $wgMultimediaPlayer->getMultimediaPlayerItemOutput( new MultimediaPlayerItem( $source,
			$id, $linkText ) );
		return array( $output, 'noparse' => true, 'isHTML' => true );
	}

	/**
	 * Inserts all the JavaScript & CSS
	 *
	 * @global array $wgMultimediaPlayerSources
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		global $wgMultimediaPlayerSources;

		$out->addModules( 'ext.MultimediaPlayer' );

		$wgMultimediaPlayer = MultimediaPlayer::Singleton();
		$wgMultimediaPlayer->setMultimediaPlayerSources( $wgMultimediaPlayerSources );
		$scripts = $wgMultimediaPlayer->getAllScripts();
		foreach ( $scripts as $script ) {
			$out->addScript( Html::inlineScript( $script ) );
		}
		return true;
	}
}
