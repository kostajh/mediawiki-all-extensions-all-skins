<?php
/**
 * @author Shoichi Chou <shoichi.chou@gmail.com>
 * @author David Sn <divad.nnamtdeis@gmail.com>
 */

class IDS {
	/**
	 * Register the parser function
	 * @param Parser $parser
	 */
	public static function onParserInit( Parser $parser ) {
		$parser->setHook( 'ids', [ __CLASS__, 'idsRender' ] );
	}

	/**
	 * This method handles IDS tags.
	 *
	 * <ids>⿰電心</ids> ->
	 * 	<img align=middle class="ids-char" src="https://tools.wmflabs.org/idsgen/⿰電心.svg?字體=宋體" ...>
	 *
	 * <ids font=楷體粗體>⿰電心</ids> ->
	 * 	<img align=middle class="ids-char" src="https://tools.wmflabs.org/idsgen/⿰電心.svg?字體=楷體粗體" ...>
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Tag_extensions
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public static function idsRender( $input, array $args, Parser $parser, PPFrame $frame ) {
		// Get configured web service endpoint (T1540435)
		$config = RequestContext::getMain()->getConfig();
		$endpoint = $config->get( 'IdsEndpoint' );

		// Support for Simplified "體" (font)
		$font = isset( $args['font'] ) ? strtr( $args['font'], '体', '體' ) : '宋體';
		$src = $endpoint . rawurlencode( $input )
			. '.svg?字體=' . rawurlencode( $font );

		return Html::element( 'img', [
			'align' => 'middle',
			'class' => 'ids-char',  // helps with custom styling
			'alt' => $input,
			'src' => $src,
			'onerror' => 'this.src = this.src.replace(".svg?字體=", ".png?字體=")',  // png fallback
			'style' => 'height: 1em; width: 1em; vertical-align: middle; margin: 0.4em 0px 0.7em;'
		] );
	}
}
