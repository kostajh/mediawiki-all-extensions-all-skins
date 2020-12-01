<?php

namespace BlueSpice\CountThings\Tag;

use BlueSpice\Tag\Handler;

class CountFilesHandler extends Handler {

	/**
	 *
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $dbr = null;

	/**
	 *
	 * @var bool
	 */
	protected $noduplicates = true;

	/**
	 *
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param bool $noduplicates
	 */
	public function __construct( $loadBalancer, $noduplicates ) {
		$this->dbr = $loadBalancer->getConnection( DB_REPLICA );
		$this->noduplicates = $noduplicates;
	}

	public function handle() {
		$distinct = '';
		if ( $this->noduplicates ) {
			$distinct = 'DISTINCT';
		}
		$number = $this->dbr->selectField( 'image', "COUNT( $distinct img_sha1 )" );
		return " $number ";
	}
}
