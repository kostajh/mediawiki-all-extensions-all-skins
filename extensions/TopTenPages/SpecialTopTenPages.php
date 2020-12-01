<?php
use HitCounters\SpecialPopularPages;

class SpecialTopTenPages extends SpecialPopularPages {
	/**
	 * @param string $name
	 */
	public function __construct( $name = 'TopTenPages' ) {
		parent::__construct( $name );
		$inc = $this->including();
	}

	/** @inheritDoc */
	public function isIncludable() {
		return true;
	}

	/** @inheritDoc */
	function isListed() {
		return false;
	}

	/**
	 * @param string|null $par
	 */
	function execute( $par ) {
		$inc = $this->including();

		if ( $inc ) {
			$parts = explode( '/', $par, 3 );
			$this->offset = (int)$parts[0];
			$this->limit = (int)$parts[1];
		}
		$this->setListoutput( false );
		$this->shownavigation = !$inc;
		parent::execute( $par );
	}

	/**
	 * @param int $offset
	 * @return string
	 */
	function openList( $offset ) {
		global $wgTopTenPagesStartAtOne;
		if ( $wgTopTenPagesStartAtOne ) {
			return parent::openList( 0 );
		}
		return parent::openList( $offset );
	}

	/** @inheritDoc */
	protected function getGroupName() {
		return 'other';
	}
}
