<?php

/**
 * Utilities, based on core MediaWiki code from version 1.24.2
 *
 * @author Ike Hecht
 */
class FlickrAPIUtils {

	/**
	 * Scaled down version of Linker::makeImageLink. Not all options are implemented yet.
	 *
	 * Given parameters derived from [[Image:Foo|options...]], generate the
	 * HTML that that syntax inserts in the page.
	 *
	 * @param Parser $parser The parser.
	 * @param string $url URL of the image being displayed
	 * @param array $frameParams Associative array of parameters external to the media handler.
	 *     Boolean parameters are indicated by presence or absence, the value is arbitrary and
	 *     will often be false.
	 *          thumbnail       If present, downscale and frame
	 *          manualthumb     Image name to use as a thumbnail, instead of automatic scaling
	 *          framed          Shows image in original size in a frame
	 *          frameless       Downscale but don't frame
	 *          upright         If present, tweak default sizes for portrait orientation
	 *          upright_factor  Fudge factor for "upright" tweak (default 0.75)
	 *          border          If present, show a border around the image
	 *          align           Horizontal alignment (left, right, center, none)
	 *          valign          Vertical alignment (baseline, sub, super, top, text-top, middle,
	 *                          bottom, text-bottom)
	 *          alt             Alternate text for image (i.e. alt attribute). Plain text.
	 *          class           HTML for image classes. Plain text.
	 *          caption         HTML for image caption.
	 *          link-url        URL to link to
	 *          link-target     Value for the target attribute, only with link-url
	 *          no-link         Boolean, suppress description link
	 *
	 * @param array $handlerParams Associative array of media handler parameters, to be passed
	 *       to transform(). Typical keys are "width" and "page".
	 * @param string|bool $time Timestamp of the file, set as false for current
	 * @param string $query Query params for desc url
	 * @since 1.20
	 * @return string HTML for an image, with links, wrappers, etc.
	 */
	public static function makeImageLink( Parser $parser, $url, $frameParams = [],
		$handlerParams = [], $time = false, $query = "" ) {
		// Shortcuts
		$fp = & $frameParams;
		$hp = & $handlerParams;

		// Clean up parameters
		if ( !isset( $fp['align'] ) ) {
			$fp['align'] = '';
		}
		if ( !isset( $fp['alt'] ) ) {
			$fp['alt'] = '';
		}
		if ( !isset( $fp['title'] ) ) {
			$fp['title'] = '';
		}
		if ( !isset( $fp['class'] ) ) {
			$fp['class'] = '';
		}

		$prefix = $postfix = '';

		if ( 'center' == $fp['align'] ) {
			$prefix = '<div class="center">';
			$postfix = '</div>';
			$fp['align'] = 'none';
		}

		if ( isset( $fp['thumbnail'] ) || isset( $fp['manualthumb'] ) || isset( $fp['framed'] ) ) {
			/*
			 * Create a thumbnail. Alignment depends on the writing direction of
			 * the page content language (right-aligned for LTR languages,
			 * left-aligned for RTL languages).
			 *
			 * If a thumbnail width has not been provided, it is set
			 * to the default user option as specified in Language*.php
			 */
			if ( $fp['align'] == '' ) {
				$fp['align'] = $parser->getTargetLanguage()->alignEnd();
			}
			return $prefix . self::makeThumbLink2( $url, $fp, $hp, $time, $query ) . $postfix;
		}

		$params = [
			'alt' => $fp['alt'],
			'title' => $fp['title'],
			'valign' => isset( $fp['valign'] ) ? $fp['valign'] : false,
			'img-class' => $fp['class'] ];
		if ( isset( $fp['border'] ) ) {
			$params['img-class'] .= ( $params['img-class'] !== '' ? ' ' : '' ) . 'thumbborder';
		}
		$imageLinkparams = self::getImageLinkMTOParams( $fp, $query, $parser ) + $params;

		$s = self::thumbToHtml( $imageLinkparams, $url );

		if ( $fp['align'] != '' ) {
			$s = "<div class=\"float{$fp['align']}\">{$s}</div>";
		}
		return str_replace( "\n", ' ', $prefix . $s . $postfix );
	}

	/**
	 * Scaled down & modified version of Linker::makeThumbLink2. Not all options are implemented yet.
	 *
	 * @param string $url Image URL.
	 * @param array $frameParams The frame parameters: align, alt, title, caption.
	 * @param array $handlerParams The handler parameters: width, custom-url-link.
	 * @param bool $time Not used.
	 * @param string $query An optional query string to add to description page links.
	 * @return string
	 */
	public static function makeThumbLink2( $url, $frameParams = [], $handlerParams = [],
		$time = false, $query = ""
	) {
		# Shortcuts
		$fp = & $frameParams;
		$hp = & $handlerParams;

		if ( !isset( $fp['align'] ) ) {
			$fp['align'] = 'right';
		}
		if ( !isset( $fp['alt'] ) ) {
			$fp['alt'] = '';
		}
		if ( !isset( $fp['title'] ) ) {
			$fp['title'] = '';
		}
		if ( !isset( $fp['caption'] ) ) {
			$fp['caption'] = '';
		}

		if ( empty( $hp['width'] ) ) {
			// Reduce width for upright images when parameter 'upright' is used
			$hp['width'] = isset( $fp['upright'] ) ? 130 : 180;
		}

		$outerWidth = $hp['width'] + 2;

		$s = "<div class=\"thumb t{$fp['align']}\">"
			. "<div class=\"thumbinner\" style=\"width:{$outerWidth}px;\">";

		$params = [
			'alt' => $fp['alt'],
			'title' => $fp['title'],
			'img-class' => ( isset( $fp['class'] ) && $fp['class'] !== '' ? $fp['class'] . ' ' : '' )
			. 'thumbimage'
		];
		$imageLinkparams = self::getImageLinkMTOParams( $fp, $query ) + $params;
		$s .= self::thumbToHtml( $imageLinkparams, $url );

		if ( isset( $fp['framed'] ) ) {
			$zoomIcon = "";
		} else {
			$zoomIcon = Html::rawElement( 'div', [ 'class' => 'magnify' ],
					Html::rawElement( 'a',
						[
						'href' => $hp['custom-url-link'],
						'title' => wfMessage( 'thumbnail-more' )->text()
						], "" ) );
		}
		$s .= '  <div class="thumbcaption">' . $zoomIcon . $fp['caption'] . "</div></div></div>";
		return str_replace( "\n", ' ', $s );
	}

	/**
	 * Scaled down version of Linker::getImageLinkMTOParams
	 *
	 * Get the link parameters for MediaTransformOutput::toHtml() from given
	 * frame parameters supplied by the Parser.
	 * @param array $frameParams The frame parameters
	 * @param string $query An optional query string to add to description page links
	 * @param Parser|null $parser
	 * @return array
	 */
	private static function getImageLinkMTOParams( $frameParams, $query = '', $parser = null ) {
		$mtoParams = [];
		if ( isset( $frameParams['link-url'] ) && $frameParams['link-url'] !== '' ) {
			$mtoParams['custom-url-link'] = $frameParams['link-url'];
			if ( isset( $frameParams['link-target'] ) ) {
				$mtoParams['custom-target-link'] = $frameParams['link-target'];
			}
			if ( $parser ) {
				$extLinkAttrs = $parser->getExternalLinkAttribs( $frameParams['link-url'] );
				foreach ( $extLinkAttrs as $name => $val ) {
					// Currently could include 'rel' and 'target'
					$mtoParams['parser-extlink-' . $name] = $val;
				}
			}
		} elseif ( isset( $frameParams['link-title'] ) && $frameParams['link-title'] !== '' ) {
			$mtoParams['custom-title-link'] = self::normaliseSpecialPage( $frameParams['link-title'] );
		} elseif ( !empty( $frameParams['no-link'] ) ) {
			// No link
		} else {
			$mtoParams['desc-link'] = true;
			$mtoParams['desc-query'] = $query;
		}
		return $mtoParams;
	}

	/**
	 * Scaled down version of ThumbnailImage::toHtml. Not all options are implemented yet.
	 *
	 * Return HTML <img ... /> tag for the thumbnail, will include
	 * width and height attributes and a blank alt text (as required).
	 *
	 * @param array $options Associative array of options. Boolean options
	 *     should be indicated with a value of true for true, and false or
	 *     absent for false.
	 *
	 *     alt          HTML alt attribute
	 *     title        HTML title attribute
	 *     desc-link    Boolean, show a description link
	 *     file-link    Boolean, show a file download link
	 *     valign       vertical-align property, if the output is an inline element
	 *     img-class    Class applied to the \<img\> tag, if there is such a tag
	 *     desc-query   String, description link query params
	 *     override-width     Override width attribute. Should generally not set
	 *     override-height    Override height attribute. Should generally not set
	 *     no-dimensions      Boolean, skip width and height attributes (useful if
	 *                        set in CSS)
	 *     custom-url-link    Custom URL to link to
	 *     custom target-link Value of the target attribute, for custom-target-link
	 *     parser-extlink-*   Attributes added by parser for external links:
	 *          parser-extlink-rel: add rel="nofollow"
	 *          parser-extlink-target: link target, but overridden by custom-target-link
	 *
	 * For images, desc-link and file-link are implemented as a click-through. For
	 * sounds and videos, they may be displayed in other ways.
	 * @param string $url The image URL.
	 * @return string
	 */
	public static function thumbToHtml( $options = [], $url ) {
		$alt = empty( $options['alt'] ) ? '' : $options['alt'];

		$query = empty( $options['desc-query'] ) ? '' : $options['desc-query'];

		if ( !empty( $options['custom-url-link'] ) ) {
			$linkAttribs = [ 'href' => $options['custom-url-link'] ];
			if ( !empty( $options['title'] ) ) {
				$linkAttribs['title'] = $options['title'];
			}
			if ( !empty( $options['custom-target-link'] ) ) {
				$linkAttribs['target'] = $options['custom-target-link'];
			} elseif ( !empty( $options['parser-extlink-target'] ) ) {
				$linkAttribs['target'] = $options['parser-extlink-target'];
			}
			if ( !empty( $options['parser-extlink-rel'] ) ) {
				$linkAttribs['rel'] = $options['parser-extlink-rel'];
			}
		} elseif ( !empty( $options['custom-title-link'] ) ) {
			// Do nothing - Titles not valid
		} elseif ( !empty( $options['desc-link'] ) ) {
			// Not implemented
		} elseif ( !empty( $options['file-link'] ) ) {
			// Not implemented
		} else {
			$linkAttribs = false;
		}

		$attribs = [
			'alt' => $alt,
			'src' => $url,
		];

		if ( !empty( $options['valign'] ) ) {
			$attribs['style'] = "vertical-align: {$options['valign']}";
		}
		if ( !empty( $options['img-class'] ) ) {
			$attribs['class'] = $options['img-class'];
		}
		if ( isset( $options['override-height'] ) ) {
			$attribs['height'] = $options['override-height'];
		}
		if ( isset( $options['override-width'] ) ) {
			$attribs['width'] = $options['override-width'];
		}

		// Copied from linkWrap function
		$contents = Xml::element( 'img', $attribs );
		if ( $linkAttribs ) {
			return Xml::tags( 'a', $linkAttribs, $contents );
		} else {
			return $contents;
		}
	}
}
