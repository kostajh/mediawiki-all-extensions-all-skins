<?php
namespace BS\UsageTracker\Collectors;

class Property extends Base {

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
			[ 'page_props' ],
			[ 'pp_propname' ],
			[ 'pp_propname' => $this->identifier ],
			__METHOD__
		);

		$oRes = new \BS\UsageTracker\CollectorResult( $this );
		$oRes->count = $res->numRows();
		return $oRes;
	}
}
