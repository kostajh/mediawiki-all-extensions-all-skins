<?php

class BlueSpiceSubPageTree extends BsExtensionMW {
	// Register any render callbacks with the parser
	public static function onParserSetup( Parser $parser ) {
		// When the parser sees the <pagetree> tag, it executes BlueSpiceSubPageTree::renderPageTree
		$parser->setHook( 'pagetree', 'BlueSpiceSubPageTree::renderPageTree' );
		$parser->setHook( 'subpagetree', 'BlueSpiceSubPageTree::renderPageTree' );

		return true;
	}

	/**
	 *
	 * @global string $bsgSubPageTreeRoot
	 * @param sting $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return bool
	 */
	public static function renderPageTree( $input, array $args, Parser $parser, PPFrame $frame ) {
		$parser->getOutput()->addModules( "ext.bluespice.subpagetree" );

		$sTreeRoot = "";
		if ( !empty( $args["root"] ) ) {
			$sTreeRoot = $args["root"];
		} else {
			global $bsgSubPageTreeRoot;
			$sTreeRoot = $bsgSubPageTreeRoot;
		}

		if ( strpos( $sTreeRoot, ":" ) === false ) {
			$sTreeRoot = ":" . $sTreeRoot;
		}

		return Html::element(
			'div',
			[
				'class' => 'bs-subpagetree',
				'data-root' => $sTreeRoot
			],
			''
		);
	}
}
