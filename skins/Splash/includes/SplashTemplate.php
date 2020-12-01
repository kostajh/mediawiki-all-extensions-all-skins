<?php
/**
 * BaseTemplate class for the Splash skin
 *
 * @ingroup Skins
 */
class SplashTemplate extends BaseTemplate {

	/**
	 * Is this the mainpage, and to be treated in any way special?
	 * @var bool
	 */
	protected $isMainPage;

	/**
	 * Outputs the entire contents of the page
	 */
	public function execute() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		$request = $skin->getRequest();
		$action = $request->getVal( 'action', 'view' );
		$config = $skin->getContext()->getConfig();

		$wrapperClass = '';
		if ( $config->get( 'SplashUseOverlayImage' ) ) {
			$wrapperClass = 'splash-overlay';
		}

		$html = '';
		$html .= $this->get( 'headelement' );

		// Special case the mainpage! And we'll be hella dumb about it because who cares, really...
		if ( $title->isMainPage() && $action == 'view' && $config->get( 'SplashUseNewMainPage' ) ) {
			// Paranoia...
			$wrapperClass = [ $wrapperClass, 'splash-mainpage' ];
			$this->isMainPage = true;

			$html .= Html::rawElement( 'div', [ 'id' => 'mw-wrapper', 'class' => $wrapperClass ],
				Html::rawElement( 'div', [ 'id' => 'container-bottom' ],
				Html::rawElement( 'div', [ 'id' => 'container-content-margin' ],
				Html::rawElement( 'div', [ 'id' => 'container-content' ],
					$this->getHeaderBlock( [ 'banner', 'search' ] ) .
					$this->getMainPage() .
					$this->getFooterBlock()

				) ) )
			);
		} else {
			$wrapperClass = [ $wrapperClass, 'splash-standard' ];
			$this->isMainPage = false;

			$html .= Html::rawElement( 'div', [ 'id' => 'mw-wrapper', 'class' => $wrapperClass ],
				Html::rawElement( 'div', [ 'id' => 'container-bottom' ],
				Html::rawElement( 'div', [ 'id' => 'container-content-margin' ],
				Html::rawElement( 'div', [ 'id' => 'container-content' ],
					Html::element( 'div', [ 'id' => 'container-content-overlay' ] ) .
					$this->getHeaderBlock() .
					$this->getContentBlock() .
					$this->getFooterBlock()

				) ) )
			);
		}

		$html .= $this->getTrail();
		$html .= Html::closeElement( 'body' );
		$html .= Html::closeElement( 'html' );

		echo $html;
	}

	/**
	 * Generate jump links because there's a soup of nav on top of everything
	 * No I don't know if this is actually helpful!
	 *
	 * @return string html
	 */
	protected function getJumpLink( $msg, $target ) {
		$html = Html::element( 'a', [ 'href' => $target, 'class' => 'mw-jump-link' ],
			$this->getMsg( $msg )->text()
		);

		return $html;
	}

	/**
	 * Generate the page header html, neater like this?
	 *
	 * @param array $skip elements to skip
	 *
	 * @return string html
	 */
	protected function getHeaderBlock( $skip = [] ) {
		$globalLinks = $this->isMainPage ? '' : $this->getGlobalLinks();

		$mobileSpoofs = '';
		$mobileSpoofs .= Html::element( 'div', [ 'id' => 'main-menu-toggle' ] );
		$mobileSpoofs .= Html::element( 'div', [ 'id' => 'toolbox-toggle' ] );
		$mobileSpoofs .= Html::element( 'div', [ 'id' => 'personal-menu-toggle' ] );
		if ( strlen( $globalLinks ) > 1 ) {
			$mobileSpoofs .= Html::element( 'div', [ 'id' => 'global-menu-toggle' ] );
		}

		return Html::rawElement( 'div', [ 'id' => 'header', 'class' => 'with-global-nav' ],
			Html::rawElement( 'div', [ 'id' => 'mw-navigation' ],
			$this->getJumpLink( 'splash-jumptocontent', '#content' ) .
			$this->getJumpLink( 'splash-jumptosearch', '#p-search' ) .
			Html::element( 'div', [ 'id' => 'menus-cover' ] ) .
			$mobileSpoofs .
			$globalLinks .
			( in_array( 'banner', $skip ) ? '' : $this->getLogo( [ 'banner-id' => 'p-banner' ] ) ).
			// Site navigation (mw:sidebar)
			Html::rawElement(
				'div',
				[ 'id' => 'site-navigation' ],
				$this->getSiteNavigation()
			) .
			( in_array( 'search', $skip ) ? '' : $this->getSearch() ) .
			// User profile links
			Html::rawElement(
				'div',
				[ 'id' => 'user-tools' ],
				$this->getUserLinks()
			) .
			$this->getClear()
		) );
	}

	/**
	 * Generate the content html
	 *
	 * @return string html
	 */
	protected function getContentBlock() {
		$html = Html::rawElement( 'div',
			[ 'class' => 'mw-body', 'id' => 'content', 'role' => 'main' ],
			$this->getSiteNotice() .
			$this->getNewTalk() .
			// Page editing and tools
			Html::rawElement( 'div', [ 'class' => 'firstHeading' ],
				Html::rawElement(
					'div',
					[ 'id' => 'page-tools' ],
					$this->getPageLinks()
				) .
				Html::rawElement( 'h1',
					[ 'lang' => $this->get( 'pageLanguage' ) ],
					$this->get( 'title' )
				)
			) .
			Html::rawElement( 'div', [ 'id' => 'siteSub' ],
				$this->getMsg( 'tagline' )->parse()
			) .
			Html::rawElement( 'div', [ 'class' => [ 'mw-body-content', 'mw-content-blob' ] ],
				Html::rawElement( 'div', [ 'id' => 'contentSub' ],
					$this->getPageSubtitle() .
					Html::rawElement(
						'p',
						[],
						$this->get( 'undelete' )
					)
				) .
				Html::rawElement( 'div', [ 'id' => 'content-inner' ],
					$this->get( 'bodycontent' )
				) .
				$this->getClear() .
				Html::rawElement( 'div', [ 'class' => 'printfooter' ],
					$this->get( 'printfooter' )
				) .
				$this->getCategoryLinks()
			) .
			$this->getContentFooter() .
			$this->getDataAfterContent() .
			$this->get( 'debughtml' )
		);

		return Html::rawElement( 'div', [ 'id' => 'content-container' ],
			Html::rawElement( 'div', [ 'id' => 'content-container-upper' ],
				$this->getJumpLink( 'splash-jumptonavigation', '#mw-navigation' ) .
				$html
			)
		);
	}

	/**
	 * Generate special cased Main Page
	 *
	 * @return string html
	 */
	protected function getMainPage() {
		$config = $this->getSkin()->getContext()->getConfig();
		$html = '';

		if ( $config->get( 'SplashUseOverlayImage' ) ) {
			$daji = Html::rawElement( 'div', [ 'id' => 'mainpage-daji' ],
				Html::element( 'img', [ 'src' => $config->get( 'ScriptPath' ) . '/skins/Splash/resources/images/daji-cutout.svg' ] )
			);
		} else {
			$daji = '';
		}

		$html .= Html::rawElement( 'div',
			[ 'class' => 'mw-body', 'id' => 'content', 'role' => 'main' ],
			Html::rawElement( 'div', [ 'id' => 'content-inner' ],
				$daji .
				$this->getLogo( [ 'link' => false ] ) .
				$this->getSearch() .
				$this->getJumpLink( 'splash-jumptonavigation', '#mw-navigation' ) .
			//	Html::rawElement( 'div', [ 'id' => 'content-container' ],
					$this->get( 'bodycontent' )
			//	)
			) .
			$this->getClear() .
			Html::rawElement( 'div', [ 'class' => 'printfooter' ],
				$this->get( 'printfooter' )
			)
		);

		return $html;
	}

	/**
	 * Get wiki-configurable page footer
	 * TODO: set an appropriate lang/dir for this
	 *
	 * @return string html
	 */
	protected function getContentFooter() {
		$html = '';

		$text = $this->getMsg( 'content-footer' )->inContentLanguage()->parse();
		if ( strlen( $text ) > 1 ) {
			$html .= Html::rawElement(
				'div',
				[ 'id' => 'content-footer' ],
				$text
			);
		}

		return $html;
	}

	/**
	 * Generate the logo and site title
	 *
	 * @param array|mixed $setOptions
	 *
	 * @return string html
	 */
	protected function getLogo( $setOptions = [] ) {
		$config = $this->getSkin()->getContext()->getConfig();
		$options = $setOptions + [
			'id' => 'p-logo',
			'link' => $this->data['nav_urls']['mainpage']['href'],
			'banner-id' => ''
		];
		$wrapper = ( !$options['link'] ? 'span' : 'a' );

		$html = Html::openElement(
			'div',
			[
				'id' => $options['id'],
				'class' => 'mw-portlet',
				'role' => 'banner'
			]
		);
		if ( $config->get( 'SplashUseLogoImage' ) ) {
			$html .= Html::element(
				$wrapper,
				[
					'href' => $options['link'],
					'class' => 'mw-wiki-logo',
				] + Linker::tooltipAndAccesskeyAttribs( 'p-logo' )
			);
		}

		$language = $this->getSkin()->getLanguage();
		$siteTitle = $language->convert( $this->getMsg( 'sitetitle' )->inContentLanguage()->escaped() );

		$html .= Html::rawElement(
			$wrapper,
			[
				'id' => $options['banner-id'],
				'class' => 'mw-wiki-title',
				'href' => $options['link']
			] + Linker::tooltipAndAccesskeyAttribs( 'p-logo' ),
			$this->getMsg( 'site-banner', $siteTitle )->escaped()
		);

		$html .= $this->getClear();
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Generate the search form
	 *
	 * @return string html
	 */
	protected function getSearch() {
		$html = Html::openElement(
			'form',
			[
				'action' => $this->get( 'wgScript' ),
				'role' => 'search',
				'class' => 'mw-portlet',
				'id' => 'p-search'
			]
		);
		$html .= Html::hidden( 'title', $this->get( 'searchtitle' ) );
		$html .= Html::rawElement(
			'h3',
			[],
			Html::label( $this->getMsg( 'search' )->text(), 'searchInput' )
		);
		$html .= $this->makeSearchInput( [ 'id' => 'searchInput' ] );
		$html .= $this->makeSearchButton( 'go', [ 'id' => 'searchGoButton', 'class' => 'searchButton' ] );
		$html .= Html::closeElement( 'form' );

		return $html;
	}

	/**
	 * Generate the site navigation (mw:sidebar)
	 *
	 * @return string html
	 */
	protected function getSiteNavigation() {
		$html = '';
		$sidebar = $this->getSidebar();

		// Not doing these here
		unset( $sidebar['SEARCH'] );
		unset( $sidebar['LANGUAGES'] );
		unset( $sidebar['TOOLBOX'] );
		// Extension:Collection
		unset( $sidebar['coll-print_export'] );

		foreach ( $sidebar as $name => $content ) {
			// Numeric strings gets an integer when set as key, cast back - T73639
			$name = (string)$name;

			// @phan-suppress-next-line SecurityCheck-DoubleEscaped
			$html .= $this->getPortlet( $name, $content['content'] );
		}

		if ( $this->isMainPage ) {
			$html .= $this->getGlobalLinks();
		}

		// Not really a sidebar, but tradition!
		$html = Html::rawElement( 'div', [ 'id' => 'mw-sidebar' ],
			Html::rawElement(
				'h2',
				[],
				$this->getMsg( 'navigation' )->parse()
			) . Html::rawElement( 'div', [ 'id' => 'sidebar-inner' ], $html )
		);

		if ( $this->isMainPage ) {
			// Gonna break some gadgets, but it's just the mainpage, so whatevs
			$views = $this->getPortlet(
				'page-views',
				// @phan-suppress-next-line PhanTypeMismatchArgument
				$this->data['content_navigation']['namespaces'] + $this->data['content_navigation']['views'],
				'actions'
			);
			$actions = $this->getPortlet(
				'page-tools',
				$this->data['content_navigation']['actions'],
				'splash-page-tools'
			);
			$tools = $this->getPortlet(
				'toolbox',
				$this->data['sidebar']['TOOLBOX'],
				'splash-toolbox'
			);

			$toolBox = Html::rawElement( 'div', [ 'id' => 'mw-tools' ],
				Html::rawElement(
					'h2',
					[],
					$this->getMsg( 'toolbox' )->parse()
				) .
				Html::rawElement( 'div', [ 'id' => 'tools-inner' ],
					$views .
					$actions .
					$tools
				)
			);

			// Just tack this on here...
			$toolBox .= $this->getLanguageLinks();
		} else {
			$toolBox = $this->getPortlet( 'tb', $this->data['sidebar']['TOOLBOX'], 'toolbox' );
		}

		return $html . $toolBox;
	}

	/**
	 * Print arbitrary block of navigation
	 * Message parsing is limited to first 10 lines only for this skin.
	 *
	 * @param string $linksMessage
	 * @param string $id
	 * @param bool $doubleHeader Stupid mobile hack
	 *
	 * @return string html
	 */
	protected function getNavigation( $linksMessage, $id, $doubleHeader = false ) {
		$message = trim( $this->getMsg( $linksMessage )->inContentLanguage()->text() );
		$lines = array_slice( explode( "\n", $message ), 0, 10 );
		$links = [];
		foreach ( $lines as $line ) {
			// ignore empty lines
			if ( strlen( $line ) == 0 ) {
				continue;
			}

			$item = [];

			$line_temp = explode( '|', trim( $line, '* ' ), 3 );
			if ( count( $line_temp ) > 1 ) {
				$line = $line_temp[1];
				$link = $this->getMsg( $line_temp[0] )->inContentLanguage()->text();

				// Pull out third item as a class
				if ( count( $line_temp ) == 3 ) {
					$item['class'] = Sanitizer::escapeIdForAttribute( $line_temp[2] );
				}
			} else {
				$line = $line_temp[0];
				$link = $line_temp[0];
			}
			$item['id'] = Sanitizer::escapeIdForAttribute( $line );

			// Determine what to show as the human-readable link description
			if ( $this->getMsg( $line )->isDisabled() ) {
				// It's *not* the name of a MediaWiki message, so display it as-is
				$item['text'] = $line;
			} else {
				// Guess what -- it /is/ a MediaWiki message!
				$item['text'] = $this->getMsg( $line )->text();
			}

			$href = '#';
			if ( $link !== null ) {
				if ( $this->getMsg( $line_temp[0] )->isDisabled() ) {
					$link = $line_temp[0];
				}

				$href = Skin::makeInternalOrExternalUrl( $link );
			}
			$item['href'] = $href;

			$links[] = $item;
		}

		return $this->getPortlet( $id, $links, null, [ 'extra-header' => $doubleHeader, 'incontentlanguage' => true ] );
	}

	/**
	 * Menu for global navigation (for cross-wiki stuff or just whatever things)
	 *
	 * @return string HTML
	 */
	protected function getGlobalLinks() {
		$html = '';
		if ( !$this->getMsg( 'global-links-menu' )->inContentLanguage()->isDisabled() ) {
			$html = $this->getNavigation( 'global-links-menu', 'global-links', true );
		}

		return $html;
	}

	/**
	 * In other languages list
	 *
	 * @return string html
	 */
	protected function getLanguageLinks() {
		$html = '';
		if ( $this->data['language_urls'] !== false || $this->getAfterPortlet( 'lang' ) !== '' ) {
			$html .= $this->getPortlet(
				'lang',
				$this->data['language_urls'],
				'otherlanguages',
				[ 'extra-header' => true ]
			);
		}

		return $html;
	}

	/**
	 * Language variants. Displays list for converting between different scripts in the same language,
	 * if using a language where this is applicable (such as latin vs cyric display for serbian).
	 *
	 * TODO: header-msg that actually reflects currently selected variant?
	 *
	 * @return string html
	 */
	protected function getVariants() {
		$variants = $this->data['content_navigation']['variants'];
		$html = '';

		if ( count( $variants ) > 0 ) {
			$html .= $this->getPortlet(
				'variants',
				$variants,
				null,
				[ 'extra-header' => true ]
			);
		}

		return $html;
	}

	/**
	 * Generate page-related tools/links
	 *
	 * @return string html
	 */
	protected function getPageLinks() {
		$namespaces = $this->data['content_navigation']['namespaces'];
		$views = $this->data['content_navigation']['views'];
		$actions = $this->data['content_navigation']['actions'];

		// Move watch out of dropdown
		if ( isset( $actions['unwatch'] ) ) {
			$views['unwatch'] = $actions['unwatch'];
			unset( $actions['unwatch'] );
		}
		if ( isset( $actions['watch'] ) ) {
			$views['watch'] = $actions['watch'];
			unset( $actions['watch'] );
		}

		// Move history, view source into dropdown
		if ( isset( $views['history'] ) ) {
			$actions = [ 'history' => $views['history'] ] + $actions;
			unset( $views['history'] );
		}
		if ( isset( $views['viewsource'] ) ) {
			$actions = [ 'viewsource' => $views['viewsource'] ] + $actions;
			unset( $views['viewsource'] );
		}

		// Relabel talkpage...
		// They all have different keys for some insane reason, so we're just going
		// assume it's the second (last) item if there's more than one item
		if ( count( $namespaces ) > 1 ) {
			$namespaces['talk'] = array_pop( $namespaces );
			$namespaces['talk']['text'] = $this->getMsg( 'splash-talk' )->text();
		}

		$html = '';

		// Page link
		// TODO: use i18n for ':' here, instead of CSS
		$html .= $this->getPortlet(
			'namespaces',
			$namespaces,
			null,
			[ 'extra-classes' => 'page-tools-inline' ]
		);

		// Inline page actions
		$views = $this->getPortlet(
			'views',
			$views,
			null,
			[ 'extra-classes' => 'page-tools-inline' ]
		);

		$dropdowns = '';

		// Language variant options
		$dropdowns .= $this->getVariants();
		// Other actions for the page: move, delete, protect, everything else
		$dropdowns .= $this->getPortlet(
			'page-tools',
			$actions,
			'splash-page-tools',
			[ 'extra-header' => true ]
		);

		$dropdowns .= $this->getLanguageLinks();

		// Ext:Collection tools
		$sidebar = $this->getSidebar();
		if ( isset( $sidebar['coll-print_export'] ) ) {
			// @phan-suppress-next-line SecurityCheck-DoubleEscaped
			$dropdowns .= $this->getPortlet(
				'coll-print_export',
				$sidebar['coll-print_export']['content'],
				null,
				[ 'extra-header' => true ]
			);
		}

		$html .= Html::rawElement( 'div', [ 'id' => 'page-tools-right' ],
			$views .
			Html::rawElement(
				'div',
				[ 'id' => 'page-tools-dropdowns' ],
				$dropdowns
			) .
			$this->getIndicators()
		);

		$html .= $this->getClear();

		return $html;
	}

	/**
	 * Generate user tools menu
	 *
	 * @return string HTML
	 */
	protected function getUserLinks() {
		$user = $this->getSkin()->getUser();
		$personalTools = $this->getPersonalTools();

		$html = '';

		// Move Echo badges out of default list - they should be visible outside of
		// dropdown; may not even work at all inside one
		$extraTools = [];
		if ( isset( $personalTools['notifications-alert'] ) ) {
			$extraTools['notifications-alert'] = $personalTools['notifications-alert'];
			unset( $personalTools['notifications-alert'] );
		}
		if ( isset( $personalTools['notifications-notice'] ) ) {
			$extraTools['notifications-notice'] = $personalTools['notifications-notice'];
			unset( $personalTools['notifications-notice'] );
		}

		// Re-label some messages
		if ( isset( $personalTools['userpage'] ) ) {
			$personalTools['userpage']['links'][0]['text'] = $this->getMsg( 'splash-userpage' )->text();
		}
		if ( isset( $personalTools['mytalk'] ) ) {
			$personalTools['mytalk']['links'][0]['text'] = $this->getMsg( 'splash-talkpage' )->text();
		}

		// Move ULS trigger
		if ( isset( $personalTools['uls'] ) ) {
			$extraTools['uls'] = $personalTools['uls'];
			unset( $personalTools['uls'] );
		}

		$personalToolsClass = 'not-logged-in';
		// Dropdown header, ULS trigger
		if ( $user->isLoggedIn() ) {
			$headerMsg = [ 'splash-loggedinas', $user->getName() ];
			$personalToolsClass = 'logged-in';

		} else {
			$headerMsg = [ 'splash-notloggedin', $user->getName() ];
		}
		$html .= Html::openElement( 'div', [ 'id' => 'mw-user-links' ] );

		// Place the extra icons/outside stuff
		if ( !empty( $extraTools ) ) {
			$iconList = '';
			foreach ( $extraTools as $key => $item ) {
				$iconList .= $this->makeListItem( $key, $item );
			}

			$html .= Html::rawElement(
				'div',
				[ 'id' => 'p-personal-extra', 'class' => 'p-body' ],
				Html::rawElement( 'ul', [], $iconList )
			);
		}

		$html .= $this->getPortlet( 'personal', $personalTools, $headerMsg, [ 'extra-classes' => $personalToolsClass, 'extra-header' => $headerMsg ] );

		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Generate siteNotice, if any
	 *
	 * @return string html
	 */
	protected function getSiteNotice() {
		return $this->getIfExists( 'sitenotice', [
			'wrapper' => 'div',
			'parameters' => [ 'id' => 'siteNotice' ]
		] );
	}

	/**
	 * Generate new talk message banner, if any
	 *
	 * @return string html
	 */
	protected function getNewTalk() {
		return $this->getIfExists( 'newtalk', [
			'wrapper' => 'div',
			'parameters' => [ 'class' => 'usermessage' ]
		] );
	}

	/**
	 * Generate subtitle stuff, if any
	 *
	 * @return string html
	 */
	protected function getPageSubtitle() {
		return $this->getIfExists( 'subtitle', [ 'wrapper' => 'p' ] );
	}

	/**
	 * Generate category links, if any
	 *
	 * @return string html
	 */
	protected function getCategoryLinks() {
		return $this->getIfExists( 'catlinks' );
	}

	/**
	 * Generate data after content stuff, if any
	 *
	 * @return string html
	 */
	protected function getDataAfterContent() {
		return $this->getIfExists( 'dataAfterContent' );
	}

	/**
	 * Simple wrapper for random if-statement-wrapped $this->data things
	 *
	 * @param string $object name of thing
	 * @param array $setOptions
	 *
	 * @return string html
	 */
	protected function getIfExists( $object, $setOptions = [] ) {
		$options = $setOptions + [
			'wrapper' => 'none',
			'parameters' => []
		];
		'@phan-var array{wrapper:string,parameters:array} $options';

		$html = '';

		if ( $this->data[$object] ) {
			if ( $options['wrapper'] === 'none' ) {
				$html .= $this->get( $object );
			} else {
				$html .= Html::rawElement(
					$options['wrapper'],
					$options['parameters'],
					$this->get( $object )
				);
			}
		}

		return $html;
	}

	/**
	 * Generate a block of navigation links with a header
	 *
	 * <INSERT SCREAMING>
	 *
	 * @param string $name
	 * @param array|string $content array of links for use with makeListItem, or a block of text
	 * @param null|string|array $msg
	 * @param array $setOptions random crap to rename/do/whatever
	 *
	 * @return string HTML
	 */
	protected function getPortlet( $name, $content, $msg = null, $setOptions = [] ) {
		// random stuff to override with any provided options
		$options = $setOptions + [
			// extra classes/ids
			'id' => 'p-' . $name,
			'class' => [ 'mw-portlet', 'emptyPortlet' => !$content ],
			'extra-classes' => [],
			// what to wrap the body list in, if anything
			'body-wrapper' => 'div',
			'body-id' => '',
			'body-class' => 'mw-portlet-body',
			'body-extra-classes' => [],
			// makeListItem options
			'list-item' => [ 'text-wrapper' => [ 'tag' => 'span' ] ],
			// option to stick arbitrary stuff at the beginning of the ul
			'list-prepend' => '',
			'extra-header' => false,
			'incontentlanguage' => false
		];

		// Handle the different $msg possibilities
		if ( $msg === null ) {
			$msg = $name;
		} elseif ( is_array( $msg ) ) {
			$msgString = array_shift( $msg );
			$msgParams = $msg;
			$msg = $msgString;
		}
		if ( $options['incontentlanguage'] ) {
			$msgObj = $this->getMsg( $msg )->inContentLanguage();
		} else {
			$msgObj = $this->getMsg( $msg );
		}
		if ( $msgObj->exists() ) {
			if ( isset( $msgParams ) && !empty( $msgParams ) ) {
				$msgString = $this->getMsg( $msg, $msgParams )->parse();
			} else {
				$msgString = $msgObj->parse();
			}
		} else {
			$msgString = htmlspecialchars( $msg );
		}

		$labelId = Sanitizer::escapeIdForAttribute( "p-$name-label" );

		if ( is_array( $content ) ) {
			if ( !count( $content ) ) {
				return '';
			}

			$contentText = '';
			if ( $options['extra-header'] ) {
				$contentText .= Html::rawElement( 'h3', [], $msgString );
			}

			$contentText .= Html::openElement( 'ul',
				[ 'lang' => $this->get( 'userlang' ), 'dir' => $this->get( 'dir' ) ]
			);
			$contentText .= $options['list-prepend'];
			foreach ( $content as $key => $item ) {
				$contentText .= $this->makeListItem( $key, $item, $options['list-item'] );
			}
			$contentText .= Html::closeElement( 'ul' );
		} else {
			$contentText = $content;
		}

		// Special handling for role=search and other weird things
		$divOptions = [
			'role' => 'navigation',
			'class' => $this->mergeClasses( $options['class'], $options['extra-classes'] ),
			'id' => Sanitizer::escapeIdForAttribute( $options['id'] ),
			'title' => Linker::titleAttrib( $options['id'] ),
			'aria-labelledby' => $labelId,
		];

		$labelOptions = [
			'id' => $labelId,
			'lang' => $this->get( 'userlang' ),
			'dir' => $this->get( 'dir' )
		];

		// @phan-suppress-next-line PhanSuspiciousValueComparison
		if ( $options['body-wrapper'] !== 'none' ) {
			$bodyDivOptions = [ 'class' => $this->mergeClasses( $options['body-class'], $options['body-extra-classes'] ) ];
			if ( strlen( $options['body-id'] ) ) {
				$bodyDivOptions['id'] = $options['body-id'];
			}
			$body = Html::rawElement( $options['body-wrapper'], $bodyDivOptions,
				$contentText .
				$this->getAfterPortlet( $name )
			);
		} else {
			$body = $contentText . $this->getAfterPortlet( $name );
		}

		$html = Html::rawElement( 'div', $divOptions,
			Html::rawElement( 'h3', $labelOptions, $msgString ) .
			$body
		);

		return $html;
	}

	/**
	 * Helper function for getPortlet
	 *
	 * Merge all provided css classes into a single array
	 * Account for possible different input methods matching what Html::element stuff takes
	 *
	 * @param string|array $class base portlet/body class
	 * @param string|array $extraClasses any extra classes to also include
	 *
	 * @return array all classes to apply
	 */
	protected function mergeClasses( $class, $extraClasses ) {
		if ( !is_array( $class ) ) {
			$class = [ $class ];
		}
		if ( !is_array( $extraClasses ) ) {
			$extraClasses = [ $extraClasses ];
		}

		return array_merge( $class, $extraClasses );
	}

	/**
	 * Better renderer for getFooterIcons and getFooterLinks
	 *
	 * @param array $setOptions Miscellaneous other options
	 * * 'id' for footer id
	 * * 'class' for footer class
	 * * 'order' to determine whether icons or links appear first: 'iconsfirst' or links, though in
	 *   practice we currently only check if it is or isn't 'iconsfirst'
	 * * 'link-prefix' to set the prefix for all link and block ids; most skins use 'f' or 'footer',
	 *   as in id='f-whatever' vs id='footer-whatever'
	 * * 'icon-style' to pass to getFooterIcons: "icononly", "nocopyright"
	 * * 'link-style' to pass to getFooterLinks: "flat" to disable categorisation of links in a
	 *   nested array
	 *
	 * @return string html
	 */
	protected function getFooterBlock( $setOptions = [] ) {
		// Set options and fill in defaults
		$options = $setOptions + [
			'id' => 'footer',
			'class' => 'mw-footer',
			'order' => 'iconsfirst',
			'link-prefix' => 'footer',
			'icon-style' => 'icononly',
			'link-style' => null
		];
		'@phan-var array{id:string,class:string,order:string,link-prefix:string,icon-style:string,link-style:?string} $options';

		$validFooterIcons = $this->getFooterIcons( $options['icon-style'] );
		$validFooterLinks = $this->getFooterLinks( $options['link-style'] );

		$html = '';

		$html .= Html::openElement( 'div', [
			'id' => $options['id'],
			'class' => $options['class'],
			'role' => 'contentinfo',
			'lang' => $this->get( 'userlang' ),
			'dir' => $this->get( 'dir' )
		] );

		$iconsHTML = '';
		if ( count( $validFooterIcons ) > 0 ) {
			$iconsHTML .= Html::openElement( 'ul', [ 'id' => "{$options['link-prefix']}-icons" ] );
			foreach ( $validFooterIcons as $blockName => $footerIcons ) {
				$iconsHTML .= Html::openElement( 'li', [
					'id' => Sanitizer::escapeIdForAttribute(
						"{$options['link-prefix']}-{$blockName}ico"
					),
					'class' => 'footer-icons'
				] );
				foreach ( $footerIcons as $icon ) {
					$iconsHTML .= $this->getSkin()->makeFooterIcon( $icon );
				}
				$iconsHTML .= Html::closeElement( 'li' );
			}
			$iconsHTML .= Html::closeElement( 'ul' );
		}

		$linksHTML = '';
		if ( count( $validFooterLinks ) > 0 ) {
			if ( $options['link-style'] === 'flat' ) {
				$linksHTML .= Html::openElement( 'ul', [
					'id' => "{$options['link-prefix']}-list",
					'class' => 'footer-places'
				] );
				foreach ( $validFooterLinks as $link ) {
					$linksHTML .= Html::rawElement(
						'li',
						[ 'id' => Sanitizer::escapeIdForAttribute( $link ) ],
						$this->get( $link )
					);
				}
				$linksHTML .= Html::closeElement( 'ul' );
			} else {
				$linksHTML .= Html::openElement( 'div', [ 'id' => "{$options['link-prefix']}-list" ] );
				foreach ( $validFooterLinks as $category => $links ) {
					$linksHTML .= Html::openElement( 'ul',
						[ 'id' => Sanitizer::escapeIdForAttribute(
							"{$options['link-prefix']}-{$category}"
						) ]
					);
					foreach ( $links as $link ) {
						$linksHTML .= Html::rawElement(
							'li',
							[ 'id' => Sanitizer::escapeIdForAttribute(
								"{$options['link-prefix']}-{$category}-{$link}"
							) ],
							$this->get( $link )
						);
					}
					$linksHTML .= Html::closeElement( 'ul' );
				}
				$linksHTML .= Html::closeElement( 'div' );
			}
		}

		if ( $options['order'] === 'iconsfirst' ) {
			$html .= $iconsHTML . $linksHTML;
		} else {
			$html .= $linksHTML . $iconsHTML;
		}

		$html .= $this->getClear() . Html::closeElement( 'div' );

		return $html;
	}
}
