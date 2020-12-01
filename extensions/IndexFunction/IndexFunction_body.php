<?php
/* TODO:
 * Memcached
 * Warnings for API edit
 * Better documentation
*/

class IndexFunction {
	public $mTo = array(); // An array of titles for pages being indexed
	public $mFrom = null; // A title object representing the index-title

	function __construct() {}

	// Constructor for a known index-title
	public static function newFromTitle( Title $indextitle ) {
		$ns = $indextitle->getNamespace();
		$t = $indextitle->getDBkey();
		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select( 'indexes', 'in_from',
			array( 'in_namespace' => $ns, 'in_title' => $t ),
			__METHOD__
		);

		if ( !$res->numRows() ) {
			return null;
		}

		$ind = new IndexFunction();
		$ids = array();

		foreach ( $res as $row ) {
			$ids[] = $row->in_from;
		}

		$ind->mTo = Title::newFromIDs( $ids );
		$ind->mFrom = $indextitle;

		return $ind;
	}

	// Constructor for known target
	public static function newFromTarget( Title $target ) {
		$pageid = $target->getArticleID();
		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select( 'indexes', array( 'in_namespace', 'in_title' ),
			array( 'in_from' => $pageid ),
			__METHOD__
		);

		if ( !$res->numRows() ) {
			return null;
		}

		$ind = new IndexFunction();
		$row = $res->fetchRow();
		$ind->mFrom = Title::makeTitle( $row->in_namespace, $row->in_title );

		return $ind;
	}

	public function getIndexTitle() {
		return $this->mFrom;
	}

	public function getTargets() {
		if ( $this->mTo ) {
			return $this->mTo;
		}

		$dbr = wfGetDB( DB_REPLICA );
		$ns = $this->mFrom->getNamespace();
		$t = $this->mFrom->getDBkey();

		$res = $dbr->select( 'indexes', 'in_from',
			array( 'in_namespace' => $ns, 'in_title' => $t ),
			__METHOD__
		);

		$ids = array();

		foreach ( $res as $row ) {
			$ids[] = $row->in_from;
		}

		$this->mTo = Title::newFromIDs( $ids );

		return $this->mTo;
	}

	// Makes an HTML <ul> list of targets
	public function makeTargetList() {
		$targets = $this->getTargets();
		$list = Xml::openElement( 'ul' );

		foreach ( $targets as $t ) {
			$link = Linker::link( $t, $t->getPrefixedText(), array(), array(), array( 'known', 'noclasses' ) );
			$list .= Xml::tags( 'li', null, $link );
		}

		$list .= Xml::CloseElement( 'ul' );

		return $list;
	}

	// Returns true if a redirect should go to a special page
	// ie - if there are multiple targets
	public function useSpecialPage() {
		if ( !$this->mTo ) {
			$this->getTargets();
		}

		return count( $this->mTo ) > 1;
	}
}
