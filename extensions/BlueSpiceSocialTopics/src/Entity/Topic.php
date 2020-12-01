<?php

/**
 * Topic class for BSSocial
 *
 * add desc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocialTopics
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Topics\Entity;

use BlueSpice\Social\Entity\Text;
use BlueSpice\Social\Parser\Input;
use BsNamespaceHelper;
use Exception;
use MediaWiki\MediaWikiServices;
use Message;
use Status;
use Title;
use User;

/**
 * Topic class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BSSocialMicroBlog
 */
class Topic extends Text {
	const TYPE = 'topic';

	const ATTR_DISCUSSION_TITLE_ID = 'discussiontitleid';
	const ATTR_TOPIC_TITLE = 'topictitle';

	/**
	 * Gets the attributes formated for the api
	 * @param array $a
	 * @return \sdtClass
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_DISCUSSION_TITLE_ID => $this->get(
					static::ATTR_DISCUSSION_TITLE_ID,
					0
				),
				static::ATTR_TOPIC_TITLE => $this->get(
					static::ATTR_TOPIC_TITLE,
					''
				),
			]
		) );
	}

	/**
	 *
	 * @param \stdClass $o
	 */
	public function setValuesByObject( \stdClass $o ) {
		if ( !empty( $o->{static::ATTR_DISCUSSION_TITLE_ID} ) ) {
			$this->set(
				static::ATTR_DISCUSSION_TITLE_ID,
				$o->{static::ATTR_DISCUSSION_TITLE_ID}
			);
		}
		if ( !empty( $o->{static::ATTR_TOPIC_TITLE} ) ) {
			$this->set(
				static::ATTR_TOPIC_TITLE,
				$o->{static::ATTR_TOPIC_TITLE}
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
			$this->get( static::ATTR_TOPIC_TITLE, '' ),
			$this->getRelatedTitle()->getFullText(),
			$this->getRelatedContentTitle()->getFullText(),
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
		if ( $this->get( static::ATTR_DISCUSSION_TITLE_ID, 0 ) < 1 ) {
			return parent::getRelatedTitle();
		}
		$this->relatedTitle = Title::newFromID(
			$this->get( static::ATTR_DISCUSSION_TITLE_ID, 0 )
		);
		return $this->relatedTitle instanceof Title
			? $this->relatedTitle
			: parent::getRelatedTitle();
	}

	/**
	 *
	 * @return Title
	 */
	public function getRelatedContentTitle() {
		$title = $this->getRelatedTitle();
		$contentTitle = Title::newFromText(
			$title->getText(),
			$title->getNamespace() - 1
		);
		return $contentTitle;
	}

	/**
	 *
	 * @param User|null $user
	 * @param array $options
	 * @return Status
	 */
	public function save( User $user = null, $options = [] ) {
		$parser = new Input();
		$this->set( static::ATTR_TOPIC_TITLE,
			$parser->parse( $this->get( static::ATTR_TOPIC_TITLE, '' ) )
		);
		if ( empty( $this->get( static::ATTR_TOPIC_TITLE, '' ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_TOPIC_TITLE )->plain()
			) );
		}
		if ( empty( $this->get( static::ATTR_DISCUSSION_TITLE_ID, 0 ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_DISCUSSION_TITLE_ID )->plain()
			) );
		}
		$title = Title::newFromID(
			$this->get( static::ATTR_DISCUSSION_TITLE_ID, 0 )
		);
		if ( !$title || !$title->exists() || !$title->isTalkPage() ) {
			return Status::newFatal( wfMessage(
				'bs-socialtopics-entity-fatalstatus-save-notalkpage'
			) );
		}
		$status = Status::newGood();
		try {
			$factory = MediaWikiServices::getInstance()->getService(
				'BSSocialDiscussionEntityFactory'
			);
			$entity = $factory->newFromDiscussionTitle( $title );
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
			$this->get( static::ATTR_DISCUSSION_TITLE_ID, 0 )
		);
		if ( !$title || !$title->exists() ) {
			return;
		}
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialDiscussionEntityFactory'
		);
		$entity = $factory->newFromDiscussionTitle( $title );
		if ( $entity && $entity->exists() ) {
			$entity->invalidateCache();
		}
	}

}
