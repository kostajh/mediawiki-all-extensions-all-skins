<?php
/**
 * BlueSpiceSocialProfile base extension for BlueSpice
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
namespace BlueSpice\Social\Profile;

use BlueSpice\Social\Profile\Content\Profile as ProfileContent;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\LessVars;

class Extension extends \BlueSpice\Extension {

	/**
	 *
	 */
	public static function onRegistration() {
		global $wgContentHandlers;

		if ( !defined( 'CONTENT_MODEL_BSSOCIALPROFILE' ) ) {
			define( 'CONTENT_MODEL_BSSOCIALPROFILE', 'BSSocialProfile' );
			$wgContentHandlers[CONTENT_MODEL_BSSOCIALPROFILE]
				= "\\BlueSpice\\Social\\Profile\\Content\\ProfileHandler";
		}

		$lessVars = LessVars::getInstance();
		$lessVars->setVar( 'bs-color-lighten-information', '#BABABA' );
	}

	/**
	 *
	 * @param \Title $oTitle
	 * @param string $sAction
	 * @return bool
	 */
	public static function isProfilePage( \Title $oTitle, $sAction = 'view' ) {
		if ( $oTitle->getNamespace() !== NS_USER || $oTitle->isSubpage() ) {
			return false;
		}
		if ( $oTitle->isTalkPage() ) {
			return false;
		}
		if ( \RequestContext::getMain()->getRequest()->getVal( 'classicprofile', false ) ) {
			return false;
		}
		if ( \RequestContext::getMain()->getRequest()->getVal( 'action', 'view' ) != $sAction ) {
			return false;
		}
		return true;
	}

	/**
	 * This is so hacky i cant breathe ^^
	 * @param \Article &$oArticle
	 * @param bool &$outputDone
	 * @param bool &$useParserCache
	 * @return bool
	 */
	public static function onArticleViewHeader( &$oArticle, &$outputDone, &$useParserCache ) {
		$oTitle = $oArticle->getTitle();
		if ( !static::isProfilePage( $oTitle ) ) {
			return true;
		}
		$useParserCache = false;
		$oUser = \User::newFromName( $oTitle->getText() );
		if ( !$oUser ) {
			return true;
		}

		$entityFactory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileEntityFactory'
		);
		$entity = $entityFactory->newFromUser( $oUser );
		if ( !$entity ) {
			return true;
		}
		$oContentModel = new ProfileContent(
			' ',
			CONTENT_MODEL_BSSOCIALPROFILE
		);
		$outputDone = $oContentModel->getParserOutput(
			$oArticle->getTitle()
		);
		$oArticle->getContext()->getOutput()->addParserOutput( $outputDone );
		return false;
	}

}
