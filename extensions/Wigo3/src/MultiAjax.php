<?php

namespace Wigo3;

class MultiAjax {

	/**
	 * @param string $pollid
	 * @param int $vote
	 * @param int $countoptions
	 * @return string
	 */
	public static function vote( $pollid, $vote, $countoptions ) {
		$dbw = wfGetDB( DB_MASTER );
		global $wgUser, $wgWigo3ConfigStoreIPs, $wgRequest;
		$voter = $wgWigo3ConfigStoreIPs ? $wgRequest->getIP() : $wgUser->getName();
		$dbw->startAtomic( __METHOD__ );
		$dbw->replace(
			'wigovote',
			[ 'id', 'voter_name' ],
			[
				'id' => $pollid,
				'voter_name' => $voter,
				'vote' => $vote,
				'timestamp' => wfTimestampNow()
			],
			__METHOD__ );
		$dbw->endAtomic( __METHOD__ );
		// get the number of votes for each option
		//$dbr = wfGetDB(DB_SLAVE);
		$res = $dbw->select(
			'wigovote',
			[ 'vote', 'count(vote)' ],
			[ 'id' => $pollid ],
			__METHOD__,
			[ 'GROUP BY' => [ 'id', 'vote' ] ]
		);
		// fill with zeroes
		for ( $i = 0; $i < $countoptions; ++$i ) {
			$results[$i] = 0;
		}
		// now store values for options that have received votes
		while ( $row = $res->fetchRow() ) {
			$results[$row['vote']] = $row['count(vote)'];
		}
		$res->free();
		return implode( ":", $results );
	}

	/**
	 * @param string $pollid
	 * @return string
	 */
	public static function getmyvote( $pollid ) {
		$dbr = wfGetDB( DB_REPLICA );
		global $wgUser, $wgWigo3ConfigStoreIPs, $wgRequest;
		$voter = $wgWigo3ConfigStoreIPs ? $wgRequest->getIP() : $wgUser->getName();
		$res = $dbr->select(
			'wigovote',
			[ 'vote' ],
			[ 'id' => $pollid, 'voter_name' => $voter ],
			__METHOD__
		);
		$row = $res->fetchRow();
		$myvote = $row['vote'] ?? -1;
		$res->free();
		return "-{$myvote}";
	}
}
