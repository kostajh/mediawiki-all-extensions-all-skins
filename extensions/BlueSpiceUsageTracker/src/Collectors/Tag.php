<?php
namespace BS\UsageTracker\Collectors;

class Tag extends Base {

	/**
	 *
	 * @var string
	 */
	protected $descKey = 'bs-usagetracker-tag-collector-desc';

	/**
	 *
	 * @param array $aConfig
	 */
	public function __construct( $aConfig = [] ) {
		parent::__construct( $aConfig );
	}

	/**
	 *
	 * @return \BS\UsageTracker\CollectorResult
	 */
	public function getUsageData() {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			[ 'page', 'revision', 'text' ],
			[ 'old_text' ],
			[ 'old_text LIKE "%' . $this->identifier . '%"' ],
			__METHOD__,
			[],
			[
				'revision' => [ 'JOIN', [ 'page_latest=rev_id' ] ],
				'text' => [ 'JOIN', [ 'rev_text_id=old_id' ] ]
			]
		);

		$oRes = new \BS\UsageTracker\CollectorResult( $this );
		$oRes->count = $res->numRows();
		return $oRes;
	}
}
