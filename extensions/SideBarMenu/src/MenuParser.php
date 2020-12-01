<?php

namespace SideBarMenu;

use InvalidArgumentException;

class MenuParser {

	private $config;

	function __construct( $config ) {
		$this->config = $config;
	}

	public function isValidInput( $data ) {
		return ! ( is_null( $data ) || strlen( $data ) == 0 );
	}

	public function getLevel( $line ) {
		return substr_count( $line, '*' );
	}

	public function getExpandedParameter( $line ) {
		if ( $this->startsWith( $line, '+' ) ) {
			return true;
		} else {
			if ( $this->startsWith( $line, '-' ) ) {
				return false;
			}
		}
		return null;
	}

	public function getTextParameter( $line ) {
		if ( preg_match( "/\[\[.*\]\]/", $line ) == 1 ) {
			return preg_filter( "/\+|\-?\**(\[\[.*\]\])\|?.*/", "$1", $line );
		} else {
			return preg_filter( "/\+|\-?\**\@?([^\|]*)\|?.*/", "$1", $line );
		}
	}

	public function getStyleParameter( $line ) {
		return preg_filter( "/.*?\|style=(.*)\|?/", "$1", $line );
	}

	public function getClassParameter( $line ) {
		return preg_filter( "/.*?\|class=(.*)\|?/", "$1", $line );
	}

	public function getExpandActionParameter( $line ) {
		if ( preg_match( "/\+|\-?\**\@.*/", $line ) == 1 ) {
			return true;
		}
		return false;
	}

	public function getMenuTree( $data ) {
		if ( $this->isValidInput( $data ) ) {
			$data = $this->cleanupData( $data );
			$root = new MenuItem( $this->config );
			$root->setExpanded( true );
			$arrayData = $this->parseDataIntoHierarchicalArray( $data );
			$this->addChildrenToMenuItemFromArrayData( $root, $arrayData );

			if ( isset( $this->config[ SBM_MINIMIZED ] ) && $this->config[ SBM_MINIMIZED ] ) {
				//When minimized is true this here forces the first menu element to be
				//collapsed causing it to display correctly and not having to add "-"
				// explicitly in the input data
				$children = $root->getChildren();
				if ( count( $children ) > 0 ) {
					$children[ 0 ]->setExpanded( false );
				}
			}

			return $root;
		}
		return null;
	}

	public function getMenuItem( $line ) {
		$line = trim( $line );
		if ( $this->isValidInput( $line ) ) {
			$menuItem = new MenuItem( $this->config );
			$menuItem->setExpanded( $this->getExpandedParameter( $line ) );
			$menuItem->setText( $this->getTextParameter( $line ) );
			$menuItem->setCustomCSSStyle( $this->getStyleParameter( $line ) );
			$menuItem->setCustomCSSClasses( $this->getClassParameter( $line ) );
			$menuItem->setExpandAction( $this->getExpandActionParameter( $line ) );
			return $menuItem;
		} else {
			throw new InvalidArgumentException();
		}
	}

	public function parseDataIntoHierarchicalArray( $data ) {
		$rootArray = array();
		$prevLevel = 0;
		$levelArray[ 0 ] = &$rootArray;
		foreach ( preg_split( "/\n/", $data ) as $line ) {
			$level = $this->getLevel( $line );
			if ( $level == $prevLevel ) {
				$levelArray[ $level ][ ] = $line;
			} else {
				if ( $level - 1 == $prevLevel ) {
					//child of previous line
					$parent = array_pop( $levelArray[ $level - 1 ] );
					$levelArray[ $level - 1 ][ $parent ][ ] = $line;
					$levelArray[ $level ] = &$levelArray[ $level - 1 ][ $parent ];
				} else {
					if ( $level < $prevLevel ) {
						//back some levels
						$levelArray[ $level ][ ] = $line;
					} else {
						//syntax error
						throw new InvalidArgumentException(wfMessage( 'sidebarmenu-parser-syntax-error', $line )->text() );
					}
				}
			}
			$prevLevel = $level;
		}
		return $rootArray;
	}

	public function addChildrenToMenuItemFromArrayData( MenuItem &$rootMenuItem, $arrayData ) {
		foreach ( $arrayData as $key => $val ) {
			if ( is_string( $key ) ) {
				$menuItem = $this->getMenuItem( $key );
				$rootMenuItem->addChild( $menuItem );
				$this->addChildrenToMenuItemFromArrayData( $menuItem, $val );
			} else {
				$menuItem = $this->getMenuItem( $val );
				$rootMenuItem->addChild( $menuItem );
			}
		}
	}

	private static function startsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		return ( substr( $haystack, 0, $length ) === $needle );
	}

	private static function cleanupData( $data ) {
		//remove pre-/post- newline
		$data = trim($data, "\n ");

		return $data;
	}

}
