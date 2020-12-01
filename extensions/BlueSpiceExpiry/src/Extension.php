<?php

namespace BlueSpice\Expiry;

class Extension extends \BlueSpice\Extension {
	public static $expirys = [];

	/**
	 *
	 * @param int $articleId
	 * @return bool | stdClass
	 */
	public static function getExpiryForPage( $articleId ) {
		// basic caching, do not ask for an article id twice per call
		if ( isset( self::$expirys[$articleId] ) ) {
			return self::$expirys[$articleId];
		}

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			[ 'bs_expiry' ],
			'*',
			[ 'exp_page_id' => $articleId, 'exp_date <= CURDATE()' ],
			__METHOD__
		);
		if ( $res && $res->numRows() ) {
			$row = $res->fetchRow();
			self::$expirys[$articleId] = $row;
		} else {
			self::$expirys[$articleId] = false;
		}
		return self::$expirys[$articleId];
	}
}
