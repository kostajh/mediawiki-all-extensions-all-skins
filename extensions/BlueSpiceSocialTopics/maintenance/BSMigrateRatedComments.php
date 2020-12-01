<?php

$extDir = dirname( dirname( __DIR__ ) );

require_once "$extDir/BlueSpiceFoundation/maintenance/BSMaintenance.php";

use BlueSpice\Social\Topics\Entity\Topic;
use MediaWiki\MediaWikiServices;

class BSMigrateRatedComments extends LoggedUpdateMaintenance {

	/**
	 *
	 * @return bool
	 */
	protected function noDataToMigrate() {
		return $this->getDB( DB_REPLICA )->tableExists( 'bs_shoutbox' ) === false;
	}

	/**
	 *
	 * @var array
	 */
	protected $data = [];

	protected function readData() {
		$res = $this->getDB( DB_REPLICA )->select(
			'bs_shoutbox',
			'*',
			[
				// entries with a title a specific to
				'sb_title != ""',
				// to rated comments and only should be handled here
				'sb_archived = 0'
			]

		);
		foreach ( $res as $row ) {
			$this->data[$row->sb_page_id][] = $row;
		}
	}

	/**
	 *
	 * @return bool
	 */
	protected function doDBUpdates() {
		if ( $this->noDataToMigrate() ) {
			$this->output( "bs_ratedcomments -> No data to migrate\n" );
			return true;
		}
		$this->output( "...bs_ratedcomments -> migration...\n" );

		$this->readData();
		foreach ( $this->data as $articleId => $shouts ) {
			// article does not exists anymore => ignore shouts, as we con not
			// figure out the discussion page
			$title = $this->ensureDiscussionPage( (int)$articleId );
			if ( !$title ) {
				continue;
			}
			foreach ( $shouts as $shout ) {
				$this->output( "." );

				if ( empty( $shout->sb_id ) ) {
					// dont even ask why this is here ^^
					continue;
				}
				$this->output( "\n$shout->sb_id..." );
				$entity = $this->makeEntity( $shout, $title );
				if ( !$entity ) {
					$this->output( "Topic could not be created" );
					continue;
				}
				try {
					$status = $entity->save( $this->getMaintenanceUser() );
				} catch ( \Exception $e ) {
					$this->output( $e->getMessage() );
					continue;
				}
				if ( !$status->isOK() ) {
					$this->output( $status->getMessage() );
					continue;
				}
				$this->modifySourceTitleTimestamp(
					$entity->getTitle(),
					$shout
				);
				$ratings = $this->getRawRatings( $shout );
				$ratingCount = count( $ratings );
				$this->output( "$ratingCount ratings" );
				if ( $ratingCount < 1 ) {
					continue;
				}
				$this->output( "." );
				$this->restoreRatings( $ratings, $shout, $entity, $title );
			}
		}
		$this->output( "\n" );
		return true;
	}

	/**
	 *
	 * @param \stdClass $shout
	 * @return \stdClass[]
	 */
	protected function getRawRatings( $shout ) {
		if ( !$this->getDB( DB_MASTER )->tableExists( 'bs_rating' ) ) {
			// some weird configuration - unlucky
			return [];
		}

		$res = $this->getDB( DB_MASTER )->select(
			'bs_rating',
			'*',
			[ 'rat_reftype' => 'ratedcomments', 'rat_ref' => $shout->sb_id ],
			__METHOD__
		);

		$ratings = [];
		foreach ( $res as $row ) {
			if ( $row->rat_archived > 0 ) {
				continue;
			}
			if ( (int)$row->rat_value !== 1 ) {
				continue;
			}
			$ratings[] = $row;
		}
		return $ratings;
	}

	/**
	 *
	 * @param \stdClass $shout
	 * @param \Title $title
	 * @return Topic
	 */
	protected function makeEntity( $shout, $title ) {
		$user = $this->extractUser( $shout );
		if ( !$user ) {
			$this->output(
				"user from shout $shout->sb_id could not be extracted"
			);
			return null;
		}
		try {
			$entity = $this->getFactory()->newFromObject( (object)[
				Topic::ATTR_TYPE => Topic::TYPE,
				Topic::ATTR_DISCUSSION_TITLE_ID => (int)$title->getArticleID(),
				Topic::ATTR_TOPIC_TITLE => $shout->sb_title,
				Topic::ATTR_OWNER_ID => $user->getId(),
				Topic::ATTR_TEXT => $shout->sb_message
			] );
		} catch ( \Exception $e ) {
			$this->output( $e->getMessage() );
			return null;
		}
		return $entity;
	}

	/**
	 *
	 * @param \stdClass $shout
	 * @return \User
	 */
	protected function extractUser( $shout ) {
		$user = null;
		if ( !empty( $shout->sb_user_id ) ) {
			$user = \User::newFromId( $shout->sb_user_id );
		}
		if ( !$user && !empty( $shout->sb_user_name ) ) {
			$user = \User::newFromName( $shout->sb_user_name );
		}
		return $user;
	}

	/**
	 * @return \BlueSpice\EntityFactory
	 */
	protected function getFactory() {
		return MediaWikiServices::getInstance()->getService( 'BSEntityFactory' );
	}

	/**
	 *
	 * @return \BlueSpice\Social\Rating\RatingFactory\Entity
	 */
	protected function getRatingFactory() {
		return MediaWikiServices::getInstance()->getService( 'BSRatingFactoryEntity' );
	}

	/**
	 *
	 * @param int $articleID
	 * @return \Title | false
	 */
	protected function ensureDiscussionPage( $articleID ) {
		$title = \Title::newFromID( $articleID );
		if ( !$title ) {
			return false;
		}
		if ( $title->getNamespace() === NS_SOCIALENTITY
			|| $title->getNamespace() === NS_SOCIALENTITY_TALK ) {
			return false;
		}
		if ( $title->getTalkPage()->exists() ) {
			return $title->getTalkPage();
		}
		$status = \BlueSpice\Social\Topics\Extension::createDiscussionPage(
			$title->getTalkPage(),
			$this->getMaintenanceUser()
		);
		if ( $status->isOK() ) {
			return $title->getTalkPage();
		}
		$this->output( $title->getTalkPage() . " could not be created" );
		return false;
	}

	/**
	 *
	 * @param \Title $title
	 * @param \stdCLass $shout
	 * @return bool
	 */
	protected function modifySourceTitleTimestamp( $title, $shout ) {
		if ( !$title || empty( $shout->sb_timestamp ) || empty( $title->getLatestRevID() ) ) {
			return false;
		}

		// dont use any MWTimestamp here, as they are not reliably in cmd!
		$date = \DateTime::createFromFormat( 'YmdHis', $shout->sb_timestamp );
		if ( !$date ) {
			return false;
		}
		$ts = $date->format( 'YmdHis' );
		if ( !$ts ) {
			return false;
		}

		// hacky, hope for the best ;)
		return $this->getDB( DB_MASTER )->update(
			'revision',
			[ 'rev_timestamp' => $ts ],
			[ 'rev_id' => $title->getLatestRevID() ],
			__METHOD__
		);
	}

	/**
	 *
	 * @param \stdClass[] $ratings
	 * @param \stdClass $shout
	 * @param Topic $entity
	 * @param \Title $title
	 * @return \BlueSpice\Social\Rating\RatingItem\Entity | null
	 */
	protected function restoreRatings( $ratings, $shout, $entity, $title ) {
		$extRating = MediaWikiServices::getInstance()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceRating' );
		$extSocialRating = MediaWikiServices::getInstance()
			->getService( 'BSExtensionFactory' )->getExtension( 'BSSocialRating' );

		if ( !$extSocialRating || !$extRating ) {
			$this->output( "Required Rating extensions not registered - skip" );
			return null;
		}

		$ratingItem = $this->getRatingFactory()->newFromEntity( $entity );
		if ( !$ratingItem ) {
			$this->output( "Rating item could not be created" );
		}
		foreach ( $ratings as $rating ) {
			$user = \User::newFromId( $rating->rat_userid );

			if ( !$user || $user->isAnon() ) {
				$this->output(
					"user from rating $rating->rat_userid could not be extracted"
				);
				return null;
			}
			try {
				$status = $ratingItem->vote(
					$this->getMaintenanceUser(),
					1,
					$user
				);
			} catch ( \Exception $e ) {
				$this->output( $e->getMessage() );
			}
			if ( !$status->isOK() ) {
				$this->output( $status->getMessage() );
			}
		}
	}

	/**
	 *
	 * @return \User
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
		return 'bs_ratedcomments-migration';
	}

}
