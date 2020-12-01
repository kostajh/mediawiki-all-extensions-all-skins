<?php

namespace BlueSpice\Rating\Data\Item;

use MediaWiki\MediaWikiServices;

abstract class PrimaryDataProvider extends \BlueSpice\Rating\Data\PrimaryDataProvider {

	/**
	 *
	 * @var \ContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 * @param \IContextSource $context
	 */
	public function __construct( $db, $context ) {
		$this->context = $context;
		parent::__construct( $db );
	}

	/**
	 *
	 * @param \BlueSpice\Rating\RatingItem $rating
	 * @return bool
	 */
	protected function checkRatingPermission( $rating ) {
		$user = $this->context->getUser();
		$status = $rating->userCan( $user, 'read' );
		if ( !$status->isOK() ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @param \stdClass $row
	 */
	protected function appendRowToData( $row ) {
		$title = \Title::newFromID( $row->page_id );
		if ( !$title ) {
			return;
		}
		$rating = $this->makeRatingItem( $row );
		if ( !$rating ) {
			return;
		}
		if ( !$this->checkRatingPermission( $rating ) ) {
			return;
		}

		$this->data[] = new \BlueSpice\Data\Record(
			(object)$this->extractDataFromRow( $row, $rating )
		);
	}

	/**
	 *
	 * @param \stdClass $row
	 * @param \BlueSpice\Rating\RatingItem $rating
	 * @return array
	 */
	protected function extractDataFromRow( $row, $rating ) {
		return [
			Record::REFTYPE => $row->{Record::REFTYPE},
			Record::REF => $row->{Record::REF},
			Record::SUBTYPE => $row->{Record::SUBTYPE},
			Record::ITEM => \FormatJson::encode( $rating ),
			Record::CONTENT => $rating->getTag(),
		];
	}

	/**
	 *
	 * @param \stdClass $row
	 * @return \BlueSpice\Rating\RatingItem
	 */
	protected function makeRatingItem( $row ) {
		$factory = MediaWikiServices::getInstance()->getService( 'BSRatingFactory' );
		return $factory->newFromObject( $row );
	}
}
