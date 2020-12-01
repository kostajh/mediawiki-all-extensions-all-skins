<?php
namespace PhpTagsObjects;

use File;
use MediaWiki\MediaWikiServices;
use PhpTags\GenericWidget;
use PhpTags\HookException;
use PhpTags\Renderer;
use PhpTags\Runtime;
use Title;

/**
 * Description of WidgetImage
 *
 * @author pastakhov
 */
class WidgetImage extends GenericWidget {

	protected $element = 'img';
	protected static $trueCase = [
		'imagewidth' => 'imageWidth',
		'imageheight' => 'imageHeight',
	];

	/**
	 *
	 * @param string $value
	 * @param array $properties
	 * @return bool
	 */
	public function m___construct( $value = null, $properties = null ) {
		$this->b_file( $value );
		return parent::m___construct( $properties );
	}

	public function getCssClassName() {}

	public function toString() {
		$info = [];
		$src = $this->getSrc( $info );
		if ( $src ) {
			$genAttribs = &$this->value[self::GENERAL_ATTRIBS];
			$genAttribs['src'] = $src;
			if ( !isset( $genAttribs['width'] ) ) {
				$genAttribs['width'] = $info['width'] . 'px';
			}
			if ( !isset( $genAttribs['height'] ) ) {
				$genAttribs['height'] = $info['height'] . 'px';
			}
		}
		// @todo $wgResponsiveImages
		return parent::toString();
	}

	public function __call( $name, $arguments ) {
		list ( $callType, $tmp ) = explode( '_', $name, 2 );
		$subname = strtolower( $tmp );

		switch ( $subname ) {
			case 'alt':
			case 'height':
			case 'width':
				if ( $callType === 'p' ) { // get property
					return $this->getTagAttribute( $subname );
				} elseif ( $callType === 'b' ) { // set property
					return $this->setTagAttribute( $subname, $arguments[0] );
				}
				break;
			case 'imagewidth':
			case 'imageheight':
				$props =& $this->value[self::PRIVATE_PROP];
				$property = self::$trueCase[$subname];
				if ( $callType === 'p' ) { // get property
					return isset( $props[$property] ) ? $props[$property] : false;
				} elseif ( $callType === 'b' ) { // set property
					if ( $arguments[0] === null ) {
						unset( $props[$property] );
					} else {
						$props[$property] = $arguments[0];
					}
					return null;
				}

		}
		return parent::__call( $name, $arguments );
	}

	public function p_file() {
		return isset( $this->value[self::PRIVATE_PROP]['file'] ) ? $this->value[self::PRIVATE_PROP]['file'] : null;
	}

	public function b_file( $value ) {
		$prop =& $this->value[self::PRIVATE_PROP];

		if ( !isset( $prop['file'] ) || $prop['file'] !== $value ) {
			if ( $value === null ) {
				unset( $prop['file'] );
			} else {
				$prop['file'] = $value;
			}

			if ( isset( $prop['objects'] ) ) {
				$prop['objects'] = [];
			}
		}

		return $this;
	}

	public function p_url() {
		$info = [];
		$url = $this->getSrc( $info );
		return $url ?: null;
	}

	public function p_srcWidth() {
		$file = $this->getFileObj();
		return $file ? $file->getWidth() : false;
	}

	public function p_srcHeight() {
		$file = $this->getFileObj();
		return $file ? $file->getHeight() : false;
	}

	/**
	 * @return Title|null
	 */
	public function getImageTitle() {
		$imageFile = $this->value[self::PRIVATE_PROP]['file'] ?? null;
		if ( $imageFile ) {
			$imageTitle = Title::newFromText( $imageFile, NS_FILE );
			return $imageTitle ?: null;
		}
		return null;
	}

	private function getImageObjects() {
		$imageTitle = $this->getImageTitle();
		if ( !$imageTitle ) {
			return null;
		}

		if ( !isset( $this->value[self::PRIVATE_PROP]['objects'] ) ) {
			$parser = Renderer::getParser();

			if ( version_compare(MW_VERSION, '1.34', '>=' ) ) {
				if ( MediaWikiServices::getInstance()->getBadFileLookup()->isBadFile( $imageTitle->getDBkey(), $parser->getTitle() ) ) {
					Runtime::pushException( new HookException( 'Image `' . $imageTitle->getDBkey() . '` is bad for this title', HookException::EXCEPTION_NOTICE ) );
					return false;
				}
			} elseif ( wfIsBadImage( $imageTitle->getDBkey(), $parser->getTitle() ) ) { // TODO remove deprecated code
				Runtime::pushException( new HookException( 'Image `' . $imageTitle->getDBkey() . '` is bad for this title', HookException::EXCEPTION_NOTICE ) );
				return false;
			}

			// see Parser::makeImage()
			# Give extensions a chance to select the file revision for us
			$options = [];
			$descQuery = false;
			\Hooks::run( 'BeforeParserFetchFileAndTitle', [ $parser, $imageTitle, &$options, &$descQuery ] );

			# Fetch and register the file (file title may be different via hooks)
			/** @var File $file */
			/** @var Title $title */
			list( $file, $title ) = $parser->fetchFileAndTitle( $imageTitle, $options );

			if ( !$file ) {
				$parser->addTrackingCategory( 'broken-file-category' );
				Runtime::pushException( new HookException( 'Image `' . $title->getPrefixedDBkey() . '` not found', HookException::EXCEPTION_NOTICE ) );
				return false;
			}

			// see Linker::makeImageLink2
			if ( !$file->allowInlineDisplay() ) {
				wfDebug( __METHOD__ . ': ' . $title->getPrefixedDBkey() . " does not allow inline display\n" );
				Runtime::pushException( new HookException( 'Image `' . $title->getPrefixedDBkey() . '` does not allow inline display', HookException::EXCEPTION_NOTICE ) );
				return false;
			}

			$handler = $file->getHandler();
			$this->value[self::PRIVATE_PROP]['objects'] = [
				'file' => $file,
				'title' => $title,
				'handler' => $handler,
			];
		}

		return $this->value[self::PRIVATE_PROP]['objects'];
	}

	/**
	 *
	 * @return \File|bool
	 */
	private function getFileObj() {
		$objects = $this->getImageObjects();
		return $objects ? $objects['file'] : false;
	}

	/**
	 *
	 * @return \MediaHandler|bool
	 */
	public function getHandlerObj() {
		$objects = $this->getImageObjects();
		return $objects ? $objects['handler'] : false;
	}

	public function getSrc( &$info ) {
		$file = $this->getFileObj();

		if ( !$file ) {
			return false;
		}

		$srcWidth = $file->getWidth();
		$srcHeight = $file->getHeight();
		$physicalWidth = $this->getPhysicalSize( true );
		$physicalHeight = $this->getPhysicalSize( false );

		if ( !$file->isVectorized() ) { // Don't allow transform thumb to bigger image size
			if ( $srcWidth && $physicalWidth > $srcWidth ) {
				$physicalWidth = $srcWidth;
			}
			if ( $srcHeight && $physicalHeight > $srcHeight ) {
				$physicalHeight = $srcHeight;
			}
		}

		if ( $physicalWidth && $physicalHeight && $srcWidth && $srcHeight ) { // select thumb with bigger size if both sizes provided
			$scaleWidth = $physicalWidth / $srcWidth;
			$scaleHeight = $physicalHeight / $srcHeight;
			if ( $scaleWidth > $scaleHeight ) {
				$physicalHeight = $srcHeight * $scaleWidth;
			} elseif ( $scaleWidth < $scaleHeight ) {
				$physicalWidth = $srcWidth * $scaleHeight;
			}
		}

		if ( !$physicalWidth ) {
			if ( $physicalHeight && $file->isVectorized() ) {
				// If its a vector image, and user only specifies height
				// we don't want it to be limited by its "normal" width.
				global $wgSVGMaxSize;
				$physicalWidth = $wgSVGMaxSize;
			} else {
				$physicalWidth = $srcWidth;
			}
		}

		if ( !$file->mustRender() && $physicalWidth == $srcWidth && (!$physicalHeight || $physicalHeight == $srcHeight) ) {
			$info['width'] = $srcWidth;
			$info['height'] = $srcHeight;
			return $file->getUrl();
		}

		# Create a resized image, without the additional thumbnail features
		$thumb = $file->transform( ['width' => $physicalWidth, 'height' => $physicalHeight] );
		if ( !$thumb ) {
			Runtime::pushException( new HookException( $file->getLastError() ) );
			return false;
		} elseif ( $thumb->isError() ) {
			Runtime::pushException( new HookException( $thumb->toText() ) );
			return false;
		}

		$info['width'] = $thumb->getWidth();
		$info['height'] = $thumb->getHeight();
		return $thumb->getUrl();
	}

	/**
	 * @param $width
	 * @return int|null
	 */
	protected function getPhysicalSize ( $width ) {
		$props =& $this->value[self::PRIVATE_PROP];
		$type1 = $width ? 'imageWidth' : 'imageHeight';
		$type2 = $width ? 'width' : 'height';

		$imageSize = false;
		if ( isset( $props[$type1] ) ) {
			$imageSize = intval( $props[$type1] );
		} else {
			$size = $this->getTagAttribute( $type2 );
			if ( $size && preg_match( '/^[0-9]*\s*(?:px)?\s*$/', $size ) ) {
				$imageSize = intval( $size );
			}
		}

		if ( $imageSize ) {
			$handler = $this->getHandlerObj();
			if ( $handler->validateParam( $type2, $imageSize ) ) {
				return (int)$imageSize;
			}
		}
		return null;
	}

	public function p_exists() {
		$imageTitle = $this->getImageTitle();
		if ( $imageTitle ) {
			return wfFindFile( $imageTitle, [] ) ? true : false;
		}
		return false;
	}

}
