<?php
namespace PhpTagsStorage;

/**
 *
 */
class PageTemplatesUpdate extends \DataUpdate {

	private $pageID;
	private $templates;
	private $schemaPageTemplates;

	function __construct( $pageID, $templates, &$pageTemplates ) {
		parent::__construct();

		$this->pageID = $pageID;
		$this->templates = $templates;
		$this->schemaPageTemplates =& $pageTemplates;
	}

	public function doUpdate() {
		wfDebugLog( 'PhpTags Storage', __METHOD__ );

		$db = wfGetDB( DB_MASTER );
		$templates = $this->templates;

		if ( $templates ) {
			wfDebugLog( 'PhpTags Storage', __METHOD__ . " REPLACE pageTemplates WHERE pageID is " . $this->pageID );
			$db->replace(
					Schema::TABLE_PAGE_TEMPLATES,
					array(array('page_id')),
					array('page_id' => $this->pageID, 'templates' => \FormatJson::encode( $templates ))
				);
			$this->schemaPageTemplates[$this->pageID] = $templates;
			return;
		}

		wfDebugLog( 'PhpTags Storage', __METHOD__ . " DELETE pageTemplates WHERE pageID is " . $this->pageID );
		$db->delete( Schema::TABLE_PAGE_TEMPLATES, array('page_id'=>$this->pageID) );
		$this->schemaPageTemplates[$this->pageID] = null;
	}

}
