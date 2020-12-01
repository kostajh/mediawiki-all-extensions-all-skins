<?php
namespace PhpTagsObjects;

/**
 * Description of Storage
 *
 * @author pastakhov
 */
class Storage extends \PhpTags\GenericObject {

	private $row_id;
	private static $row_ids = array();
	private static $data = array();

	public function m___construct( $structure ) {
		wfDebugLog( 'PhpTags Storage', __METHOD__ );
		$frameTitle = \PhpTags\Renderer::getFrame()->getTitle();
//		echo __METHOD__ . '( ' . $frameTitle->getArticleID() . " )\n";
		$this->value = new \PhpTagsStorage\Schema( $frameTitle, $structure );
		return true;
	}

	public function m_setValues( $values ) {
		wfDebugLog( 'PhpTags Storage', __METHOD__ . print_r( $values, true ) );
		$schema = $this->value;
		$templateID = $schema->getTemplateID();
		if ( $templateID <= 0 ) {
			wfDebugLog( 'PhpTags Storage', __METHOD__ . ' has zerro template ID, skipping.');
			return;
		}
		if ( $templateID === \PhpTags\Renderer::getParser()->getTitle()->getArticleID() ) {
			wfDebugLog( 'PhpTags Storage', __METHOD__ . ' template does not store data for itself, skipping.');
			return;
		}

		$rowID = $this->getRowID();
		foreach ( $values as $fieldName => $val ) {
			$field = $schema->getField( $fieldName );
			if ( $field === false ) {
				unset( self::$data[$templateID][$rowID] );
				throw new \PhpTags\HookException( "Unknown field: $fieldName" );
			}
			self::$data[$templateID][$rowID][$field->getDBName()] = $val;
		}

	}

	private function getRowID() {
		if ( $this->row_id === null ) {
			$templateID = $this->value->getTemplateID();
			$scope = \PhpTags\Renderer::getScopeID( \PhpTags\Renderer::getFrame() );
			if ( false === isset( self::$row_ids[$templateID][$scope] ) ) {
				self::$row_ids[$templateID][$scope] = isset( self::$row_ids[$templateID] ) ? count( self::$row_ids[$templateID] ) + 1 : 1;
			}
			$this->row_id = self::$row_ids[$templateID][$scope];
		}
		return $this->row_id;
	}

	/**
	 *
	 * @param int $titleID
	 * @param array $updates
	 */
	public static function onDataUpdates( $titleID, &$updates ) {
		if ( $titleID <= 0 ) {
			wfDebugLog( 'PhpTags Storage', __METHOD__ . ' has zerro template ID, skipping.');
			return;
		}
		\PhpTagsStorage\Schema::onDataUpdates( $titleID, $updates );

		$templates = array();
		foreach ( self::$data as $templateID => $rows ) {
			$templates[] = $templateID;
			$updates[] = new \PhpTagsStorage\PageDataUpdate( $titleID, $templateID, $rows );
		}

		$oldTemplates = \PhpTagsStorage\Schema::getPageTemplates( $titleID );
		if ( $oldTemplates !== false ) {
			$deleteOldTemplates = array_diff( $oldTemplates, $templates );
			\PhpTagsStorage\Schema::loadSchema( $deleteOldTemplates );
			foreach ( $deleteOldTemplates as $templateID ) {
				if ( \PhpTagsStorage\Schema::getLoadedRow( $templateID ) === true ) { // schema doesn't exists
					continue;
				}
				$updates[] = new \PhpTagsStorage\PageDataUpdate( $titleID, $templateID, false );
			}
		}

		$updates[] = \PhpTagsStorage\Schema::createPageTemplatesUpdate( $titleID, $templates );
		self::$data = array();
		self::$row_ids = array();
	}

}
