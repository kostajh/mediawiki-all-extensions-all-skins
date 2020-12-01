<?php

namespace BlueSpice\Expiry;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter\Date;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\ResultSet;
use BlueSpice\Expiry\Data\Record;
use BlueSpice\Expiry\Data\Store;
use DateTime;
use DateTimeZone;
use Status;
use Title;

class Factory {

	/**
	 *
	 * @var Record[]
	 */
	protected $targetCache = [];

	public function __construct() {
	}

	/**
	 *
	 * @param Title $title
	 * @return bool|Record
	 */
	public function newFromTitle( Title $title ) {
		if ( $title->getArticleID() < 1 ) {
			return false;
		}
		$record = $this->fromCache( $title );
		if ( $record ) {
			return $record;
		}
		$readerParams = new ReaderParams(
			[
				ReaderParams::PARAM_FILTER => [ [
					Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
					Numeric::KEY_PROPERTY => Record::PAGE_ID,
					Numeric::KEY_VALUE => $title->getArticleID(),
					Numeric::KEY_TYPE => 'numeric'
				]
			] ], [
				ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE
			]
		);
		$res = $this->getStore()->getReader()->read( $readerParams );
		$record = false;
		foreach ( $res->getRecords() as $record ) {
		}

		if ( !$record ) {
			$record = new Record( (object)[
				Record::PAGE_ID => $title->getArticleID()
			] );
		}

		$this->appendCache( $record );
		return $record;
	}

	/**
	 *
	 * @param Title $title
	 * @param DateTime $date
	 * @param string $comment
	 * @return Status
	 */
	public function expire( Title $title, DateTime $date, $comment = '' ) {
		if ( $title->getArticleID() < 1 ) {
			return Status::newFatal( 'Title must exist' );
		}

		$record = new Record( (object)[
			Record::PAGE_ID => $title->getArticleID(),
			Record::COMMENT => $comment,
			// The format used for date here is bad. Should switch to TS_MW
			Record::DATE => $date->format( 'Y-m-d' ),
		] );
		$res = $this->getStore()->getWriter()->write( new ResultSet( [ $record ], 1 ) );
		$this->invalidate( $title );
		return $res->getRecords()[0]->getStatus();
	}

	/**
	 *
	 * @param DateTime|null $from
	 * @param DateTime|null $to
	 * @return Title[]
	 */
	public function getExpiredTitles( DateTime $from = null, DateTime $to = null ) {
		if ( !$from ) {
			$from = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		}
		$filter = [ [
				Date::KEY_COMPARISON => Date::COMPARISON_GREATER_THAN,
				Date::KEY_PROPERTY => Record::DATE,
				// TODO: Replace old format with MW_TS
				Date::KEY_VALUE => "'" . $from->format( 'Y-m-d' ) . "'",
				Date::KEY_TYPE => FieldType::DATE
			],
		];
		if ( $to ) {
			$filter[] = [
				Date::KEY_COMPARISON => Date::COMPARISON_LOWER_THAN,
				Date::KEY_PROPERTY => Record::DATE,
				// TODO: Replace old format with MW_TS
				Date::KEY_VALUE => "'" . $to->format( 'Y-m-d' ) . "'",
				Date::KEY_TYPE => FieldType::DATE
			];
		}
		$readerParams = new ReaderParams(
			[ ReaderParams::PARAM_FILTER => $filter ],
			[ ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE ]
		);
		$res = $this->getStore()->getReader()->read( $readerParams );

		$titles = [];
		foreach ( $res->getRecords() as $record ) {
			$this->appendCache( $record );
			$title = Title::newFromID( $record->get( Record::PAGE_ID, 0 ) );
			if ( !$title || !$title->isValid() || !$title->exists() ) {
				continue;
			}
			$titles[] = $title;
		}
		return $titles;
	}

	/**
	 *
	 * @param Record $record
	 */
	protected function appendCache( Record $record ) {
		$this->targetCache[ $record->get( Record::PAGE_ID ) ] = $record;
	}

	/**
	 *
	 * @param Title $title
	 * @return Record|false
	 */
	protected function fromCache( Title $title ) {
		if ( isset( $this->targetCache[$title->getArticleID()] ) ) {
			return $this->targetCache[$title->getArticleID()];
		}
		return false;
	}

	/**
	 *
	 * @param Title $title
	 * @return true
	 */
	public function invalidate( Title $title ) {
		if ( isset( $this->targetCache[$title->getArticleID()] ) ) {
			unset( $this->targetCache[$title->getArticleID()] );
		}
		return true;
	}

	/**
	 *
	 * @return Store
	 */
	protected function getStore() {
		return new Store();
	}
}
