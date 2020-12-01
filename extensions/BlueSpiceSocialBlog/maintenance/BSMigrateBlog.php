<?php

$extDir = dirname( dirname( __DIR__ ) );

require_once "$extDir/BlueSpiceFoundation/maintenance/BSMaintenance.php";

use BlueSpice\Social\Blog\Entity\Blog;
use MediaWiki\MediaWikiServices;

class BSMigrateBlog extends LoggedUpdateMaintenance {

	public function __construct() {
		parent::__construct();
		// we hope, that the default blog namespace was used or the current blog
		// namespace is still defined in any configuration file!
		global $wgExtraNamespaces;
		if ( !defined( 'NS_BLOG' ) ) {
			define( 'NS_BLOG', 1502 );
			$wgExtraNamespaces[NS_BLOG] = 'Blog';
			$GLOBALS['bsgSystemNamespaces'][1502] = 'NS_BLOG';
		}

		if ( !defined( 'NS_BLOG_TALK' ) ) {
			define( 'NS_BLOG_TALK', 1503 );
			$wgExtraNamespaces[NS_BLOG_TALK] = 'Blog_talk';
			$GLOBALS['bsgSystemNamespaces'][1503] = 'NS_BLOG_TALK';
		}
	}

	protected $data = [];

	protected function readData() {
		$res = $this->getDB( DB_REPLICA )->select(
			'page',
			'*',
			[ 'page_namespace' => NS_BLOG ]

		);
		foreach ( $res as $row ) {
			$this->data[$row->page_id] = $row;
		}
	}

	/**
	 *
	 * @return bool
	 */
	protected function doDBUpdates() {
		$this->output( "...bs_blog -> migration...\n" );

		$this->readData();
		$this->output( count( $this->data ) . "blogs\n" );
		foreach ( $this->data as $articleId => $blog ) {
			$this->output( "." );
			$title = Title::newFromRow( $blog );

			$entity = $this->makeEntity( $title );
			if ( !$entity ) {
				$this->output( "Blog could not be created" );
				continue;
			}
			try {
				$status = $entity->save(
					$this->getMaintenanceUser()
				);
			} catch ( Exception $e ) {
				$this->output( $e->getMessage() );
				continue;
			}
			if ( !$status->isOK() ) {
				$this->output( $status->getMessage() );
				continue;
			}
			$this->modifySourceTitleTimestamp(
				$entity->getTitle(),
				$title
			);
		}
		$this->output( "\n" );
		return true;
	}

	/**
	 *
	 * @param Title $title
	 * @return Blog
	 */
	protected function makeEntity( Title $title ) {
		$user = $this->extractUser( $title );
		if ( !$user ) {
			$user = $this->getMaintenanceUser();
		}
		try {
			$entity = $this->getFactory()->newFromObject( (object)[
				Blog::ATTR_TYPE => Blog::TYPE,
				Blog::ATTR_BLOG_TITLE => $title->getText(),
				Blog::ATTR_OWNER_ID => $user->getId(),
				Blog::ATTR_TEXT => \BsPageContentProvider::getInstance()
					->getWikiTextContentFor( $title )
			] );
		} catch ( Exception $e ) {
			$this->output( $e->getMessage() );
			return null;
		}
		return $entity;
	}

	/**
	 *
	 * @param Title $title
	 * @return User
	 */
	protected function extractUser( Title $title ) {
		return User::newFromID( $title->getFirstRevision()->getUser() );
	}

	/**
	 *
	 * @return EntityFactory
	 */
	protected function getFactory() {
		return MediaWikiServices::getInstance()->getService( 'BSEntityFactory' );
	}

	/**
	 *
	 * @param Title $title
	 * @param Title $origTitle
	 * @return bool
	 */
	protected function modifySourceTitleTimestamp( $title, $origTitle ) {
		if ( !$title || empty( $title->getLatestRevID() ) ) {
			return false;
		}

		// dont use any MWTimestamp here, as they are not reliably in cmd!
		$date = DateTime::createFromFormat(
			'YmdHis',
			$origTitle->getEarliestRevTime()
		);
		$ts = $date->format( 'YmdHis' );
		if ( !$date || !$ts ) {
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
		return 'bs_blog-migration2';
	}

}
