<?php

/**
 * BSSociaEntityProfile class for BSSocial
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
 * @subpackage BlueSpiceSocialProfile
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Profile\Entity;

use BlueSpice\Social\Entity\Page;
use BlueSpice\Social\Profile\ICustomField;
use BlueSpice\Social\Profile\IField;
use Exception;
use MediaWiki\MediaWikiServices;
use Message;
use ParserOptions;
use RequestContext;
use Status;
use Title;
use User;
use WikiPage;

/**
 * BSSociaEntityProfile class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocialProfile
 */
class Profile extends Page {
	const TYPE = 'profile';

	/**
	 *
	 * @var string
	 */
	protected $baseTitleContent = null;

	/**
	 * @param \stdClass $data
	 * @return Profile
	 */
	public function setValuesByObject( \stdClass $data ) {
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileCustomFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		foreach ( $fieldDefinitions as $name => $definition ) {
			if ( isset( $data->{$name} ) ) {
				$this->set( $name, $data->{$name} );
			}
		}
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileFieldsFactory'
		);
		foreach ( $fieldDefinitions as $name => $definition ) {
			if ( isset( $data->{$name} ) ) {
				$this->set( $name, $data->{$name} );
			}
		}
		return parent::setValuesByObject( $data );
	}

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
		$wikiPage = WikiPage::factory( $this->getRelatedTitle() );
		try {
			$output = $wikiPage->getContent()->getParserOutput(
				$this->getRelatedTitle(),
				null,
				ParserOptions::newFromContext( RequestContext::getMain() ),
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
	 * Returns the Message object for the entity header
	 * @param Message|null $msg
	 * @return Message
	 */
	public function getHeader( $msg = null ) {
		$msg = parent::getHeader( $msg );
		return $msg->params( [
			$this->getRelatedTitle()->getFullText()
		] );
	}

	/**
	 * Gets the BSSociaEntityPage attributes formated for the api
	 * @param array $a
	 * @return \stdClass
	 */
	public function getFullData( $a = [] ) {
		$fields = [];
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileFieldsFactory'
		);
		foreach ( $fieldDefinitions as $name => $definition ) {
			$field = $factory->factory( $name, $this->getOwner() );
			if ( !$field instanceof IField ) {
				continue;
			}
			$fields[$name] = $this->get( $name );
		}
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileCustomFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		foreach ( $fieldDefinitions as $name => $definition ) {
			$field = $factory->factory( $name, $this->getOwner() );
			if ( !$field instanceof ICustomField ) {
				continue;
			}
			$fields[$name] = $this->get( $name, $field->getDefault() );
		}
		return parent::getFullData( array_merge( $a, $fields ) );
	}

	/**
	 *
	 * @return Title
	 */
	public function getRelatedTitle() {
		if ( $this->relatedTitle ) {
			return $this->relatedTitle;
		}
		$this->relatedTitle = $this->getOwner()->getUserPage();
		return $this->relatedTitle instanceof Title
			? $this->relatedTitle
			: parent::getRelatedTitle();
	}

	/**
	 *
	 * @param array $actions
	 * @param User|null $user
	 * @return string
	 */
	public function getActions( array $actions = [], User $user = null ) {
		if ( !$user ) {
			$user = RequestContext::getMain()->getUser();
		}
		$actions = parent::getActions( $actions, $user );

		if ( isset( $actions['edit'] ) ) {
			// replace with editprofilefields and edituserpage
			unset( $actions['edit'] );
		}
		$status = $this->userCan( 'editothers', $user );
		if ( $this->userIsOwner( $user ) || $status->isOK() ) {
			$actions['changeimage'] = [];
			$actions['editprofilefields'] = [];
		}
		if ( $this->getRelatedTitle() ) {
			$editUsrPg = MediaWikiServices::getInstance()->getPermissionManager()->userCan(
				'edit',
				$user,
				$this->getRelatedTitle()
			);
			if ( $editUsrPg ) {
				$actions['edituserpage'] = [];
			}
		}
		if ( $status->isOK() ) {
			$actions['edithiddenfields'] = [];
		}
		return $actions;
	}

	/**
	 *
	 * @param string $type
	 * @param bool $noCache
	 * @return string
	 */
	public function render( $type = 'Default', $noCache = false ) {
		if ( $type == 'Page' && !$noCache ) {
			$noCache = true;
		}
		return parent::render( $type, $noCache );
	}

	/**
	 *
	 * @param User|null $user
	 * @param array $options
	 * @return Status
	 */
	public function save( User $user = null, $options = [] ) {
		if ( !$this->getOwner() || $this->getOwner()->isAnon() ) {
			return Status::newFatal( wfMessage(
				'bs-socialprofile-entity-fatalstatus-save-invaliduser'
			) );
		}
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileCustomFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		foreach ( $fieldDefinitions as $name => $definition ) {
			$field = $factory->factory( $name, $this->getOwner() );
			if ( !$field instanceof ICustomField ) {
				continue;
			}
			$status = $field->validate(
				$this->get( $name, $field->getDefault() )
			);
			if ( !$status->isOK() ) {
				return $status;
			}
			$this->set( $name, $status->getValue() );
		}
		return parent::save( $this->getOwner(), $options );
	}
}
