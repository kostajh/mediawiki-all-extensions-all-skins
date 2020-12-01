<?php

/**
 * BSSociaEntityText class for BlueSpiceSocial
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
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\Entity;

use BlueSpice\Social\Entity;
use BsPageContentProvider;
use ParserOptions;
use ParserOutput;
use RequestContext;
use Status;
use Title;
use User;

/**
 * Text class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class Text extends Entity {
	const TYPE = 'text';

	const ATTR_TEXT = 'text';
	const ATTR_PARSED_TEXT = 'parsedtext';
	const ATTR_ATTACHMENTS = 'attachments';

	protected $oParserOutput = null;

	/**
	 * Gets the BSSociaEntityText attributes formated for the api
	 * @param array $a
	 * @return object
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_TEXT => $this->get(
					static::ATTR_TEXT,
					''
				),
				static::ATTR_PARSED_TEXT => $this->get(
					static::ATTR_PARSED_TEXT,
					''
				),
				static::ATTR_ATTACHMENTS => $this->get(
					static::ATTR_ATTACHMENTS,
					[]
				),
			]
		) );
	}

	/**
	 *
	 * @param string $attrName
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get( $attrName, $default = null ) {
		if ( $attrName === static::ATTR_TEXT && empty( parent::get( $attrName, '' ) ) ) {
			if ( empty( $this->get( static::ATTR_PRELOAD, '' ) ) ) {
				return parent::get( $attrName, '' );
			}
			$title = Title::newFromText(
				$this->get( static::ATTR_PRELOAD, '' )
			);
			if ( !$title || !$title->exists() ) {
				return parent::get( $attrName, '' );
			}

			$pm = \MediaWiki\MediaWikiServices::getInstance()->getPermissionManager();
			$user = RequestContext::getMain()->getUser();
			if ( !$pm->userCan( 'read', $user, $title ) ) {
				return parent::get( $attrName, '' );
			}
			return BsPageContentProvider::getInstance()->getWikiTextContentFor(
				$title
			);
		}

		if ( $attrName === static::ATTR_PARSED_TEXT ) {
			if ( empty( $this->get( static::ATTR_TEXT, '' ) ) ) {
				return '';
			}
			if ( empty( $this->attributes[static::ATTR_PARSED_TEXT] ) ) {
				$this->attributes[static::ATTR_PARSED_TEXT]
					= $this->getParserOutput()->getText( [
						'enableSectionEditLinks' => false,
						'allowTOC' => false,
					] );
			}
		}

		if ( $attrName === static::ATTR_ATTACHMENTS ) {
			$availableAttachments = $this->getConfig()->get(
				'AvailableAttachments'
			);
			if ( empty( $availableAttachments ) ) {
				return $default;
			}
			if ( empty( $this->attributes[static::ATTR_ATTACHMENTS] ) ) {
				$this->attributes[static::ATTR_ATTACHMENTS] = [];
				// TODO: Attachment handler class
				if ( in_array( 'images', $availableAttachments ) ) {
					$this->attributes[static::ATTR_ATTACHMENTS]['images']
						= $this->getAttachmentImages();
				}
				if ( in_array( 'links', $availableAttachments ) ) {
					$this->attributes[static::ATTR_ATTACHMENTS]['links']
						= $this->getAttachmentLinks();
				}
			}
		}

		return parent::get( $attrName, $default );
	}

	/**
	 * Returns the attachments of type images as array
	 * @return array
	 */
	protected function getAttachmentImages() {
		$images = array_keys( $this->getParserOutput()->getImages() );
		return $images;
	}

	/**
	 * Returns the attachments of type images as array
	 * @return array
	 */
	protected function getAttachmentLinks() {
		$links = [];
		if ( empty( $this->getParserOutput()->getLinks()[0] ) ) {
			return $links;
		}
		foreach ( $this->getParserOutput()->getLinks() as $maybeSection ) {
			// make own loop, because there is some weired reference stuff going on!
			foreach ( $maybeSection as $name => $id ) {
				$links[] = $name;
			}
		}
		return $links;
	}

	/**
	 * @return ParserOutput
	 */
	public function getParserOutput() {
		if ( isset( $this->oParserOutput ) && $this->oParserOutput !== null ) {
			return $this->oParserOutput;
		}

		$sClass = $this->getConfig()->get( 'ParserClass' );
		$oParser = new $sClass();
		$this->oParserOutput = $oParser->parse(
			html_entity_decode( $this->get( static::ATTR_TEXT, '' ) ),
			$this->getTitle(),
			$this->getParserOptions()
		);
		return $this->oParserOutput;
	}

	/**
	 * @return ParserOptions
	 */
	public function getParserOptions() {
		$oUser = RequestContext::getMain()->getUser();
		return ParserOptions::newFromUser( $oUser );
	}

	/**
	 *
	 * @param \stdClass $o
	 */
	public function setValuesByObject( \stdClass $o ) {
		if ( isset( $o->{static::ATTR_TEXT} ) ) {
			$this->set( static::ATTR_TEXT, $o->{static::ATTR_TEXT} );
		}
		parent::setValuesByObject( $o );
	}

	/**
	 *
	 * @param User|null $oUser
	 * @param array $aOptions
	 * @return Status
	 */
	public function save( User $oUser = null, $aOptions = [] ) {
		if ( empty( $this->get( static::ATTR_TEXT, '' ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_TEXT )->plain()
			) );
		}
		return parent::save( $oUser, $aOptions );
	}

	public function invalidateCache() {
		$this->oParserOutput = null;
		if ( isset( $this->attributes[static::ATTR_ATTACHMENTS] ) ) {
			unset( $this->attributes[static::ATTR_ATTACHMENTS] );
		}
		if ( isset( $this->attributes[static::ATTR_PARSED_TEXT] ) ) {
			unset( $this->attributes[static::ATTR_PARSED_TEXT] );
		}
		return parent::invalidateCache();
	}
}
