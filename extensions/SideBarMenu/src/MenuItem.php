<?php

namespace SideBarMenu;

use InvalidArgumentException;

class MenuItem {

	private $expanded = null;
	/**
	 * @var MenuItem[]
	 */
	private $children = array();
	/**
	 * @var null|MenuItem
	 */
	private $parent = null;
	private $text;
	private $customCSSStyle;
	private $customCSSClasses;
	private $config;
	private $expandAction = false;

	/**
	 * @param array $config
	 */
	function __construct( $config ) {
		$this->config = $config;
	}

	public function setExpanded( $expanded ) {
		if ( is_null( $expanded ) || $expanded === true || $expanded === false ) {
			$this->expanded = $expanded;
		} else {
			throw new InvalidArgumentException( wfMessage( 'sidebarmenu-parser-menuitem-expanded-invalid' )->text() );
		}
	}

	public function isExpandedSpecified() {
		return ! is_null( $this->expanded );
	}

	public function isExpanded() {
		if ( $this->isExpandedSpecified() ) {
			return $this->expanded;
		} else {
			return $this->config[ SBM_EXPANDED ];
		}
	}

	public function setExpandAction( $expandAction ) {
		$this->expandAction = $expandAction;
	}

	public function isExpandAction() {
		return $this->expandAction;
	}

	public function setText( $link ) {
		$this->text = $link;
	}

	public function getText() {
		return $this->text;
	}

	public function addChild( MenuItem $child ) {
		if ( !in_array( $child, $this->children ) ) {
			$this->children[ ] = $child;
			$child->setParent( $this );
		}
	}

	/**
	 * @return MenuItem[]
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * @return bool
	 */
	public function hasChildren() {
		return sizeof( $this->children ) > 0;
	}

	/**
	 * @param MenuItem $parent
	 */
	public function setParent( MenuItem $parent ) {
		if ( $this->parent !== $parent ) {
			$this->parent = $parent;
			$parent->addChild( $this );
		}
	}

	/**
	 * @return null|MenuItem
	 */
	public function getParent() {
		return $this->parent;
	}

	public function isRoot() {
		return is_null( $this->parent );
	}

	public function getLevel() {
		if ( $this->isRoot() ) {
			return 0;
		} else {
			return 1 + $this->getParent()->getLevel();
		}
	}

	public function toHTML() {
		$output = "";
		if ( $this->isRoot() ) {
			$output .= $this->childrenToHTML();
		} else {
			$itemClasses[ ] = 'sidebar-menu-item';
			$itemClasses[ ] = 'sidebar-menu-item-' . $this->getLevel();

			if ( !$this->hasChildren() ) {
				//to distinguish if this is the last item in the menu tree (eg. a leaf)
				$itemClasses[ ] = 'sidebar-menu-item-last';
			}

			if ( $this->hasChildren() ) {
				if ( $this->isExpandedSpecified() ) {
					$itemClasses[ ] = $this->isExpanded() ? 'sidebar-menu-item-expanded' : 'sidebar-menu-item-collapsed';
				} else {
					$itemClasses[ ] = $this->config[ SBM_EXPANDED ] ? 'sidebar-menu-item-expanded' : 'sidebar-menu-item-collapsed';
				}
			}

			if ( $this->hasCustomCSSClasses() ) {
				$itemClasses[ ] = $this->getCustomCSSClasses();
			}

			$textClasses[ ] = 'sidebar-menu-item-text';
			$textClasses[ ] = 'sidebar-menu-item-text-' . $this->getLevel();
			if ( $this->isExpandAction() ) {
				$textClasses[ ] = 'sidebar-menu-item-expand-action';
			}

			$output .= "<li class=\"" . join( ' ', $itemClasses ) . "\"" . ( $this->hasCustomCSSStyle() ? " style=\"{$this->getCustomCSSStyle()}\"" : '' ) . ">";
			$output .= "<div class=\"sidebar-menu-item-text-container\">";
			$output .= "<span class=\"" . join( ' ', $textClasses ) . "\">" . $this->getText() . "</span>";

			if ( $this->hasChildren() && $this->isExpandedSpecified() ) {
				$output .= "<span class=\"sidebar-menu-item-controls\"></span>";
			}

			$output .= "</div>";
			$output .= $this->childrenToHTML();
			$output .= "</li>";
		}

		return $output;
	}

	private function childrenToHTML() {
		if ( $this->hasChildren() ) {
			$menuClasses[ ] = 'sidebar-menu';
			$menuClasses[ ] = 'sidebar-menu-' . $this->getLevel();

			$output = "<ul class=\"" . join( ' ', $menuClasses ) . "\">";
			foreach ( $this->getChildren() as $child ) {
				$output .= $child->toHTML();
			}
			$output .= "</ul>";
			return $output;
		}
	}

	public function getCustomCSSStyle() {
		return $this->customCSSStyle;
	}

	public function setCustomCSSStyle( $style ) {
		$this->customCSSStyle = $style;
	}

	public function hasCustomCSSStyle() {
		return isset( $this->customCSSStyle ) && $this->customCSSStyle !== '';
	}

	public function getCustomCSSClasses() {
		return $this->customCSSClasses;
	}

	public function setCustomCSSClasses( $class ) {
		$this->customCSSClasses = $class;
	}

	public function hasCustomCSSClasses() {
		return isset( $this->customCSSClasses ) && $this->customCSSClasses !== '';
	}

}
