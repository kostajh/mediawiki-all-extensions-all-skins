<?php
namespace PhpTagsStorage;

/**
 *
 */
class PageDataUpdate extends \DataUpdate {

	private $pageID;
	private $templateID;
	private $rows;

	function __construct( $pageID, $templateID, $rows ) {
		parent::__construct();

		$this->pageID = $pageID;
		$this->templateID = $templateID;
		$this->rows = $rows;
	}

	public function doUpdate() {
		wfDebugLog( 'PhpTags Storage', __METHOD__ );

		$db = wfGetDB( DB_MASTER );
		wfDebugLog( 'PhpTags Storage', __METHOD__ . ' DELETE ' . $this->templateID . ' WHERE page_id=' . $this->pageID );
		$db->delete( Schema::TABLE_PREFIX . $this->templateID, array('page_id'=>$this->pageID) );
		unset( \PhpTagsObjects\PageData::$cache[$this->pageID] );

		if ( $this->rows === false ) {
			return;
		}
		$a = array();
		$pageID = $this->pageID;
		foreach ( $this->rows as $rowID => $value ) {
			$value['page_id'] = $pageID;
			$value['row_id'] = $rowID;
			$a[] = $value;
		}
		wfDebugLog( 'PhpTags Storage', __METHOD__ . ' INSERT ' . $this->templateID . ' VALUES ' . print_r( $a, true ) );
		$db->insert( Schema::TABLE_PREFIX . $this->templateID, $a );
	}

}
