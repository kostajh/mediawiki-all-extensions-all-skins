<?php
/**
 * BlueSpiceSocial base extension for BlueSpice
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

namespace BlueSpice\Social;

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\LessVars;

class Extension extends \BlueSpice\Extension {

	/**
	 *
	 */
	public static function onRegistration() {
		global $wgExtraNamespaces, $wgNamespacesWithSubpages,
			$wgNamespacesToBeSearchedDefault, $wgContentHandlers,
			$wgNamespaceContentModels;

		if ( !defined( 'NS_SOCIALENTITY' ) ) {
			define( "NS_SOCIALENTITY", 1506 );
			$wgExtraNamespaces[NS_SOCIALENTITY] = 'SocialEntity';
			$wgNamespacesWithSubpages[NS_SOCIALENTITY] = false;
			$wgNamespacesToBeSearchedDefault[NS_SOCIALENTITY] = false;
			$GLOBALS['bsgSystemNamespaces'][1506] = 'NS_SOCIALENTITY';
		}
		if ( !defined( 'NS_SOCIALENTITY_TALK' ) ) {
			define( 'NS_SOCIALENTITY_TALK', 1507 );
			$wgExtraNamespaces[NS_SOCIALENTITY_TALK] = 'SocialEntity_talk';
			$GLOBALS['bsgSystemNamespaces'][1507] = 'NS_SOCIALENTITY_TALK';
		}
		if ( !defined( 'CONTENT_MODEL_BSSOCIAL' ) ) {
			define( 'CONTENT_MODEL_BSSOCIAL', 'BSSocial' );
			$wgContentHandlers[CONTENT_MODEL_BSSOCIAL]
				= 'BlueSpice\\Social\\Content\\EntityHandler';
			$wgNamespaceContentModels[NS_SOCIALENTITY] = CONTENT_MODEL_BSSOCIAL;
		}

		// Add social namespaces to "noindex" list for ExtendedSearch
		if ( isset( $GLOBALS['bsgESSourceConfig']['wikipage']['skip_namespaces'] ) ) {
			$GLOBALS['bsgESSourceConfig']['wikipage']['skip_namespaces'] = array_merge(
				$GLOBALS['bsgESSourceConfig']['wikipage']['skip_namespaces'],
				[ 1506, 1507 ]
			);
		} else {
			$GLOBALS['bsgESSourceConfig']['wikipage']['skip_namespaces'] = [
				1506, 1507
			];
		}

		$lessVars = LessVars::getInstance();
		$lessVars->setVar( 'bs-primary-light-background', '#98A7C4' );
		$lessVars->setVar( 'bs-light-background', 'lighten(@bs-color-neutral, 38.43%)' );
		$lessVars->setVar( 'bs-primary-light-subelement-background', 'lighten(@bs-primary-light-background, 24.3%)' );
		$lessVars->setVar( 'bs-tertiary-light-background', 'lighten(@bs-color-tertiary, 45%)' );
		$lessVars->setVar( 'bs-color-neutral-headline', 'lighten(@bs-color-neutral, 27.8%)' );
		$lessVars->setVar( 'bs-color-lighten-information', '#BABABA' );
		$lessVars->setVar( 'bs-color-header-information', '#252525' );
		$lessVars->setVar( 'bs-color-header-information-link', '@bs-primary-light-subelement-border' );
		$lessVars->setVar( 'bs-color-social-entity-background', 'white' );
		$lessVars->setVar( 'bs-color-social-link', '#0060DF' );
		$lessVars->setVar( 'bs-color-social-link-dark', '@bs-primary-light-subelement-border' );
		$lessVars->setVar( 'navigation-tab-color', '@bs-color-primary' );
		$lessVars->setVar( 'navigation-color', 'lighten( @bs-color-neutral3, 23.1373)' );
		$lessVars->setVar( 'bs-social-background-color-archived', '#F1D8D8' );
		$lessVars->setVar(
			'bs-social-background-color-owned',
			'lighten(desaturate(spin(@bs-primary-light-background, 0.4545), 2.1605), 27.0588)'
		);
		$lessVars->setVar( 'bs-primary-light-subelement-border', '#3E5389' );
	}

	/**
	 *
	 * @return \Title
	 */
	public static function getDefaultRelatedTitle() {
		// TODO: make changeable!
		return \Title::newMainPage();
	}

	/**
	 * Embeds CSS into pdf export
	 * @param array &$aTemplate
	 * @param array &$aStyleBlocks
	 * @return bool Always true to keep hook running
	 */
	public static function onBSUEModulePDFBeforeAddingStyleBlocks( &$aTemplate, &$aStyleBlocks ) {
		$oCollector = ResourceCollector::getMain();
		$aStyleBlocks[ 'SocialEntity' ] = $oCollector->getCombinedStylesFile();
		$aStyleBlocks[ 'SocialEntity' ] .= <<<HEREDOC
.bs-social-entity-aftercontent, .bs-social-entity-actions,
.bs-social-entity-content-more, .bs-social-entitylist-menu {
	display: none !important;
}
.bs-social-entity-userimage {
	float: left;
	position: absolute;
}
HEREDOC;
		return true;
	}

	/**
	 * Removes watchlist notifications for entities
	 * @param User $watchingUser
	 * @param Title $title
	 * @param UserMailer $userMailer
	 * @return bool
	 */
	public static function onSendWatchlistEmailNotification( $watchingUser, $title, $userMailer ) {
		if ( !$title || $title->getNamespace() !== NS_SOCIALENTITY ) {
			return true;
		}
		$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
			->newFromSourceTitle( $title );
		if ( !$entity || !$entity->exists() ) {
			return true;
		}

		return false;
	}

}
