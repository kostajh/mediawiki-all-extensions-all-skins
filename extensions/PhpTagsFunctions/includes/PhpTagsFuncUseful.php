<?php
namespace PhpTagsObjects;

/**
 *
 *
 * @file PhpTagsFuncUseful.php
 * @ingroup PhpTagsFunctions
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 */
class PhpTagsFuncUseful extends \PhpTags\GenericObject {

	public static function getConstantValue( $constantName ) {
		switch ( $constantName ) {
			case 'UUID':
				return self::f_uuid_create();
			case 'PHPTAGS_FUNCTIONS_VERSION':
				return \ExtensionRegistry::getInstance()->getAllThings()['PhpTags Functions']['version'];
		}
		parent::getConstantValue( $constantName );
	}

	public static function f_uuid_create() {
		return \UIDGenerator::newUUIDv4();
	}

	public static function f_mw_json_decode( $value ) {
		return \FormatJson::decode( $value, true );
	}

	public static function f_mw_json_encode( $value ) {
		return \FormatJson::encode( $value, false, \FormatJson::UTF8_OK );
	}

	public static function f_get_arg( $index, $default = null ) {
		$args = self::f_get_args();
		if ( isset( $args[$index] ) || array_key_exists( $index, $args ) ) {
			return $args[$index];
		}
		return $default;
	}

	public static function f_get_args() {
		$variables = \PhpTags\Runtime::getVariables();
		$argv = $variables['argv'];
		unset( $argv[0] );
		return $argv;
	}

	public static function f_num_args() {
		$variables = \PhpTags\Runtime::getVariables();
		return $variables['argc'] - 1;
	}

	public static function f_transclude( $template, $parameters = [], $default = null ) {
		$parser = \PhpTags\Renderer::getParser();
		$frame = \PhpTags\Renderer::getFrame();

		foreach ( $parameters as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$parameters[$key] = (string)$value;
				continue;
			}
			throw new \PhpTags\HookException( 'Expects parameter 2 to be array that contains only string values' );
		}

		if ( $frame->depth >= $parser->mOptions->getMaxTemplateDepth() ) {
			throw new \PhpTags\HookException( 'Template depth limit exceeded' );
		}

		if ( $template === false || $template === true || $template === null ) {
			$title = false;
		} elseif ( $template instanceof \PhpTags\GenericObject ) {
			$title = $template->value;
			if ( false === $title instanceof \Title ) {
				if ( $template->getName() !== 'WTitle' ) {
					throw new \PhpTags\PhpTagsException( \PhpTags\PhpTagsException::FATAL_OBJECT_COULD_NOT_BE_CONVERTED, [ $template->getName(), 'WTitle' ] );
				}
				throw new \PhpTags\HookException( 'Wrong WTitle object', \PhpTags\HookException::EXCEPTION_FATAL );
			}
		} elseif ( is_string( $template ) ) {
			$title = \Title::newFromText( $template, NS_TEMPLATE );
		} else {
			throw new \PhpTags\PhpTagsException( \PhpTags\PhpTagsException::WARNING_EXPECTS_PARAMETER, [ 1, 'string or WTitle', gettype( $template ) ] );
		}

		if ( $title ) {
			if ( \MWNamespace::isNonincludable( $title->getNamespace() ) ) {
				throw new \PhpTags\HookException( 'Template inclusion denied' );
			}
			if ( $title->isExternal() ) {
				throw new \PhpTags\HookException( 'Cannot transclude external template' );
			}
			if ( $title->isSpecialPage() ) {
				throw new \PhpTags\HookException( 'Cannot transclude special page' );
			}

			list( $dom, $finalTitle ) = $parser->getTemplateDom( $title );
			if ( !$frame->loopCheck( $finalTitle ) ) {
				throw new \PhpTags\HookException( 'Template loop detected' );
			}
		}

		if ( !$title || !$dom ) {
			if ( $default === null ) {
				$titleName = $title ? '"' . $title->getPrefixedText() . '"' : 'NULL';
				throw new \PhpTags\HookException( "Template $titleName does not exist" );
			}
			$dom = $parser->getPreprocessor()->preprocessToObj(
					str_replace( [ "\r\n", "\r" ], "\n", $default ),
					$frame->depth ? \Parser::PTD_FOR_INCLUSION : 0
				);
			$newFrame = $parser->getPreprocessor()->newFrame();
		} else {
			$fargs = $parser->getPreprocessor()->newPartNodeArray( $parameters );
			$newFrame = $frame->newChild( $fargs, $finalTitle, -1 );
		}

		return $newFrame->expand( $dom );
	}

}
