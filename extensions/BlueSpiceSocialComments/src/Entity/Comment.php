<?php

/**
 * BSSociaEntityComment class for BSSocial
 *
 * add desc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License.
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
 * @subpackage BSSocialMicroBlog
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Comments\Entity;

use BlueSpice\Data\Entity\IStore;
use BlueSpice\EntityConfig;
use BlueSpice\EntityFactory;
use BlueSpice\Social\Entity\Text;
use Status;
use User;

/**
 * Comment class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BSSocialMicroBlog
 */
class Comment extends Text {
	const TYPE = 'comment';

	/**
	 * Returns the instance - Should not be used directly. Use mediawiki service
	 * 'BSEntityFactory' instead
	 * @param \stdClass $data
	 * @param EntityConfig $config
	 * @param IStore $store
	 * @param EntityFactory|null $entityFactory
	 * @return \static
	 */
	public static function newFromFactory( \stdClass $data, EntityConfig $config,
		IStore $store, EntityFactory $entityFactory = null ) {
		if ( !$entityFactory ) {
			$entityFactory = Services::getInstance()->getService(
				'BSEntityFactory'
			);
		}
		$instance = new static( $data, $config, $entityFactory, $store );
		// Dealing with currupted entities, whenever a proccess or - more likely
		// a human breaks stuff by deleting, moving, protecting... etc. source
		// titles
		if ( $instance->get( static::ATTR_PARENT_ID, 0 ) < 1 ) {
			return null;
		}
		if ( !$instance->getParent() ) {
			return null;
		}
		return $instance;
	}

	/**
	 *
	 * @param array $actions
	 * @param User|null $user
	 * @return array
	 */
	public function getActions( array $actions = [], User $user = null ) {
		$actions = parent::getActions( $actions, $user );
		if ( isset( $action['create'] ) ) {
			if ( !$this->hasParent() ) {
				// Comments must have a parrent
				unset( $actions[ 'create'] );
			}
		}
		return $actions;
	}

	/**
	 *
	 * @param User|null $user
	 * @param array $options
	 * @return Status
	 */
	public function save( User $user = null, $options = [] ) {
		if ( !$this->getParent() || !$this->getParent()->exists() ) {
			return Status::newFatal(
				'bs-socialcomments-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_PARENT_ID )->plain()
			);
		}
		return parent::save( $user, $options );
	}

	/**
	 * Returns the Message object for the entity header
	 * @param Message|null $msg
	 * @return Message
	 */
	public function getHeader( $msg = null ) {
		$msg = parent::getHeader( $msg );
		if ( !$this->exists() ) {
			return $msg;
		}

		return $msg->params( [
			$this->getParent()->getTitle()->getFullText(),
			strip_tags( $this->getParent()->getHeader()->parse() )
		] );
	}
}
