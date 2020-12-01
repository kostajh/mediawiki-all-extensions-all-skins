<?php

/**
 * Register the <html> tag in certain namespaces
 *
 * @author Ike Hecht
 */
class NamespaceHTML {

	/**
	 * Where secure and relevant, adds support for <html> tag
	 *
	 * @param Parser $parser
	 * @return boolean
	 */
	static function addNamespaceHTML( Parser &$parser ) {
		$parser->setHook( 'html', array( __CLASS__, 'html' ) );
		return true;
	}

	/**
	 * Pass everything to core parser tag hook function for 'html'.
	 * Enabled when $wgRawHtml is disabled.
	 *
	 * This is potentially unsafe and should be used only in protected
	 * namespaces, as the contents are emitted as raw HTML.
	 *
	 * Uses undocumented extended tag hook return values, introduced in r61913.
	 *
	 * @global array $wgRawHtmlNamespaces Namespaces where raw HTML should be allowed
	 * @param string $content
	 * @param array $attributes
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string Raw or escaped HTML
	 */
	static function html( $content, array $attributes, Parser $parser, PPFrame $frame ) {
		global $wgRawHtmlNamespaces;

		$title = $parser->getTitle();
		if ( !isset( $title ) ) {
			return htmlspecialchars( Html::rawElement( 'html', $attributes, $content ) );
		}
		$titleNamespace = $title->getNamespace();
		$frameNamespace = $frame->getTitle()->getNamespace();

		# Ideally, this check should take place in function 'addNamespaceHTML'
		# but for some reason, $parser->getTitle() often returns null there.
		if ( (bool) array_intersect( $wgRawHtmlNamespaces, array( $titleNamespace, $frameNamespace ) ) ) {
			// copied from CoreTagHooks::html
			return array( $content, 'markerType' => 'nowiki' );
		}

		# raw HTML not allowed here so send out escaped text
		return htmlspecialchars( Html::rawElement( 'html', $attributes, $content ) );
	}
}
