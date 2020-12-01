<?php

class SofaMapUpdate implements DeferrableUpdate {

	/** @var array List of sofa maps to add */
	private $smaps;
	/** @var Title */
	private $title;

	/**
	 * @param Title $title
	 * @param array $smaps
	 */
	public function __construct( Title $title, array $smaps ) {
		$this->title = $title;
		$this->smaps = $smaps;
	}

	public function doUpdate() {
var_dump( __METHOD__ );
		$sdb = new SofaDB;
		$updates = $sdb->getUpdates( $this->title, $this->smaps );
		foreach ( $updates as $update ) {
var_dump( "doing update" );
			$update->doUpdate();
		}
	}

}
