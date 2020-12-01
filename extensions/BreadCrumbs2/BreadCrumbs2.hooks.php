<?php
/*
 * This file is part of Extension:BreadCrumbs2
 *
 * Copyright (C) 2007; Eric Hartwell and Ike Hecht.
 *
 * Distributed under the terms of the CC-BY-3.0 license.
 * Terms and conditions of the license can be found at
 * <https://creativecommons.org/licenses/by/3.0/>
 *
 * @author Eric Hartwell (http://www.ehartwell.com/InfoDabble/BreadCrumbs2)
 * @author Ike Hecht
 * @license CC-BY-3.0
 */
use MediaWiki\MediaWikiServices;

class BreadCrumbs2Hooks {

	public static function onSkinSubPageSubtitle( string &$subpages, Skin $skin, OutputPage $out ) {
		# Only show breadcrumbs when viewing the page, not editing, etc.
		# The following line should perhaps utilize Action::getActionName( $skin->getContext() );
		if ( $skin->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}

		# Get the list of categories for the current page
		$categories = $skin->getOutput()->getCategories();
		$title = $skin->getRelevantTitle();

		$breadCrumbs2 = new BreadCrumbs2( $categories, $title, $skin->getUser() );
		$skin->getOutput()->setProperty( 'BreadCrumbs2', $breadCrumbs2 );

		$config = MediaWikiServices::getInstance()->getMainConfig();
		$hideUnmatched = $config->get( 'BreadCrumbs2HideUnmatched' );
		if ( $hideUnmatched && !$breadCrumbs2->hasBreadCrumbs() ) {
			// If no breadcrumbs are defined for this page, do nothing.
			return true;
		}

		# See if we should change the site logo
		# Don't go overboard with this... subtle is better.
		$logoPath = $breadCrumbs2->getLogoPath();
		if ( $logoPath ) {
			global $wgLogo, $wgScriptPath;
			// FIXME: Does not work with modern MediaWiki versions and modern skins, which have already
			// set the logo at this point using the ResourceLoader
			$wgLogo = $wgScriptPath . '/' . $logoPath;
		}

		$subpages = $breadCrumbs2->getOutput() . $subpages;
		$removeBasePageLink = $config->get( 'BreadCrumbs2RemoveBasePageLink' );
		if ( $removeBasePageLink && $title->isSubpage() && $breadCrumbs2->hasBreadCrumbs() ) {
			return false;
		}
		return true;
	}

	/**
	 * @param Skin $skin
	 * @param array &$sidebar
	 */
	public static function onSidebarBeforeOutput( Skin $skin, array &$sidebar ) {
		/** @var BreadCrumbs2 $breadCrumbs2 */
		$breadCrumbs2 = $skin->getOutput()->getProperty( 'BreadCrumbs2' );
		if ( !$breadCrumbs2 ) {
			return;
		}

		$sidebarText = $breadCrumbs2->getSidebarText();
		if ( $sidebarText ) {
			# See if there's a corresponding link in the sidebar and mark it as active.
			# This is especially useful for skins that display the sidebar as a tab bar.
			foreach ( $sidebar as $bar => $cont ) {
				foreach ( $cont as $key => $val ) {
					if ( $val['text'] === $sidebarText ) {
						$sidebar[$bar][$key]['active'] = true;
						break;
					}
				}
			}
		}
	}
}
