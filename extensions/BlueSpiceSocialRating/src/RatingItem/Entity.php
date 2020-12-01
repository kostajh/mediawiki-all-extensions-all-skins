<?php
/**
 * Entity class for extension Rating
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
 * @subpackage BlueSpiceSocialRating
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\Rating\RatingItem;

use BlueSpice\Rating\RatingItem;
use BlueSpice\Social\Entity as SocialEntity;
use MediaWiki\MediaWikiServices;

/**
 * Entity class for Rating extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocialRating
 */
class Entity extends RatingItem {
	protected $refType = 'bssocial';

	/**
	 *
	 * @return SocialEntity
	 */
	public function getEntity() {
		$factory = MediaWikiServices::getInstance()->getService(
			'BSEntityFactory'
		);
		return $factory->newFromID( (int)$this->getRef(), SocialEntity::NS );
	}

	public function jsonSerialize() {
		$aData = parent::jsonSerialize();
		$oStatus = $this->userCan(
			\RequestContext::getMain()->getUser(),
			'update',
			$this->getEntity()->getRelatedTitle()
		);
		$aData['usercanmodify'] = $oStatus->isOK();
		return $aData;
	}

	/**
	 *
	 * @param \User $oUser
	 * @param mixed $mValue
	 * @param \User|null $oOwner
	 * @param int $iContext
	 * @param \Title|null $oTitle
	 * @return \Status
	 */
	public function vote( \User $oUser, $mValue, \User $oOwner = null, $iContext = 0,
		\Title $oTitle = null ) {
		if ( !$oTitle instanceof \Title ) {
			$oTitle = $this->getEntity()->getRelatedTitle();
		}
		$oStatus = parent::vote( $oUser, $mValue, $oOwner, $iContext, $oTitle );
		if ( !$oStatus->isOK() ) {
			return $oStatus;
		}

		$entity = $this->getEntity();
		if ( !$entity instanceof SocialEntity ) {
			// TODO:: msg
			return \Status::newFatal( 'invalid Entity' );
		}
		$entity->invalidateCache();
		return $oStatus;
	}
}
