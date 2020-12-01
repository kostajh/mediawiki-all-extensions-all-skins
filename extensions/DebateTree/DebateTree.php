<?php

class DebateTree {

	/**
	 * @param OutputPage &$out
	 * @param Skin &$skin
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModuleStyles( 'ext.DebateTree.css' );
		$out->addModules( 'ext.DebateTree' );
	}

	/**
	 * @param Parser &$parser
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setHook( 'debatetree', 'DebateTree::render' );
	}

	/**
	 * @param mixed $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFRame $frame
	 * @return string
	 */
	public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
		return Html::rawElement(
			'div', [
				'class' => 'debatetree'
			],
			$input
		);
	}
}
