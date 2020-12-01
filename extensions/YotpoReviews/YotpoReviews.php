<?php

class YotpoReviews {

	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setHook( 'yotporeviews', 'YotpoReviews::render' );
		$parser->setHook( 'yotpo-reviews', 'YotpoReviews::render' ); // Backwards compatibility
		return true;
	}

	public static function onSkinAfterContent( &$data, Skin $skin ) {
		global $wgYotpoAppKey;
		$data .= '<script>(function e(){var e=document.createElement("script");e.type="text/javascript",e.async=true,e.src="//staticw2.yotpo.com/' . $wgYotpoAppKey . '/widget.js";var t=document.getElementsByTagName("script")[0];t.parentNode.insertBefore(e,t)})();</script>';
		return true;
	}

	public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
		$width = '100%'; // Default
		if ( array_key_exists( 'width', $args ) ) {
			$width = (int)$args['width'] . 'px';
		}

		$height = '100%'; // Default
		if ( array_key_exists( 'height', $args ) ) {
			$height = (int)$args['height'] . 'px';
		}

		$align = 'center'; // Default
		if ( array_key_exists( 'align', $args ) ) {
			$align = $args['align'];
		}
		if ( $align === 'left' ) {
			$margin = '0 auto 0 0';
		}
		if ( $align === 'right' ) {
			$margin = '0 0 0 auto';
		}
		if ( $align === 'center' ) {
			$margin = '0 auto 0 auto';
		}

		$float = 'none'; // Default
		if ( array_key_exists( 'float', $args ) && preg_match( '/^[a-zA-Z]+$/', $args['float'] ) ) {
			$float = $args['float'];
		}

		$title = $parser->getTitle();
		$data_product_id = $title->getDBKey();
		$data_name = $title->getText();
		$data_url = $title->getFullURL();
		$data_image_url = '';
		$data_description = '';

		return Html::element(
			'div',
			[
				'style' => 'float:' . $float . '; margin:' . $margin . '; width:' . $width . '; height:' . $height . ';',
				'class' => "yotpo yotpo-main-widget",
				'data-product-id' => $data_product_id,
				'data-name' => $data_name,
				'data-url' => $data_url,
				'data-image-url' => $data_image_url,
				'data-description' => $data_description
			],
			''
		);
	}
}
