<?php

/**
 * Action class for BlueSpiceSocial
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
use MediaWiki\MediaWikiServices;
use Status;
use User;

/**
 * Action class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class Action extends Entity {
	const ATTR_ACTION = 'action';
	const ATTR_SUMMARY = 'summary';

	/**
	 *
	 * @param array $a
	 * @return array
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_ACTION => $this->get( static::ATTR_ACTION, 'create' ),
				static::ATTR_SUMMARY => $this->get( static::ATTR_SUMMARY, '' ),
			]
		) );
	}

	/**
	 *
	 * @param \stdClass $o
	 */
	public function setValuesByObject( \stdClass $o ) {
		if ( isset( $o->{static::ATTR_ACTION} ) ) {
			$this->set( static::ATTR_ACTION, $o->{static::ATTR_ACTION} );
		}
		if ( isset( $o->{static::ATTR_SUMMARY} ) ) {
			$this->set( static::ATTR_SUMMARY, $o->{static::ATTR_SUMMARY} );
		}
		parent::setValuesByObject( $o );
	}

	/**
	 *
	 * @param User|null $user
	 * @param array $options
	 * @return Status
	 */
	public function save( User $user = null, $options = [] ) {
		// always use the maintenance user for auto-created entities to prevent
		// unrealistic edit statistics for users
		$user = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();
		if ( empty( $this->get( static::ATTR_ACTION ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_ACTION )->plain()
			) );
		}
		return parent::save( $user, $options );
	}
}
