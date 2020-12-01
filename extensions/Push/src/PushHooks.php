<?php

use MediaWiki\MediaWikiServices;

/**
 * Static class for hooks handled by the Push extension.
 *
 * @since 0.1
 *
 * @file Push.hooks.php
 * @ingroup Push
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class PushHooks {

	/**
	 * Adds a link to Admin Links page.
	 *
	 * @since 0.1
	 *
	 * @param ALTree $admin_links_tree
	 * @return true
	 */
	public static function addToAdminLinks( $admin_links_tree ) {
		$ioSection = $admin_links_tree->getSection( wfMessage( 'adminlinks_importexport' )->text() );
		$mainRow = $ioSection->getRow( 'main' );
		$mainRow->addItem( ALItem::newFromSpecialPage( 'Push' ) );

		return true;
	}

	/**
	 * Adds an "action" (i.e., a tab) to allow pushing the current article.
	 *
	 * Called on both content and special pages after variants have been added
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateNavigation::Universal
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 */
	public static function onSkinTemplateNavigationUniversal( SkinTemplate $sktemplate, array &$links ) {
		// Make sure that this is not a special page, the page has contents, and the user can push.
		$title = $sktemplate->getTitle();
		$user = $sktemplate->getUser();
		// TODO get rid of eg prefix
		$config = new GlobalVarConfig( 'egPush' );
		$pushTargets = $config->get( 'Targets' );
		$pushShowTab = $config->get( 'ShowTab' );
		$request = $sktemplate->getRequest();
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		if (
			$title
			&& $title->getNamespace() !== NS_SPECIAL
			&& $title->exists()
			&& $pm->userHasRight( $user, 'push' )
			&& count( $pushTargets ) > 0
		) {
			$location = $pushShowTab ? 'views' : 'actions';
			$links[$location]['push'] = [
				'text' => wfMessage( 'push-tab-text' )->text(),
				'class' => $request->getVal( 'action' ) == 'push' ? 'selected' : '',
				'href' => $title->getLocalURL( 'action=push' )
			];
		}
	}
}
