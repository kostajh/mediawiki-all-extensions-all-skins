<?php

/**
 * Title class for BlueSpiceSocial
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

use BsNamespaceHelper;
use Message;
use Status;
use Title;
use User;

/**
 * Title class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class ActionTitle extends Action {
	const ATTR_NAMESPACE = 'namespace';
	const ATTR_TITLE_TEXT = 'titletext';

	/**
	 *
	 * @param Message|null $oMsg
	 * @return Message
	 */
	public function getHeader( $oMsg = null ) {
		return parent::getHeader( $oMsg )->params(
			$this->getRelatedTitle()->getPrefixedText(),
			$this->get( static::ATTR_TITLE_TEXT, '' ),
			$this->get( static::ATTR_NAMESPACE, 0 ),
			BsNamespaceHelper::getNamespaceName(
				$this->get( static::ATTR_NAMESPACE, 0 )
			)
		);
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
	 * @return Title
	 */
	public function getRelatedTitle() {
		if ( $this->relatedTitle ) {
			return $this->relatedTitle;
		}

		$this->relatedTitle = Title::makeTitle(
			$this->get( static::ATTR_NAMESPACE, 0 ),
			$this->get( static::ATTR_TITLE_TEXT, '' )
		);
		if ( !$this->relatedTitle ) {
			return parent::getRelatedTitle();
		}
		return $this->relatedTitle;
	}

	/**
	 *
	 * @param User|null $oUser
	 * @param array $aOptions
	 * @return Status
	 */
	public function save( User $oUser = null, $aOptions = [] ) {
		if ( empty( $this->get( static::ATTR_TITLE_TEXT, '' ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_TITLE_TEXT )->plain()
			) );
		}
		return parent::save( $oUser, $aOptions );
	}
}
