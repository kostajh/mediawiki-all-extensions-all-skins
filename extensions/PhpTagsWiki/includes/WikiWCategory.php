<?php
namespace PhpTagsObjects;

use Category;
use PhpTags\GenericObject;
use PhpTags\Hooks;
use PhpTags\Renderer;

/**
 * Description of WikiWCategory
 *
 * @author pastakhov
 */
class WikiWCategory extends GenericObject {

	private static $cache = [];

	public function __construct( $objectName, $objectKey, $value = null ) {
		if ( false === $value instanceof \Category ) {
			$value = null;
		}
		parent::__construct( $objectName, $objectKey, $value );
	}

	public function m___construct( $name = null ) {
		$category = null;
		if ( $name instanceof GenericObject ) {
			$value = $name->getValue();
			if ( $value instanceof Category ) {
				$category = $value;
			} elseif ( $value instanceof \Title && $value->inNamespace( NS_CATEGORY ) ) {
				$category = Category::newFromTitle( $value );
			}
		} elseif ( true === is_string( $name ) ) {
			$category = Category::newFromName( $name );
		} elseif ( true === is_numeric( $name ) ) {
			$category = Category::newFromID( (int) $name );
		}

		if ( $category instanceof Category ) {
			$dbkey = $category->getTitle()->getPrefixedDBkey();
			if ( isset( self::$cache[$dbkey] ) ) {
				$this->value = self::$cache[$dbkey];
			} else {
				Renderer::incrementExpensiveFunctionCount();
				self::$cache[$dbkey] = $category;
				$this->value = $category;
			}
			return true;
		}
		$this->value = null;
		return false;
	}

	public function p_memberCount() {
		return $this->value->getPageCount();
	}

	public function p_pageCount() {
		$category = $this->value;
		$count = $category->getPageCount();
		$count -= $category->getSubcatCount();
		$count -= $category->getFileCount();
		return $count;
	}

	public function p_fileCount() {
		return $this->value->getFileCount();
	}

	public function p_subcatCount() {
		return $this->value->getSubcatCount();
	}

	public function p_name() {
		return $this->value->getName();
	}

	public function p_id() {
		return $this->value->getID();
	}

	public function p_title() {
		return Hooks::getObjectWithValue( 'WTitle', $this->value->getTitle() );
	}

}

