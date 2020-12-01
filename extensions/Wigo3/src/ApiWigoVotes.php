<?php

namespace Wigo3;

use ApiBase;
use ApiQuery;
use ApiQueryBase;

class ApiWigoVotes extends ApiQueryBase {

	/**
	 * @param ApiQuery $query
	 * @param string $moduleName
	 */
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'wv' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$db = $this->getDB();
		$params = $this->extractRequestParams();

		$month = $params['month'];
		if ( $month !== null ) {
			$month = str_pad( $month, 2, '0', STR_PAD_LEFT );
		}

		$year = $params['year'];
		$limit = $params['limit'];
		$start = $params['start'];
		$end = $params['end'];
		$id = $params['id'];
		$prefix = $params['prefix'];
		$dir = $params['dir'];
		$min = $params['min'];
		$max = $params['max'];

		$this->addTables( 'wigovote' );

		// Handle continue parameter
		if ( $params['continue'] !== null ) {
			$continue = explode( '|', $params['continue'] );
			if ( count( $continue ) != 2 ) {
				$this->dieWithError( 'apierror-badcontinue', '_badcontinue' );
			}
			$db = $this->getDB();
			$encId = $db->addQuotes( $continue[0] );
			$encVoter = $db->addQuotes( $continue[2] );
			$encTS = $db->addQuotes( $db->timestamp( $continue[1] ) );
			$op = ( $dir == 'older' ? '<' : '>' );
			$this->addWhere(
				"timestamp $op $encTS OR " .
				"(timestamp = $encTS AND " .
				"(id $op= $encId" .
				")" .
				")"
			);
		}

		// "rev_timestamp like \"{$this->year}{$this->month}%\""

		$anyString = $this->getDB()->anyString();
		$anyChar = $this->getDB()->anyChar();
		if ( $year !== null && $month !== null ) {
			$this->addWhere( 'timestamp' . $this->getDB()->buildLike(
				"{$year}{$month}", $anyString ) );
		} elseif ( $year !== null ) {
			$this->addWhere( 'timestamp' . $this->getDB()->buildLike(
				"{$year}", $anyString ) );
		} elseif ( $month !== null ) {
			$this->addWhere(
				'timestamp' . $this->getDB()->buildLike(
					$anyChar, $anyChar, $anyChar, $anyChar,
					$month, $anyString
				)
			);
		}

		$this->addFields( [ 'id', 'vote', 'timestamp' ] );

		$this->addWhereRange( 'timestamp', $dir, $start, $end );
		if ( $prefix !== null ) {
			$this->addWhere( 'id' . $this->getDB()->buildLike( $prefix, $this->getDB()->anyString() ) );
			$this->addWhereRange( 'id', $dir, null, null );
		} elseif ( $id !== null ) {
			$this->addWhere( 'id' . $this->getDB()->buildLike( $id ) );
		}

		$this->addWhereRange( 'vote', 'newer', $min, $max, false );

		$this->addOption( 'LIMIT', $limit + 1 );

		$res = $this->select( __METHOD__ );

		$count = 0;
		$result = $this->getResult();
		$data = [];

		foreach ( $res as $row ) {
			if ( ++$count > $limit ) {
				// We've reached the one extra which shows that there are
				// additional pages to be had. Stop here...
				$this->setContinueEnumParameter( 'continue', $this->continueStr( $row ) );
				break;
			}

			$data['id'] = $row->id;
			$data['timestamp'] = wfTimestamp( TS_ISO_8601, $row->timestamp );
			$data['value'] = $row->vote;
			$fit = $result->addValue( [ 'query', $this->getModuleName() ], null, $data );
			if ( !$fit ) {
				$this->setContinueEnumParameter( 'continue', $this->continueStr( $row ) );
				break;
			}
		}

		$db->freeResult( $res );

		$result->addIndexedTagName( [ 'query', $this->getModuleName() ], 'vote' );
	}

	/**
	 * @param \stdClass $row
	 * @return string
	 */
	private function continueStr( $row ) {
		return $row->id . '|' . wfTimestamp( TS_ISO_8601, $row->timestamp );
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParams() {
		return [
			'month' => null,
			'year' => null,
			'id' => null,
			'prefix' => null,
			'continue' => null,
			'start' => [
				ApiBase::PARAM_TYPE => 'timestamp'
			],
			'end' => [
				ApiBase::PARAM_TYPE => 'timestamp'
			],
			'min' => null,
			'max' => null,
			'limit' => [
				ApiBase::PARAM_DFLT => 10,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			],
			'dir' => [
				ApiBase::PARAM_DFLT => 'older',
				ApiBase::PARAM_TYPE => [
					'newer',
					'older'
				]
			],
		];
	}

	/**
	 * @deprecated
	 * @return string[]
	 */
	public function getParamDescription() {
		return [
			'month' => 'Only count votes from this month',
			'year' => 'Only count votes from this year',
			'id' => 'Return votes for this entry',
			'prefix' => 'Return entries whose ids begin with this value. Overrides ' .
				$this->getModulePrefix() . 'id',
			'start' => 'The start timestamp to return from',
			'end' => 'The end timestamp to return to',
			'continue' => 'When more results are available, use this to continue',
			'limit' => 'How many total entries to return',
			'dir' => 'The direction to search (older or newer)',
			'min' => 'Only return votes greater or equal to this',
			'max' => 'Only return votes lower or equal to this',
		];
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function getDescription() {
		return 'Get wigo votes';
	}

	/**
	 * @deprecated
	 * @return string[]
	 */
	public function getExamples() {
		return [
			'api.php?action=query&list=wigovotes',
			'api.php?action=query&list=wigovotes&wvprefix=wigo&wvmin=-1&wvmax=1',
			'api.php?action=query&list=wigovotes&wvid=wigo3334&wvstart=2010-04-13T11:07:38Z&' .
				'wvlimit=10&wvdir=newer'
		];
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function getVersion() {
		return "1.0";
	}
}
