<?php

class TextScroller {

	public static function setParserFunction( &$parser ) {
		# Setup parser hook
		$parser->setFunctionHook( 'txtscrl', 'TextScroller::parserFunction' );
	}

	public static function parserFunction( $parser, $arrowText = '', $grayText = '', $scrollText = '' ) {
		$scrollText = self::prepareText( $scrollText );
		$grayText = self::prepareText( $grayText );
		$arrowText = self::prepareText( $arrowText );

		$parser->getOutput()->addModules( 'ext.textscroller.scripts' );
		$parser->getOutput()->addModuleStyles( 'ext.textscroller.styles' );

		if ( !empty( $arrowText ) ) {
			$outerContent = "\t\t<div class=\"arrow_right\"></div>\n\t\t<div class=\"arrow_text\">{$arrowText}</div>\n";
		} else {
			$outerContent = "\n\t\t&nbsp;\n";
		}

		$id = 'scrl-' . hash( 'md5', $scrollText . mt_rand( 1, 1000 ) );
		$html = <<<TEXTSCROLLER
<div class="textscroller_outer" id="{$id}">
	<div class="textscroller_inner">
		<div class="greytext">{$grayText}</div>
		<div class="scrolltext">{$scrollText}</div>
	</div>
	<div class="arrow_outer">
		{$outerContent}
	</div>
</div>
TEXTSCROLLER;
		$html = preg_replace( "@\n@", '', $html );

		return $parser->insertStripItem( $html );
	}

	private static function prepareText( $text ) {
		$text = trim( $text );
		// Replace line breaks with a token to later be replaced in the scroller JavaScript
		$text = preg_replace( "@[\r\n]@", '@br@', $text );
		return $text;
	}
}
