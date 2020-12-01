<?php

namespace Wigo3;

class WigoDataUpdate extends \DataUpdate {
	/** @var \ArrayObject */
	private $polls;

	/**
	 * @param \ArrayObject $polls
	 */
	public function __construct( $polls ) {
		parent::__construct();
		$this->polls = $polls;
	}

	/**
	 * @inheritDoc
	 */
	public function doUpdate() {
		$dbw = wfGetDB( DB_MASTER );
		foreach ( $this->polls as $poll ) {
			$dbw->replace(
				'wigotext',
				'vote_id',
				[
					'vote_id' => $poll['id'],
					'text' => $poll['text'],
					'is_cp' => $poll['cp']
				], __METHOD__ );
		}
	}
}
