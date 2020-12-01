<?php
/**
 * @author: Kim Eik
 */

namespace SideBarMenu\SubPage;

use InvalidArgumentException;
use Title;

class SubPageRenderer {

	/**
	 * Replaces #subpage occurences with a propertly formatted sidebarmenu syntax
	 * of subpages
	 *
	 * @param $input
	 * @param \Parser $parser
	 */
	public static function renderSubPages( &$input, \Parser &$parser ) {
		$lines = explode( "\n", $input );
		for ( $x = 0; $x < count( $lines ); $x ++ ) {
			$line = & $lines[ $x ];
			$line = preg_replace_callback( "/([-+]*)(\\**)#subpage .*/", function ( $matches ) use ($parser) {
				$title = substr( $matches[ 0 ], strpos( $matches[ 0 ], '#subpage ' ) + 9 );
				$title = $parser->recursiveTagParse($title);
				$title = Title::newFromText( $title );
				if ( is_null( $title ) ) {
					throw new InvalidArgumentException( wfMessage( 'sidebarmenu-parser-subpage-error-title-not-found' ) );
				}
				$subPages = SubPageRenderer::getHierarchicalSubpages( $title );
				$code = SubPageRenderer::getSubpagesWikiCode( $subPages, $matches[ 1 ], strlen( $matches[ 2 ] ) );
				return $code;
			}, $line );

		}
		$input = implode( "\n", $lines );
	}

	/**
	 * Creates wiki code of the subpages that is readable by <sidebarmenu>
	 *
	 * @param $subPages
	 * @param string $prefix
	 * @param int $level
	 *
	 * @return string
	 */
	public static function getSubpagesWikiCode( $subPages, $prefix = '', $level = 0 ) {
		$result = $prefix;
		foreach ( $subPages as $sub => $children ) {
			$sub = Title::newFromText( $sub );
			for ( $i = 0; $i < $level; $i ++ ) {
				$result .= '*';
			}
			$result .= '[[' . $sub->getFullText() . '|' . $sub->getSubpageText() . "]]\n";

			if ( ! empty( $children ) ) {
				$result .= self::getSubpagesWikiCode( $children, $prefix, $level + 1 );
			}

		}
		return $result;
	}

	/**
	 * Creates a recursively sorted hierarchical representation of subpages
	 * In the form of title->[]children
	 *
	 * @param Title $title
	 *
	 * @return array
	 */
	public static function getHierarchicalSubpages( Title $title ) {
		$titles = array_keys( self::getSubpagesAsSet( $title ) );
		usort( $titles, function ( $a, $b ) {
			return ( substr_count( $a, '/' ) - substr_count( $b, '/' ) );
		} );
		$relations = array();
		self::findChildrenOf( $title->getFullText(), $titles, $relations );
		self::tksort( $relations );
		return $relations;
	}

	public static function findChildrenOf( $parentTitle, $titles, &$array ) {
		foreach ( $titles as $childTitle ) {
			if ( strpos( $childTitle, $parentTitle ) === 0 ) {
				if ( substr_count( $parentTitle, '/' ) + 1 === substr_count( $childTitle, '/' ) ) {
					$array[ $parentTitle ][ $childTitle ] = array();
					self::findChildrenOf( $childTitle, $titles, $array[ $parentTitle ] );
				}
			}
		}
	}

	/**
	 * Retrieves subpages as a set
	 *
	 * @param Title $title
	 * @param string $parent
	 *
	 * @return array
	 */
	public static function getSubpagesAsSet( Title $title, $parent = '' ) {
		$subPages = $title->getSubpages();
		if(empty($subPages)){
			return array();
		}
		/** @var Title[] $titles */
		$titles[ ] = $title;
		while ( $subPages->valid() ) {
			$titles[ ] = $subPages->current();
			$subPages->next();
		}

		$result = array();
		foreach ( $titles as $title ) {
			$titleText = $title->getFullText();
			$titleTextParts = explode( '/', $titleText );
			$titleText = '';
			foreach ( $titleTextParts as $part ) {
				$titleText .= $part;
				$result[ $titleText ] = null;
				$titleText .= '/';
			}
		}
		return $result;
	}

	/**
	 * @param $array
	 */
	private static function tksort( &$array ) {
		ksort( $array );
		foreach ( array_keys( $array ) as $k ) {
			if ( gettype( $array[ $k ] ) == "array" ) {
				self::tksort( $array[ $k ] );
			}
		}
	}
}