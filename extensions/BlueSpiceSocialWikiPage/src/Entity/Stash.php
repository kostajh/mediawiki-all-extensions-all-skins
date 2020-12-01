<?php

namespace BlueSpice\Social\WikiPage\Entity;

use BlueSpice\Social\Entity\Text;
use BsNamespaceHelper;
use Exception;
use MediaWiki\MediaWikiServices;
use Status;
use Title;
use User;

/**
 * Stash class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BSSocialMicroBlog
 */
class Stash extends Text {
	const TYPE = 'stash';

	const ATTR_WIKI_PAGE_ID = 'wikipageid';

	/**
	 * Gets the attributes formated for the api
	 * @param array $a
	 * @return object
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_WIKI_PAGE_ID => $this->get(
					static::ATTR_WIKI_PAGE_ID,
					0
				),
			]
		) );
	}

	/**
	 *
	 * @param \stdClass $o
	 */
	public function setValuesByObject( \stdClass $o ) {
		if ( !empty( $o->{static::ATTR_WIKI_PAGE_ID} ) ) {
			$this->set(
				static::ATTR_WIKI_PAGE_ID,
				$o->{static::ATTR_WIKI_PAGE_ID}
			);
		}
		parent::setValuesByObject( $o );
	}

	/**
	 *
	 * @param Message|null $msg
	 * @return Message
	 */
	public function getHeader( $msg = null ) {
		$msg = parent::getHeader( $msg );
		return $msg->params( [
			$this->getRelatedTitle()->getText(),
			$this->getRelatedTitle()->getNamespace(),
			BsNamespaceHelper::getNamespaceName(
				$this->getRelatedTitle()->getNamespace()
			),
			$this->getRelatedTitle()->getFullText(),
		] );
	}

	/**
	 *
	 * @return Title
	 */
	public function getRelatedTitle() {
		if ( $this->relatedTitle ) {
			return $this->relatedTitle;
		}
		if ( $this->get( static::ATTR_WIKI_PAGE_ID, 0 ) < 1 ) {
			return parent::getRelatedTitle();
		}
		$this->relatedTitle = Title::newFromID(
			$this->get( static::ATTR_WIKI_PAGE_ID, 0 )
		);
		return $this->relatedTitle instanceof Title
			? $this->relatedTitle
			: parent::getRelatedTitle();
	}

	/**
	 *
	 * @param User|null $user
	 * @param array $options
	 * @return Status
	 */
	public function save( User $user = null, $options = [] ) {
		$title = Title::newFromID(
			$this->get( static::ATTR_WIKI_PAGE_ID, 0 )
		);
		if ( !$title || !$title->exists() ) {
			return Status::newFatal( wfMessage(
				'bs-socialwikipage-entity-fatalstatus-save-invalidtitle'
			) );
		}
		$status = Status::newGood();
		try {
			$factory = MediaWikiServices::getInstance()->getService(
				'BSSocialWikiPageEntityFactory'
			);
			$entity = $factory->newFromTitle( $title );
			if ( !$entity->exists() ) {
				$status = $entity->save( $user );
			}
		} catch ( Exception $e ) {
			return Status::newFatal( $e->getMessage() );
		}
		if ( !$status->isOK() ) {
			return $status;
		}
		return parent::save( $user, $options );
	}

	/**
	 *
	 * @return void
	 */
	public function invalidateCache() {
		parent::invalidateCache();
		$title = Title::newFromID(
			$this->get( static::ATTR_WIKI_PAGE_ID, 0 )
		);
		if ( !$title || !$title->exists() ) {
			return;
		}
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialWikiPageEntityFactory'
		);
		$entity = $factory->newFromTitle( $title );
		if ( $entity && $entity->exists() ) {
			$entity->invalidateCache();
		}
	}

}
