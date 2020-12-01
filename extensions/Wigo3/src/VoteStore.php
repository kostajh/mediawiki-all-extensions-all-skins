<?php

namespace Wigo3;

class VoteStore {

	/**
	 * @param string $voteid
	 * @return int[]
	 */
	public static function getVoteCounts( $voteid ) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'wigovote',
			[ 'sum(case vote when 1 then 1 else 0 end) as plus',
			  'sum(case vote when -1 then 1 else 0 end) as minus',
			  'sum(case vote when 0 then 1 else 0 end) as zero' ],
			[ 'id' => $voteid ], __METHOD__
		);
		$row = $res->fetchRow();
		return [
			'plus' => $row['plus'] ?? 0,
			'minus' => $row['minus'] ?? 0,
			'zero' => $row['zero'] ?? 0,
		];
	}
}
