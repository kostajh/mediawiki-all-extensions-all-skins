<?php

/**
 * Hooks for FlickrAPI extension
 *
 * @file
 * @ingroup Extensions
 */
class FlickrAPIHooks {

	/**
	 * Hooked to ParserFirstCallInit.
	 * @link https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 * @param Parser &$parser The parser.
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setHook( 'flickr', self::class . '::flickrAPITag' );
		return true;
	}

	/**
	 * Get output for the <flickr> tag
	 *
	 * @param string $optionsString Tag content.
	 * @param array $args Tag attributes.
	 * @param Parser $parser The parser.
	 * @return string
	 */
	public static function flickrAPITag( $optionsString, array $args, Parser $parser ) {
		try {
			$output = self::getOutput( $optionsString, $parser );
		} catch ( MWException $e ) {
			return self::handleError( $e );
		}
		return $output;
	}

	/**
	 * Translate the options string from the user into a useful array
	 *
	 * @param string $optionsString
	 * @return array
	 */
	private static function extractOptions( $optionsString ) {
		$parts = StringUtils::explode( '|', $optionsString );

		$options = [ 'id' => $parts->current() ];
		$parts->next();

		$validSizes = self::getValidSizes();
		$validTypes = [ 'thumb', 'frame', 'frameless' ];
		$validAligns = [ 'right', 'left', 'center', 'none' ];
		# Okay now deal with parameters
		/** @todo Copied from Flickr extension. Refactor. */
		while ( $parts->valid() ) {
			$currentPart = strtolower( trim( htmlspecialchars( $parts->current() ) ) );
			if ( empty( $options['type'] ) && in_array( $currentPart, $validTypes ) ) {
				$options['type'] = $currentPart;
			} elseif ( empty( $options['location'] ) && in_array( $currentPart, $validAligns ) ) {
				$options['location'] = $currentPart;
			} elseif ( empty( $options['size'] ) && array_key_exists( $currentPart, $validSizes ) ) {
				$options['size'] = $currentPart;
			} elseif ( empty( $options['caption'] ) ) {
				# Allow uppercase in caption
				$options['caption'] = trim( htmlspecialchars( $parts->current() ) );
			} else {
				$options['caption'] .= '|' . trim( htmlspecialchars( $parts->current() ) );
			}
			$parts->next();
		}

		return $options;
	}

	/**
	 * Get the valid sizes available to the user. Some of these may not actually be available
	 * from the API for this image.
	 *
	 * @return array
	 */
	private static function getValidSizes() {
		return [
			's' => 'Square',
			't' => 'Thumbnail',
			'm' => 'Small',
			'-' => 'Medium',
			'b' => 'Large',
		];
	}

	/**
	 * Apply defaults for any parameter that has not been set within the <flickr> tag
	 *
	 * @global array $wgFlickrAPIDefaults
	 * @param array &$options
	 * @param array $info
	 */
	private static function applyDefaults( array &$options, array $info ) {
		global $wgFlickrAPIDefaults;

		if ( empty( $options['type'] ) ) {
			$options['type'] = $wgFlickrAPIDefaults['type'];
		}
		if ( empty( $options['location'] ) ) {
			$options['location'] = $wgFlickrAPIDefaults['location'];
		}
		if ( empty( $options['size'] ) ) {
			$options['size'] = $wgFlickrAPIDefaults['size'];
		}
		if ( empty( $options['caption'] ) ) {
			$options['caption'] = $info['photo']['title']['_content'];
		}
	}

	/**
	 * Send out an error message
	 *
	 * @param MWException $e
	 * @return string HTML
	 */
	private static function handleError( MWException $e ) {
		return Html::element( 'strong', [ 'class' => [ 'error', 'flickrapi-error' ] ],
				$e->getMessage() );
	}

	/**
	 * Get an image link for this user input
	 *
	 * @global string $wgFlickrAPIKey
	 * @global string $wgFlickrAPISecret
	 * @global boolean $wgUseFileCache
	 * @global string $wgFileCacheDirectory
	 * @param string $optionsString
	 * @param Parser $parser
	 * @return string HTML
	 * @throws MWException
	 */
	private static function getOutput( $optionsString, Parser $parser ) {
		global $wgFlickrAPIKey, $wgFlickrAPISecret, $wgUseFileCache, $wgFileCacheDirectory;

		$options = self::extractOptions( $optionsString );

		/** @todo i18n these errors? */
		if ( $wgFlickrAPIKey == '' ) {
			throw new MWException(
			'Flickr Error ( No API key ): You must set $wgFlickrAPIKey!' );
		}
		if ( !$options['id'] ) {
			throw new MWException( 'Flickr Error ( No ID ): Enter at least a PhotoID' );
		}
		if ( !is_numeric( $options['id'] ) ) {
			throw new MWException( 'Flickr Error ( Not a valid ID ): PhotoID not numeric' );
		}

		$phpFlickr = new phpFlickr( $wgFlickrAPIKey, $wgFlickrAPISecret );

		// Decide which cache to use
		if ( $wgUseFileCache ) {
			$phpFlickr->enableCache( 'fs', $wgFileCacheDirectory );
		} else {
			$phpFlickr->enableCache( 'custom',
				[ 'FlickrAPICache::getCache', 'FlickrAPICache::setCache' ] );
		}

		$info = $phpFlickr->photos_getInfo( $options['id'] );
		$flickrSizes = $phpFlickr->photos_getSizes( $options['id'] );
		if ( !$info || !$flickrSizes ) {
			throw new MWException( 'Flickr Error ( Photo not found ): PhotoID ' . $options['id'] );
		}

		self::applyDefaults( $options, $info );

		$linkUrl = $info['photo']['urls']['url']['0']['_content'];

		$frameParams = [
			'align' => $options['location'],
			'alt' => $options['caption'],
			'caption' => $options['caption'],
			'title' => $options['caption'],
			'link-url' => $linkUrl
		];

		if ( $options['type'] == 'thumb' ) {
			$frameParams['thumbnail'] = true;
		} elseif ( $options['type'] == 'frame' ) {
			$frameParams['framed'] = true;
		}

		$validSizes = self::getValidSizes();
		$handlerParams = [];
		foreach ( $flickrSizes as $flickrSize ) {
			if ( $flickrSize['label'] === $validSizes[$options['size']] ) {
				$handlerParams['width'] = $flickrSize['width'];
				$url = $flickrSize['source'];
			}
		}

		if ( !isset( $url ) ) {
			throw new MWException( 'Flickr Error ( Not a valid size ): Not found in this size' );
		}

		$handlerParams['custom-url-link'] = $linkUrl;

		$imageLink = FlickrAPIUtils::makeImageLink( $parser, $url, $frameParams, $handlerParams );

		return Html::rawElement( 'div', [ 'class' => 'flickrapi' ], $imageLink );
	}
}
