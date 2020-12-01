<?php
/**
 * Item class for extension Resolve
 *
 * Provides a rating item.
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
 * @subpackage BlueSpiceSocialResolve
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\Resolve;

use BlueSpice\Renderer;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Entity;
use BlueSpice\Social\Resolve\Renderer\Resolve;
use Config;
use MediaWiki\MediaWikiServices;
use RequestContext;
use Status;
use User;

class Item implements \JsonSerializable {

	/**
	 *
	 * @var Entity
	 */
	protected $entity = null;

	/**
	 *
	 * @var bool
	 */
	protected $resolved = false;

	/**
	 *
	 * @param Entity $entity
	 */
	public function __construct( Entity $entity ) {
		$this->entity = $entity;
		$this->resolved = $entity->get( 'resolved', false );
	}

	/**
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		$data = [];
		$oStatus = $this->getEntity()->userCan(
			'resolve',
			RequestContext::getMain()->getUser()
		);
		$data['usercanresolve'] = $oStatus->isOK();
		$data['id'] = $this->getEntity()->get( Entity::ATTR_ID, 0 );
		$data['resolved'] = $this->isResolved();
		return $data;
	}

	/**
	 *
	 * @return Entity
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 *
	 * @return Config
	 */
	public function getConfig() {
		return $this->getEntity()->getConfig();
	}

	/**
	 *
	 * @return bool
	 */
	public function isResolved() {
		return $this->resolved ? true : false;
	}

	/**
	 *
	 * @param User $user
	 * @param bool $value
	 * @return Status
	 */
	public function resolve( User $user, $value ) {
		if ( !$this->getEntity() instanceof Entity ) {
			// TODO:: msg
			return Status::newFatal( 'invalid Item' );
		}
		$value = $value ? true : false;
		$status = $this->getEntity()->userCan( 'resolve', $user );
		if ( !$status->isOK() ) {
			return $status;
		}

		$this->getEntity()->set( 'resolved', $value );
		$status = $this->getEntity()->save( $user );
		if ( !$status->isOK() ) {
			return $status;
		}

		return Status::newGood( $this );
	}

	/**
	 *
	 * @param User|null $user
	 * @return Renderer
	 */
	public function getRenderer( User $user = null ) {
		if ( !$user ) {
			$user = RequestContext::getMain()->getUser();
		}
		return MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'entityresolve',
			new Params( [
				Resolve::PARAM_RESOLVE_ITEM => $this,
				Resolve::PARAM_USER => $user
			] )
		);
	}
}
