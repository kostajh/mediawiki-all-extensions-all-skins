<?php

namespace SideBarMenu;

use Exception;
use Html;
use Linker;
use ParamProcessor\Processor;
use Parser;
use PPFrame;
use SideBarMenu\SubPage\SubPageRenderer;

class Hooks {

	/**
	 * @param Parser $parser
	 * @return bool
	 */
	public static function init( Parser &$parser ) {
		$parser->setHook( 'sidebarmenu', 'SideBarMenu\Hooks::renderSideBarMenuFromTag' );
		return true;
	}

	public static function renderSideBarMenuFromTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		try {
			$parser->getOutput()->addModules( 'ext.sidebarmenu.core' );

			if ( strpos( $input, '#subpage ' ) !== false ) {
				//subpages handling
				$parser->getOutput()->updateCacheExpiry( 0 );
				SubPageRenderer::renderSubPages( $input,$parser );
				$input = str_replace( "\n\n", "\n", $input );
				$input = trim($input);

			}
			$input = $parser->recursiveTagParse( $input, $frame );

			//default settings
			$parameters = self::getTagConfig( $args );
			$config = array();
			if ( count( $parameters->getErrors() ) > 0 ) {
				$errors = wfMessage( 'sidebarmenu-parser-config-error' ) . "\n";
				foreach ( $parameters->getErrors() as $error ) {
					$errors .= '* ' . $error->getMessage() . "\n";
				}
				return $errors;
			} else {
				foreach ( $parameters->getParameters() as $param ) {
					$config[ $param->getName() ] = $param->getValue();
				}
			}

			$id = uniqid( 'sidebar-menu-id-' );
			$output = '<div id="' . $id . '" class="sidebar-menu-container' . ( is_null( $config[ SBM_CLASS ] ) ? '' : ' ' . $config[ SBM_CLASS ] ) . '" style="display:none;' . ( is_null( $config[ SBM_STYLE ] ) ? '' : $config[ SBM_STYLE ] ) . '">';

			$menuParser = new MenuParser( $config );
			$menuTree = $menuParser->getMenuTree( $input );
			if(!is_null($menuTree)){
				$output .= $menuTree->toHTML();
			}

			if ( $config[ SBM_EDIT_LINK ] ) {
				$output .= Linker::link( $frame->getTitle(), wfMessage( 'sidebarmenu-edit' )->escaped(), array( 'title' => wfMessage( 'sidebarmenu-edit' )->escaped(), 'class' => 'sidebar-menu-edit-link' ), array( 'action' => 'edit' ) );
			}
			$output .= '</div>';

			$jsOutput = self::getJSConfig( $config, $id );
			return array( $jsOutput . $output, 'noparse' => true, 'isHTML' => true );

		}
		catch( Exception $x ) {
			wfDebug( "An error occurred during parsing of: '$input' caught exception: $x" );
			return wfMessage( 'sidebarmenu-parser-input-error', '<strong>' . $x->getMessage() . "</strong>\n<pre>$input</pre>" )->parse();
		}
	}

	/**
	 * @param array $files
	 *
	 * @return bool
	 */
	public static function registerUnitTests( &$files ) {
		$testDir = dirname( __DIR__ ) . '/test/';
		$testFiles = scandir( $testDir );
		foreach ( $testFiles as $testFile ) {
			$absoluteFile = $testDir . $testFile;
			if ( is_file( $absoluteFile ) ) {
				$files[ ] = $absoluteFile;
			}
		}
		return true;
	}

	private static function minifyJavascript( $js ) {
		$js = preg_replace( "/[\n\r]/", "", $js ); //remove newlines
		$js = preg_replace( "/[\s]{2,}/", " ", $js ); //remove spaces

		return $js;
	}

	private static function getJSConfig( $config, $id ) {
		//javascript config output
		$jsonConfig = json_encode( $config );
		$jsOutput = <<<EOT
			(function(json,id){
				if(window.sidebarmenu === undefined){
					window.sidebarmenu = {};
				}
				window.sidebarmenu[id] = json;
			})($jsonConfig,'$id');
EOT;

		$jsOutput = Html::inlineScript( $jsOutput );
		//minify js to prevent <p> tags to be rendered
		$jsOutput = self::minifyJavascript( $jsOutput );
		return $jsOutput;
	}

	private static function getTagConfig( $args ) {
		$parameterDefs = array(
			array(
				'name' => 'expanded',
				'default' => $GLOBALS['wgSideBarMenuConfig'][ SBM_EXPANDED ],
				'type' => 'boolean',
				'message' => 'sidebarmenu-param-expanded',
				'aliases' => array( 'parser.menuitem.expanded' ),
			),
			array(
				'name' => 'show',
				'default' => is_null( $GLOBALS['wgSideBarMenuConfig'][ SBM_CONTROLS_SHOW ] ) ? '[' . wfMessage( 'showtoc' )->escaped() . ']' : $wgSideBarMenuConfig[ SBM_CONTROLS_SHOW ],
				'message' => 'sidebarmenu-param-show',
				'aliases' => array( 'controls.show' ),
			),
			array(
				'name' => 'hide',
				'default' => is_null( $GLOBALS['wgSideBarMenuConfig'][ SBM_CONTROLS_HIDE ] ) ? '[' . wfMessage( 'hidetoc' )->escaped() . ']' : $wgSideBarMenuConfig[ SBM_CONTROLS_HIDE ],
				'message' => 'sidebarmenu-param-hide',
				'aliases' => array( 'controls.hide' ),
			),
			array(
				'name' => 'animate',
				'default' => $GLOBALS['wgSideBarMenuConfig'][ SBM_JS_ANIMATE ],
				'type' => 'boolean',
				'message' => 'sidebarmenu-param-animate',
				'aliases' => array( 'js.animate' ),
			),
			array(
				'name' => 'editlink',
				'default' => $GLOBALS['wgSideBarMenuConfig'][ SBM_EDIT_LINK ],
				'type' => 'boolean',
				'message' => 'sidebarmenu-param-editlink',
			),
			array(
				'name' => 'class',
				'default' => '',
				'message' => 'sidebarmenu-param-class',
			),
			array(
				'name' => 'style',
				'default' => '',
				'message' => 'sidebarmenu-param-style',
			),
			array(
				'name' => 'minimized',
				'default' => $GLOBALS['wgSideBarMenuConfig'][ SBM_MINIMIZED ],
				'type' => 'boolean',
				'message' => 'sidebarmenu-param-minimized',
			),
		);

		$processor = Processor::newDefault();
		$processor->setParameters( $args, $parameterDefs );
		$processedParams = $processor->processParameters();

		return $processedParams;
	}
}
