<?php

/**
 * File class for BlueSpiceSocial
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

use File;
use MediaWiki\MediaWikiServices;
use Status;
use User;

/**
 * File class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class ActionFile extends ActionTitle {
	const ATTR_FILE_NAME = 'filename';
	const ATTR_FILE_TIMESTAMP = 'filetimestamp';

	protected $iNamespace = NS_FILE;

	/**
	 *
	 * @param string $attrName
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get( $attrName, $default = null ) {
		// files must be in file namespace
		if ( $attrName == static::ATTR_NAMESPACE ) {
			return NS_FILE;
		}
		return parent::get( $attrName, $default );
	}

	/**
	 *
	 * @param string $attrName
	 * @param mixed $value
	 * @return ActionEntity
	 */
	public function set( $attrName, $value ) {
		// files must be in file namespace
		if ( $attrName == static::ATTR_NAMESPACE ) {
			return parent::set( $attrName, NS_FILE );
		}
		return parent::set( $attrName, $value );
	}

	/**
	 *
	 * @param int $iNamespace
	 * @return ActionFile
	 */
	public function setNamespace( $iNamespace ) {
		$this->iNamespace = NS_FILE;
		return $this->setUnsavedChanges();
	}

	/**
	 *
	 * @param array $a
	 * @return array
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_FILE_NAME => $this->get(
					static::ATTR_FILE_NAME,
					''
				),
				static::ATTR_FILE_TIMESTAMP => $this->get(
					static::ATTR_FILE_TIMESTAMP,
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
		if ( isset( $o->{static::ATTR_FILE_NAME} ) ) {
			$this->set(
				static::ATTR_FILE_NAME,
				$o->{static::ATTR_FILE_NAME}
			);
		}
		if ( isset( $o->{static::ATTR_FILE_TIMESTAMP} ) ) {
			$this->set(
				static::ATTR_FILE_TIMESTAMP,
				$o->{static::ATTR_FILE_TIMESTAMP}
			);
		}
		parent::setValuesByObject( $o );
	}

	/**
	 *
	 * @return File|null
	 */
	public function getRelatedFile() {
		return MediaWikiServices::getInstance()->getRepoGroup()
			->findFile( $this->getRelatedTitle(), [
				'time' => $this->get( static::ATTR_FILE_TIMESTAMP, '' )
			] );
	}

	/**
	 *
	 * @param User|null $oUser
	 * @param array $aOptions
	 * @return Status
	 */
	public function save( User $oUser = null, $aOptions = [] ) {
		if ( empty( $this->get( static::ATTR_FILE_NAME, '' ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_FILE_NAME )->plain()
			) );
		}
		if ( empty( $this->get( static::ATTR_FILE_TIMESTAMP, '' ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_FILE_TIMESTAMP )->plain()
			) );
		}
		return parent::save( $oUser, $aOptions );
	}
}
