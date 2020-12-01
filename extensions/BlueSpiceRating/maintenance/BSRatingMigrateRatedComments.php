<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );
require_once "$IP/maintenance/Maintenance.php";

use BlueSpice\Rating\Data\Record;
use BlueSpice\Rating\RatingFactory;
use BlueSpice\Rating\RatingItem\Article;
use MediaWiki\MediaWikiServices;

class BSRatingMigrateRatedComments extends LoggedUpdateMaintenance {

	protected function noDataToMigrate() {
		return $this->getDB( DB_REPLICA )->tableExists( 'bs_rating' ) === false;
	}

	protected $data = [];

	protected function readData() {
		$res = $this->getDB( DB_REPLICA )->select(
			'bs_rating',
			'*',
			[ 'rat_reftype = "rcarticle"' ]

		);
		foreach ( $res as $row ) {
			$this->data[$row->rat_ref][] = $row;
		}
	}

	protected function doDBUpdates() {
		if ( $this->noDataToMigrate() ) {
			$this->output( "bs_rating-migrateratedcomments -> No data to migrate\n" );
			return true;
		}
		$this->output( "...bs_rating-migrateratedcomments -> migration...\n" );

		$this->readData();
		foreach ( $this->data as $articleId => $ratings ) {
			// article does not exists anymore => ignore ratings
			$title = \Title::newFromID( (int)$articleId );
			if ( !$title ) {
				continue;
			}
			$ratingItem = $this->makeRatingItem( $title );
			if ( !$ratingItem ) {
				continue;
			}
			$this->output(
				"\n{$title->getArticleID()}... " . count( $ratings ) . " ratings"
			);
			foreach ( $ratings as $rating ) {
				if ( empty( $rating->rat_value ) ) {
					// just ignore what ever happend there ^^
					continue;
				}
				$this->output( "." );

				$user = User::newFromId( $rating->rat_userid );
				if ( !$user ) {
					$this->output( "No User - skip" );
					continue;
				}
				if ( $this->userVoted( $user, $ratingItem ) ) {
					$this->output(
						"User voted - skip"
					);
					continue;
				}
				try {
					$status = $ratingItem->vote(
						$this->getMaintenanceUser(),
						$rating->rat_value,
						$user,
						0,
						$title
					);
				} catch ( \Exception $e ) {
					$this->output( $e->getMessage() );
					continue;
				}
				if ( !$status->isOK() ) {
					$this->output( $status->getMessage() );
					continue;
				}
			}
		}
		$this->output( "\n" );

		return true;
	}

	/**
	 *
	 * @param User $user
	 * @param Article $ratingItem
	 * @return bool
	 */
	protected function userVoted( User $user, Article $ratingItem ) {
		$ratings = $ratingItem->getRatingSet()->getUserRatings( $user );
		return !empty( $ratings );
	}

	/**
	 *
	 * @param \Title $title
	 * @return article
	 */
	protected function makeRatingItem( \Title $title ) {
		$ratingItem = $this->getRatingFactory()->newFromObject( (object)[
			Record::CONTEXT => 0,
			Record::REFTYPE => 'article',
			Record::REF => $title->getArticleID(),
			Record::SUBTYPE => '',
		] );
		if ( !$ratingItem ) {
			$this->output( "Rating item could not be created" );
			return null;
		}
		return $ratingItem;
	}

	/**
	 *
	 * @return RatingFactory
	 */
	protected function getRatingFactory() {
		return MediaWikiServices::getInstance()->getService(
			'BSRatingFactory'
		);
	}

	/**
	 *
	 * @return User
	 */
	protected function getMaintenanceUser() {
		return MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_rating-migrateratedcomments';
	}

}
