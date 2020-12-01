<?php

/**
 * @brief Code for the @ref Extensions-AddPersonalUrls.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup Extensions-AddPersonalUrls
 *
 * @author [RV1971](https://www.mediawiki.org/wiki/User:RV1971)
 *
 */

/**
 * @brief Class implementing the @ref Extensions-AddPersonalUrls.
 *
 * @ingroup Extensions-AddPersonalUrls
 */
class AddPersonalUrls {
	/* == public static methods == */

	/**
	 * @brief Get an instance of this class.
	 *
	 * Due to the use of late static binding, the mechanism works for
	 * derived classes as well. All derived classes would use the same
	 * instance stored here, which is OK because we will never need
	 * more than one.
	 *
	 * @return AddPersonalUrls
	 */
	public static function &singleton() {
		static $instance;

		if ( !isset( $instance ) ) {
			$instance = new static;
		}

		return $instance;
	}

	/**
	 * @brief Initialize this extension.
	 */
	public static function init() {
		global $wgHooks;

		$wgHooks['BeforePageDisplay'][] = self::singleton();
		$wgHooks['EditFormPreloadText'][] = self::singleton();
		$wgHooks['PersonalUrls'][] = self::singleton();
	}

	/* == hooks == */

	/**
	 * @brief [BeforePageDisplay]
	 * (https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay) hook.
	 *
	 * Add the [Resource Modules]
	 * (https://www.mediawiki.org/wiki/$wgResourceModules) to the page.
	 *
	 * @param OutputPage &$out The OutputPage object.
	 *
	 * @param Skin &$skin Skin object that will be used to
	 * generate the page.
	 *
	 * @return bool Always TRUE.
	 */
	public function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModules( 'ext.addPersonalUrls' );

		return true;
	}

	/**
	 * @brief [EditFormPreloadText]
	 * (https://www.mediawiki.org/wiki/Manual:Hooks/EditFormPreloadText) hook.
	 *
	 * Preload text when creating new pages in the User namespace. See
	 * @ref $wgAddPersonalUrlsTable for an explanation how the text is
	 * composed.
	 *
	 * @param string &$text Text to prefill edit form with.
	 *
	 * @param Title &$title Title of new page (Title Object).
	 *
	 * @return bool Always TRUE.
	 */
	public function onEditFormPreloadText( &$text, Title &$title ) {
		if ( $title->getNamespace() != NS_USER ) {
			return true;
		}

		/** Skip if there is already another preload text. */
		if ( $text ) {
			return true;
		}

		$msg1 = wfMessage( 'addpersonalurls-'
			. strtolower( $title->getSubpageText() ) . '-preload' );

		/** If the page-specific message does not exist, do not
		 *	preload anything.
		 */
		if ( !$msg1->exists() ) {
			return true;
		}

		$msg2 = wfMessage( 'addpersonalurls-preload' );

		$text = "<!-- {$msg1->text()}";

		if ( $msg1->text() !== '' && $msg2->text() !== '' ) {
			$text .= "\n\n";
		}

		$text .= "{$msg2->text()} -->";

		return true;
	}

	/**
	 * @brief [PersonalUrls]
	 * (https://www.mediawiki.org/wiki/Manual:Hooks/PersonalUrls) hook.
	 *
	 * This is the core of the extension which actually adds the URLs
	 * to the list of personal URLs.
	 *
	 * @param array &$personal_urls The array of URLs set up so far.
	 * @param Title $title The Title object of the current article.
	 * @param SkinTemplate $skin Skin template, for context
	 *
	 * @return bool Always TRUE.
	 */
	public function onPersonalUrls( array &$personal_urls, Title $title, SkinTemplate $skin ) {
		global $wgAddPersonalUrlsTable;

		$user = $skin->getUser();
		$username = $user->getName();

		/** Consider logged-in users only. */
		if ( $user->getID() ) {
			$pageurl = $title->getLocalURL();

			/** Extract link to user page in order to keep it as first
			 *	item.
			 */
			$urls = [ 'userpage' => array_shift( $personal_urls ) ];

			foreach ( $wgAddPersonalUrlsTable as $id => $url ) {
				/** Ignore items were the target is unset. This allows
				 * to remove in `LocalSettings.php` items defined in
				 * extension.json.
				 */
				if ( !isset( $url ) ) {
					continue;
				}

				/** Replace $username with actual username. */
				$url = str_replace( '$username', $username, $url );

				/** Setup URL details, distinguishing between internal
				 *	and external links.
				 */
				if ( strpos( $url, '://' ) !== false ) {
					$href = $url;
					$class = 'external text';
				} else {
					/** Split the URL at '?', if any. */
					$components = explode( '?', $url, 2 );
					$name = $components[0];
					$urlaction = isset( $components[1] )
						? $components[1] : null;

					$linkedTitle = Title::newFromText( $name );
					Skin::checkTitle( $linkedTitle, $name );
					$exists = $linkedTitle->getArticleID() != 0
						|| $linkedTitle->isSpecialPage();

					/** If a page does not exist and is not a special
					 *	page, open it for editing and format it as a
					 *	link to a new page.
					 */
					if ( !$exists ) {
						$urlaction = 'action=edit';
						$class = 'new';
					}

					$href = $linkedTitle->getLocalURL( $urlaction );

					$active = ( isset( $class ) && $class == 'new' )
						? $linkedTitle->getLocalURL() == $pageurl
						: $href == $pageurl;
				}

				$text = wfMessage( $id )->text();
				$urls[$id] = compact( 'text', 'href', 'active', 'class' );
			}

			/** Prepend new URLs to existing ones. */
			$personal_urls = $urls + $personal_urls;
		}

		return true;
	}
}
