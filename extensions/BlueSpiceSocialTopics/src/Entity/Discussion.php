<?php

/**
 * BSSociaEntityDiscussion class for BSSocial
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
 * @subpackage BSSocialDiscussion
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Topics\Entity;

use BlueSpice\Social\Entity\Page;
use BsNamespaceHelper;
use Exception;
use Message;
use ParserOptions;
use RequestContext;
use Status;
use Title;
use User;
use WikiPage;

/**
 * BSSociaEntityDiscussion class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BSSocialDiscussion
 */
class Discussion extends Page {
	const TYPE = 'discussion';

	const ATTR_DISCUSSION_TITLE_ID = 'discussiontitleid';

	protected $baseTitleContent = null;

	/**
	 *
	 * @return string
	 */
	public function getBaseTitleContent() {
		if ( $this->baseTitleContent ) {
			return $this->baseTitleContent;
		}
		$this->baseTitleContent = '';

		if ( !$this->getRelatedTitle()->exists() ) {
			return $this->baseTitleContent;
		}
		$oWikiPage = WikiPage::factory( $this->getRelatedTitle() );
		try {
			$oOutput = $oWikiPage->getContent()->getParserOutput(
				$this->getRelatedTitle(),
				null,
				ParserOptions::newFromContext( RequestContext::getMain() ),
				true,
				true
			);
		} catch ( Exception $e ) {
			// sometimes parser recursion - unfortunately this can not be solved
			// due to the randomnes of the content model -.-
			$oOutput = null;
		}

		if ( !$oOutput ) {
			return $this->baseTitleContent;
		}
		$this->baseTitleContent = $oOutput->getText();
		return $this->baseTitleContent;
	}

	/**
	 * Gets the BSSociaEntityPage attributes formated for the api
	 * @param array $a
	 * @return \stdClass
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_DISCUSSION_TITLE_ID => $this->get(
					static::ATTR_DISCUSSION_TITLE_ID,
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
		if ( !empty( $o->{static::ATTR_DISCUSSION_TITLE_ID} ) ) {
			$this->set(
				static::ATTR_DISCUSSION_TITLE_ID,
				$o->{static::ATTR_DISCUSSION_TITLE_ID}
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
	 * @param User|null $oUser
	 * @param array $aOptions
	 * @return Status
	 */
	public function save( User $oUser = null, $aOptions = [] ) {
		if ( empty( $this->get( static::ATTR_DISCUSSION_TITLE_ID, 0 ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_DISCUSSION_TITLE_ID )->plain()
			) );
		}
		$title = Title::newFromID( $this->get( static::ATTR_DISCUSSION_TITLE_ID, 0 ) );
		if ( !$title || !$title->exists() || !$title->isTalkPage() ) {
			return Status::newFatal( wfMessage(
				'bs-socialtopics-entity-fatalstatus-save-notalkpage'
			) );
		}
		return parent::save( $oUser, $aOptions );
	}
}
