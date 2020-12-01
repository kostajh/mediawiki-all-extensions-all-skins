<?php

/**
 * WikiPage class for BSSocial
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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BSSocialWikiPage
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\WikiPage\Entity;

use BlueSpice\Social\Entity\Page;
use BsNamespaceHelper;
use Exception;
use Message;
use ParserOptions;
use Status;
use Title;
use User;
use WikiPage as Article;

/**
 * WikiPage class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BSSocialWikiPage
 */
class WikiPage extends Page {
	const TYPE = 'wikipage';

	const ATTR_WIKI_PAGE_ID = 'wikipageid';
	const ATTR_NAMESPACE = 'namespace';
	const ATTR_TITLE_TEXT = 'titletext';

	/**
	 *
	 * @var string
	 */
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
		$wikiPage = Article::factory( $this->getRelatedTitle() );
		try {
			$output = $wikiPage->getContent()->getParserOutput(
				$this->getRelatedTitle(),
				null,
				ParserOptions::newFromContext( \RequestContext::getMain() ),
				true,
				true
			);
		} catch ( Exception $e ) {
			// sometimes parser recursion - unfortunately this can not be solved
			// due to the randomnes of the content model -.-
			$output = null;
		}

		if ( !$output ) {
			return $this->baseTitleContent;
		}
		$this->baseTitleContent = $output->getText();
		return $this->baseTitleContent;
	}

	/**
	 * Gets the BSSociaEntityPage attributes formated for the api
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
				static::ATTR_NAMESPACE => $this->get(
					static::ATTR_NAMESPACE,
					0
				),
				static::ATTR_TITLE_TEXT => $this->get(
					static::ATTR_TITLE_TEXT,
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
		if ( !empty( $o->{static::ATTR_WIKI_PAGE_ID} ) ) {
			$this->set(
				static::ATTR_WIKI_PAGE_ID,
				$o->{static::ATTR_WIKI_PAGE_ID}
			);
		}
		if ( isset( $o->{static::ATTR_NAMESPACE} ) ) {
			$this->set(
				static::ATTR_NAMESPACE,
				$o->{static::ATTR_NAMESPACE}
			);
		}
		if ( isset( $o->{static::ATTR_TITLE_TEXT} ) ) {
			$this->set(
				static::ATTR_TITLE_TEXT,
				$o->{static::ATTR_TITLE_TEXT}
			);
		}
		parent::setValuesByObject( $o );
	}

	/**
	 *
	 * @param Messag|null $msg
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
		if ( empty( $this->get( static::ATTR_WIKI_PAGE_ID, 0 ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_WIKI_PAGE_ID )->plain()
			) );
		}
		if ( !$this->getRelatedTitle()
			|| $this->getRelatedTitle()->isTalkPage()
			|| !$this->getRelatedTitle()->exists() ) {
			return Status::newFatal( wfMessage(
				'bs-socialwikipage-entity-fatalstatus-save-novalidpage'
			) );
		}
		$this->set(
			static::ATTR_TITLE_TEXT,
			$this->getRelatedTitle()->getFullText()
		);
		return parent::save( $user, $options );
	}
}
