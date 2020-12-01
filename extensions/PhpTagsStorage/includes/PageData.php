<?php
namespace PhpTagsObjects;

/**
 * Description of PageData
 *
 * @author pastakhov
 */
class PageData extends \PhpTags\GenericObject {

	public static $cache = array();
	private $pageID;
	private $templates = array();

	public function m___construct( $page = null, $templates = null ) {
		wfDebugLog( 'PhpTags Storage', __METHOD__ );

		$this->pageID = $page === null ? $this->pageID = \PhpTags\Renderer::getParser()->getTitle()->getArticleID() : self::getID( $page );
		if ( $templates === null ) {
			$this->templates = null;
		} elseif ( is_array( $templates ) ) {
			foreach ( $templates as $t ) {
				$this->templates[] = self::getID( $t );
			}
		} else {
			$this->templates[] = self::getID( $templates );
		}
		return true;
	}

	private static function getID ( $page ) {
		if ( is_numeric( $page ) && $page > 0 ) {
			$title = \Title::newFromID( $page );
			return (int)$page;
		} elseif ( $page instanceof \PhpTags\GenericObject ) {
			$value = $page->getValue();
			if ( $value instanceof \Title ) {
				$title = $value;
			} else {
				return false;
			}
		} elseif ( is_string( $page) ) {
			$title = \Title::newFromText( $page );
		} else {
			return false;
		}
		if ( $title->isRedirect() ) {
			$redirects = \WikiPage::factory( $title )->getContent()->getRedirectChain();
			if ( !$redirects ) {
				return false;
			}
			$title = array_pop( $redirects );
		}
		if ( $title && $title->exists() ) {
			$user = \RequestContext::getMain()->getUser();
			if ( class_exists( 'MediaWiki\Permissions\PermissionManager' ) ) {
				// MW 1.33+
				if ( \MediaWiki\MediaWikiServices::getInstance()
					->getPermissionManager()
					->userCan( 'read', $user, $title )
				) {
					return $title->getArticleID();
				}
			} else {
				if ( $title->userCan( 'read' ) ) {
					return $title->getArticleID();
				}
			}
		}
		return false;
	}

	public function m_getValues() {
		wfDebugLog( 'PhpTags Storage', __METHOD__ );
		$pageTemplates = \PhpTagsStorage\Schema::getPageTemplates( $this->pageID );
		if ( $pageTemplates === false ) { // Page has no data
			return false;
		}

		$return = array();
		$templates = $this->templates;
		if ( $templates ) {
			$wrongTemplates = array_diff( $pageTemplates, $templates );
			foreach ( $wrongTemplates as $k => $wt ) {
				$return[$wt] = false;
				unset( $templates[$k] );
			}
			if ( ! $templates ) {
				return $return;
			}
		} else {
			$templates = $pageTemplates;
		}

		$pageID = $this->pageID;
		$pageCache =& self::$cache[$pageID];
		if ( isset(self::$cache[$pageID]) ) { // Get data from cache
			foreach ( $templates as $k => $t ) {
				if ( isset( $pageCache[$t] ) ) {
					$return[$t] = $pageCache[$t];
					unset( $templates[$k] );
				}
			}
			if ( ! $templates ) {
				return $return;
			}
		}

		\PhpTagsStorage\Schema::loadSchema( $templates );
		$db = wfGetDB( DB_REPLICA, 'PhpTags' );
		foreach ( $templates as $t ) {
			$fields = \PhpTagsStorage\Schema::getTemplateFields( $t );
			$res = $db->select( \PhpTagsStorage\Schema::TABLE_PREFIX . $t, '*', array('page_id'=>$pageID) );
			while ( $row = $res->fetchRow() ) {
				$rowID = $row['row_id'];
				foreach ( $fields as $f ) {
					$pageCache[$t][$rowID][$f->getName()] = $row[$f->getDBName()];
				}
			}
			$res->free();
			$return[$t] = $pageCache[$t];
		}
		return $return;
	}

}
