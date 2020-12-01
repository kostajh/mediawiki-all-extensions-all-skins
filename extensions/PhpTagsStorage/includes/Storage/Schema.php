<?php
namespace PhpTagsStorage;

class Schema {

	const TABLE_PREFIX = 'phptags_storage_';
	const TABLE_SCHEMA = 'phptags_schemas';
	const TABLE_PAGE_TEMPLATES = 'phptags_page_templates';

	/**
	 *
	 * @var \Title
	 */
	private $title;

	private static $schemaUpdates = array();

	private $templateID = 0;
	private static $loadedRows = array();
	private static $templates = array();
	private static $pageTemplates = array();

	public function __construct( \Title $templateTitle, $newStructure = null ) {
		$this->title = $templateTitle;
		$templateID = $templateTitle->getArticleID();
		$this->templateID = $templateID;

		$this->preloadSchemas(); // try to query structures for all templates in the page if needed
		$originFields = self::getTemplateFields( $templateID );
		$newFields = array();
		$number = 1;
		foreach ( $newStructure as $fieldName => $fieldType ) {
			$newFields[$fieldName] = Field::newFromUserString( $templateID, $fieldName, $fieldType, $number++ );
		}

		if ( $templateID <= 0 ) {
			wfDebugLog( 'PhpTags Storage', __METHOD__ . ' has zerro template ID, skipping.');
			return;
		}

		if ( $originFields === false ) {
			wfDebugLog( 'PhpTags Storage', __METHOD__ . ' has new schema, will be created.');
			$fDropTable = false;
		} else {
			if ( $originFields == $newFields ) {
				wfDebugLog( 'PhpTags Storage', __METHOD__ . ' has no changes in schema, skipping.');
				return;
			}
			wfDebugLog( 'PhpTags Storage', __METHOD__ . ' has changes in schema, will be updated.');
			$fDropTable = true;
		}

		self::$templates[$templateID] = $newFields;
		self::$schemaUpdates[$templateID] = new SchemaUpdate( $templateID, $newFields, $fDropTable, self::$loadedRows, self::$templates );
	}

	private function preloadSchemas() {
		$pageID = \PhpTags\Renderer::getParser()->getTitle()->getArticleID();
		if ( $pageID <= 0 || isset( self::$pageTemplates[$pageID] ) ) { // already preloaded or id = 0
			return;
		}

		$templates = self::getPageTemplates( $pageID );
		$templateID = $this->templateID;
		if( $templateID <= 0 ) {
			return;
		}

		if ( $templates === false ) {
			$templates = array( $templateID );
		} elseif( false === in_array( $templateID, $templates ) ) {
			$templates[] = $templateID;
		}
		return self::loadSchema( $templates );
	}

	public static function getPageTemplates( $pageID ) {
		if ( isset(self::$pageTemplates[$pageID]) ) {
			if ( self::$pageTemplates[$pageID] === true ) {
				return false;
			}
			return self::$pageTemplates[$pageID];
		}

		$db = wfGetDB( DB_REPLICA );
		$rowTemplates = $db->selectRow( self::TABLE_PAGE_TEMPLATES , 'templates', array('page_id'=>$pageID) );
		if ( $rowTemplates !== false ) {
			$templates = \FormatJson::decode( $rowTemplates->templates, true );
			self::$pageTemplates[$pageID] = $templates;
			return $templates;
		}

		self::$pageTemplates[$pageID] = true;
		return false;
	}

	public static function loadSchema( $templates ) {
		$tmp = array();
		foreach ( $templates as $id ) {
			if ( isset( self::$loadedRows[$id] ) ) {
				continue;
			}
			$tmp[] = $id;
		}
		if ( ! $tmp ) {
			return true;
		}

		$db = wfGetDB( DB_REPLICA );
		$schemaRows = $db->select( self::TABLE_SCHEMA, array('template_id','table_schema'), array('template_id'=>$tmp) );
		while ( $row = $schemaRows->fetchObject() ) {
			self::$loadedRows[$row->template_id] = $row->table_schema;
		}
		foreach ( $tmp as $id ) {
			if ( isset( self::$loadedRows[$id] ) ) {
				continue;
			}
			self::$loadedRows[$id] = true;
		}
		return $schemaRows->numRows();
	}

	/**
	 *
	 * @param type $templateID
	 * @return Field[]
	 */
	public static function getTemplateFields( $templateID ) {
		if ( $templateID <= 0 ) {
			return false;
		}
		if ( !isset( self::$templates[$templateID] ) ) {
			if ( !isset( self::$loadedRows[$templateID] ) ) {
				self::loadSchema( array($templateID) );
			}
			if ( self::$loadedRows[$templateID] === true ) {
				self::$templates[$templateID] = true;
			} else {
				$fields = \FormatJson::decode( self::$loadedRows[$templateID], true );
				$objFields = array();
				foreach ( $fields as $fieldName => $fieldInfo ) {
					$objFields[$fieldName] = Field::newFromDB( $templateID, $fieldName, $fieldInfo );
				}
				self::$templates[$templateID] = $objFields;
			}
		}
		return self::$templates[$templateID] === true ? false : self::$templates[$templateID];
	}

	public function getTemplateID() {
		return $this->templateID;
	}

	/**
	 * Returns filed by name
	 * @param string $fieldName
	 * @return Field
	 */
	public function getField( $fieldName ) {
		return isset( self::$templates[$this->templateID][$fieldName] ) ? self::$templates[$this->templateID][$fieldName] : false;
	}

	public static function getLoadedRow( $id ) {
		return isset( self::$loadedRows[$id] ) ? self::$loadedRows[$id] : null;
	}

	public static function createPageTemplatesUpdate( $pageID, $templates ) {
		return new PageTemplatesUpdate( $pageID, $templates, self::$pageTemplates );
	}

	/**
	 *
	 * @param int $titleID
	 * @param array $updates
	 */
	public static function onDataUpdates( $titleID, &$updates ) {
		if ( isset( self::$schemaUpdates[$titleID] ) ) { // need to update the schema
			wfDebugLog( 'PhpTags Storage', __METHOD__ . ' adds UPDATE schema task for ID:' . $titleID );
			$updates[] = self::$schemaUpdates[$titleID];
//			echo 'UPDATE schema task ' . $titleID;
			self::$schemaUpdates[$titleID] = null;
		} elseif ( isset( self::$templates[$titleID] ) && self::$templates[$titleID] !== true ) {
//			echo "...SKIP";
		} elseif ( isset( self::$loadedRows[$titleID] ) || self::loadSchema( array($titleID) ) > 0 ) { // need to drop the storage table
			wfDebugLog( 'PhpTags Storage', __METHOD__ . ' adds DROP TABLE schema task for ID:' . $titleID );
			$updates[] = new SchemaUpdate( $titleID, false, true, self::$loadedRows, self::$templates ); // drop it
//			echo 'DROP TABLE schema task ' . $titleID;
		}
//		echo "...\n";
	}

	public static function onPageDelete( $titleID ) {
		unset( self::$templates[$titleID] );
	}

}
