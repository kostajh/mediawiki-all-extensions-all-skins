<?php

namespace Wigo3;

use ApiBase;
use ApiQuery;
use ApiQueryBase;

class ApiWigo extends ApiQueryBase {

	/**
	 * @param ApiQuery $query
	 * @param string $moduleName
	 */
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'wigo' );
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
		$prefix = $params['prefix'];

		$this->addTables( 'wigovote' );

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

		$this->addFields( [ 'id', 'sum(vote) AS sum', 'count(vote) AS count' ] );
		$this->addOption( 'GROUP BY', 'id' );

		$this->addWhereRange( 'id', 'newer', $start, null );
		if ( $prefix !== null ) {
			$this->addOption( 'ORDER BY', "0 + substring(id," . strlen( $prefix ) . "+1)" );
		}
		$this->addWhere( 'id' . $this->getDB()->buildLike( $prefix, $anyString ) );
		$this->addWhereRange( 'vote', 'newer', '-1', '+1', false );

		$this->addOption( 'LIMIT', $limit + 1 );

		$res = $this->select( __METHOD__ );

		$count = 0;
		$result = $this->getResult();
		$data = [];

		foreach ( $res as $row ) {
			if ( ++$count > $limit ) {
				// We've reached the one extra which shows that there are additional
				// pages to be had. Stop here...
				$this->setContinueEnumParameter( 'start', $row->id );
				break;
			}

			$data['id'] = $row->id;
			$data['sum'] = $row->sum;
			$data['count'] = $row->count;
			$fit = $result->addValue( [ 'query', $this->getModuleName() ], null, $data );
			if ( !$fit ) {
				$this->setContinueEnumParameter( 'start', $row->id );
				break;
			}
		}

		$db->freeResult( $res );

		$result->addIndexedTagName( [ 'query', $this->getModuleName() ], 'entry' );
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParams() {
		return [
			'month' => null,
			'year' => null,
			'prefix' => null,
			'start' => null,
			'limit' => [
				ApiBase::PARAM_DFLT => 10,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			]
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
			'prefix' => 'Return entries whose ids begin with this value',
			'start' => 'Start from this id',
			'limit' => 'How many total entries to return',
		];
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function getDescription() {
		return 'Get wigo entries';
	}

	/**
	 * @deprecated
	 * @return string[]
	 */
	public function getExamples() {
		return [
			'api.php?action=query&list=wigo',
			'api.php?action=query&list=wigo&wigoprefix=wigo&wigostart=wigo3500',
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
