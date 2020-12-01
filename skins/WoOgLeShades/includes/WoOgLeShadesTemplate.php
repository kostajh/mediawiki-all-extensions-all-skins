<?php
/**
 * BaseTemplate class for the WoOgLeShades skin
 *
 * @ingroup Skins
 */
class WoOgLeShadesTemplate extends BaseTemplate {
	/**
	 * Outputs the entire contents of the page
	 */
	public function execute() {
		$html = '';
		$html .= $this->get( 'headelement' );

		$html .=  Html::element( 'div', [ 'id' => 'menus-cover' ] ) .
			Html::rawElement( 'div', [ 'id' => 'mw-wrapper' ],
			Html::rawElement( 'div', [ 'id' => 'header' ],
				Html::rawElement( 'div', [ 'id' => 'mw-navigation-outer-outer' ],
				Html::rawElement( 'div', [ 'id' => 'mw-navigation-outer' ],
					Html::rawElement( 'div', [ 'id' => 'mw-navigation' ],
						$this->getLogo() .
						Html::rawElement(
							'h2',
							[],
							$this->getMsg( 'navigation-heading' )->parse()
						) .
						// User profile links
						Html::rawElement(
							'div',
							[ 'id' => 'user-tools' ],
							$this->getUserLinks()
						) .
						$this->getSearch() .
						// Global navigation
						Html::rawElement(
							'div',
							[ 'id' => 'global-navigation' ],
							$this->getGlobalLinks()

						) .
						Html::element( 'div', [ 'id' => 'main-menu-toggle' ] ) .
						Html::element( 'div', [ 'id' => 'personal-menu-toggle' ] ) .
						$this->getClear()
					)
				) )
			) .
			Html::rawElement( 'div', [ 'id' => 'mw-column' ],
			Html::rawElement( 'div', [ 'id' => 'mw-sidebar-outer' ],
				Html::rawElement ( 'div', [ 'id' => 'mw-sidebar' ],
					$this->getBanner() .
					// Site navigation/sidebar
					Html::rawElement(
						'div',
						[ 'id' => 'site-navigation' ],
						$this->getSiteNavigation() .
						// Toolbox
						$this->getPortlet(
							'tb',
							$this->getToolbox(),
							'toolbox'
						)
					) .
					$this->getClear()
				)
			).
			Html::rawElement( 'div', [ 'id' => 'content-outer' ],
				// Page editing and tools
				Html::rawElement( 'div', [ 'id' => 'page-tools' ],
					Html::rawElement( 'div', [ 'id' => 'page-tools-left' ],
						$this->getPortlet(
							'namespaces',
							$this->data['content_navigation']['namespaces']
						)
					) .
					$this->getPageToolsRight() .
					$this->getClear()
				) .
				Html::rawElement( 'div', [ 'class' => 'mw-body', 'id' => 'content', 'role' => 'main' ],
					$this->getSiteNotice() .
					$this->getNewTalk() .
					$this->getIndicators() .
					Html::rawElement( 'h1',
						[
							'class' => 'firstHeading',
							'lang' => $this->get( 'pageLanguage' )
						],
						$this->get( 'title' )
					) .
					Html::rawElement( 'div', [ 'id' => 'siteSub' ],
						$this->getMsg( 'tagline' )->parse()
					) .
					Html::rawElement( 'div', [ 'class' => 'mw-body-content' ],
						Html::rawElement( 'div', [ 'id' => 'contentSub' ],
							$this->getPageSubtitle() .
							Html::rawElement(
								'p',
								[],
								$this->get( 'undelete' )
							)
						) .
						$this->get( 'bodycontent' ) .
						$this->getClear() .
						Html::rawElement( 'div', [ 'class' => 'printfooter' ],
							$this->get( 'printfooter' )
						) .
						$this->getCategoryLinks() .
						$this->getDataAfterContent() .
						$this->get( 'debughtml' )
					)
				)
			) ) .
			Html::rawElement( 'div', [ 'id' => 'footer-outer-outer' ],
				Html::rawElement( 'div', [ 'id' => 'footer-outer' ],
					$this->getFooterBlock()
				)
			)
		);

		$html .= $this->getTrail();
		$html .= Html::closeElement( 'body' );
		$html .= Html::closeElement( 'html' );

		echo $html;
	}

	/**
	 * Generates the logo image
	 *
	 * @param string $id ID for the element
	 *
	 * @return string HTML
	 */
	protected function getLogo( $id = 'p-logo' ) {
		$html = Html::openElement(
			'div',
			[
				'id' => $id,
				'class' => 'mw-portlet',
				'role' => 'banner'
			]
		);
		$html .= Html::element(
			'a',
			[
				'href' => $this->data['nav_urls']['mainpage']['href'],
				'class' => 'mw-wiki-logo',
			] + Linker::tooltipAndAccesskeyAttribs( 'p-logo' )
		);

		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Generates the site title banner
	 *
	 * @param string $id ID for the element
	 *
	 * @return string HTML
	 */
	protected function getBanner( $id = 'p-banner' ) {
		$html = Html::openElement(
			'div',
			[
				'id' => $id,
				'class' => 'mw-portlet',
				'role' => 'banner'
			]
		);

		$language = $this->getSkin()->getLanguage();
		$siteTitle = $language->convert( $this->getMsg( 'sitetitle' )->escaped() );

		$html .= Html::element(
			'a',
			[
				'class' => 'mw-wiki-title',
				'href' => $this->data['nav_urls']['mainpage']['href']
			] + Linker::tooltipAndAccesskeyAttribs( 'p-logo' ),
			$siteTitle
		);
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Generates the search form
	 *
	 * @return string HTML
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
			Html::label( $this->getMsg( 'search' )->escaped(), 'searchInput' )
		);
		$html .= $this->makeSearchInput( [ 'id' => 'searchInput' ] );
		$html .= $this->makeSearchButton( 'go', [ 'id' => 'searchGoButton', 'class' => 'searchButton' ] );
		$html .= Html::closeElement( 'form' );

		return $html;
	}

	/**
	 * Generates the sidebar
	 * Set the elements to true to allow them to be part of the sidebar
	 * Or get rid of this entirely, and take the specific bits to use wherever you actually want them
	 *  * Toolbox is the page/site tools that appears under the sidebar in vector
	 *  * Languages is the interlanguage links on the page via en:... es:... etc
	 *  * Default is each user-specified box as defined on MediaWiki:Sidebar; you will still need a
	 *    foreach loop to parse these.
	 *
	 * @return string HTML
	 */
	protected function getSiteNavigation() {
		$html = '';

		$sidebar = $this->getSidebar();
		$sidebar['SEARCH'] = false;
		$sidebar['TOOLBOX'] = false;
		$sidebar['LANGUAGES'] = false;

		foreach ( $sidebar as $name => $content ) {
			if ( $content === false ) {
				continue;
			}
			// Numeric strings gets an integer when set as key, cast back - T73639
			$name = (string)$name;
			$html .= $this->getPortlet( $name, $content['content'] );
		}
		return $html;
	}

	/**
	 * Print arbitrary block of navigation
	 * Message parsing is limited to first 10 lines only for this skin.
	 *
	 * @param string $linksMessage
	 * @param string $id
	 * @param bool $doubleHeader Stupid mobile hack
	 */
	protected function getNavigation( $linksMessage, $id, $doubleHeader = false ) {
		$message = trim( $this->getMsg( $linksMessage )->text() );
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
					$item['class'] = htmlspecialchars( Sanitizer::escapeIdForAttribute( $line_temp[2] ) );
				}
			} else {
				$line = $line_temp[0];
				$link = $line_temp[0];
			}
			$item['id'] =  htmlspecialchars( Sanitizer::escapeIdForAttribute( $line ) );

			// Determine what to show as the human-readable link description
			if ( $this->getMsg( $line )->isDisabled() ) {
				// It's *not* the name of a MediaWiki message, so display it as-is
				$item['text'] = $line;
			} else {
				// Guess what -- it /is/ a MediaWiki message!
				$item['text'] = $this->getMsg( $line )->text();
			}

			if ( $link !== null ) {
				if ( $this->getMsg( $line_temp[0] )->isDisabled() ) {
					$link = $line_temp[0];
				}
				if ( Skin::makeInternalOrExternalUrl( $link ) ) {
					$href = $link;
				} else {
					$title = Title::newFromText( $link );
					if ( $title ) {
						$title = $title->fixSpecialName();
						$href = $title->getLocalURL();
					} else {
						$href = '#';
					}
				}
			}
			$item['href'] = $href;

			$links[] = $item;
		}

		return $this->getPortlet( $id, $links, null, [ 'extra-header' => $doubleHeader ] );
	}

	/**
	 * Menu for global navigation (for cross-wiki stuff or just whatever things)
	 *
	 * @return string HTML
	 */
	protected function getGlobalLinks() {
		$html = '';
		if ( !$this->getMsg( 'global-links-menu' )->isDisabled() ) {
			$html = $this->getNavigation( 'global-links-menu', 'global-links', true );
		}

		return $html;
	}

	/**
	 * In other languages list
	 *
	 * @return string HTML
	 */
	protected function getLanguageLinks() {
		$html = '';
		if ( $this->data['language_urls'] !== false ) {
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
	 * if using a language where this is applicable.
	 *
	 * @return string HTML
	 */
	protected function getVariants() {
		$html = '';
		if ( count( $this->data['content_navigation']['variants'] ) > 0 ) {
			$html .= $this->getPortlet(
				'variants',
				$this->data['content_navigation']['variants'],
				null,
				[ 'extra-header' => true ]
			);
		}

		return $html;
	}

	/**
	 * Right cactions thing, if any
	 * (Avoid wrapping an empty string)
	 *
	 * @return string HTML
	 */
	protected function getPageToolsRight() {
		$html = '';

		$views = $this->data['content_navigation']['views'];
		$actions = $this->data['content_navigation']['actions'];
		if ( isset( $actions['unwatch'] ) ) {
			$views['unwatch'] = $actions['unwatch'];
			unset( $actions['unwatch'] );
		}
		if ( isset( $actions['watch'] ) ) {
			$views['watch'] = $actions['watch'];
			unset( $actions['watch'] );
		}

		$junk = $this->getPortlet( 'views', $views ) .
			$this->getPortlet( 'actions', $actions, null, [ 'extra-header' => true ] ) .
			$this->getVariants() .
			$this->getLanguageLinks();

		if ( strlen( $junk ) ) {
			$html = Html::rawElement( 'div', [ 'id' => 'page-tools-right' ], $junk );
		}

		return $html;
	}

	/**
	 * Generates user tools menu
	 *
	 * @return string HTML
	 */
	protected function getUserLinks() {
		$user = $this->getSkin()->getUser();
		$personalTools = $this->getPersonalTools();

		$html = '';

		// Move Echo badges out of default list - they should be visible outside of dropdown;
		// may not even work at all inside one
		$extraTools = [];
		if ( isset( $personalTools['notifications-alert'] ) ) {
			$extraTools['notifications-alert'] = $personalTools['notifications-alert'];
			unset( $personalTools['notifications-alert'] );
		}
		if ( isset( $personalTools['notifications-notice'] ) ) {
			$extraTools['notifications-notice'] = $personalTools['notifications-notice'];
			unset( $personalTools['notifications-notice'] );
		}
		// Move ULS trigger if you want to better support the user options trigger
		if ( isset( $personalTools['uls'] ) ) {
			$extraTools['uls'] = $personalTools['uls'];
			unset( $personalTools['uls'] );
		}

		// Re-label some messages
		if ( isset( $personalTools['userpage'] ) ) {
			$personalTools['userpage']['links'][0]['text'] = $this->getMsg( 'woogleshades-userpage' )->text();
		}
		if ( isset( $personalTools['mytalk'] ) ) {
			$personalTools['mytalk']['links'][0]['text'] = $this->getMsg( 'woogleshades-talkpage' )->text();
		}

		// Dropdown header
		if ( $user->isLoggedIn() ) {
			$headerMsg = [ 'woogleshades-loggedinas', $user->getName() ];
		} else {
			$headerMsg = [ 'woogleshades-notloggedin', $user->getName() ];
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

		$html .= $this->getPortlet( 'personal', $personalTools, $headerMsg );

		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Generates siteNotice, if any
	 *
	 * @return string HTML
	 */
	protected function getSiteNotice() {
		return $this->getIfExists( 'sitenotice', [
			'wrapper' => 'div',
			'parameters' => [ 'id' => 'siteNotice' ]
		] );
	}

	/**
	 * Generates new talk message banner, if any
	 *
	 * @return string HTML
	 */
	protected function getNewTalk() {
		return $this->getIfExists( 'newtalk', [
			'wrapper' => 'div',
			'parameters' => [ 'class' => 'usermessage' ]
		] );
	}

	/**
	 * Generates subtitle stuff, if any
	 *
	 * @return string HTML
	 */
	protected function getPageSubtitle() {
		return $this->getIfExists( 'subtitle', [ 'wrapper' => 'p' ] );
	}

	/**
	 * Generates category links, if any
	 *
	 * @return string HTML
	 */
	protected function getCategoryLinks() {
		return $this->getIfExists( 'catlinks' );
	}

	/**
	 * Generates data after content stuff, if any
	 *
	 * @return string HTML
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
	 * @return string HTML
	 */
	protected function getIfExists( $object, $setOptions = [] ) {
		$options = $setOptions + [
			'wrapper' => 'none',
			'parameters' => []
		];

		$html = '';

		if ( $this->data[$object] ) {
			if ( $options['wrapper'] == 'none' ) {
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
	 * Generates a block of navigation links with a header
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
			'class' => 'mw-portlet',
			'extra-classes' => '',
			// what to wrap the body list in, if anything
			'body-wrapper' => 'div',
			'body-id' => null,
			'body-class' => 'mw-portlet-body',
			// makeListItem options
			'list-item' => [ 'text-wrapper' => [ 'tag' => 'span' ] ],
			// option to stick arbitrary stuff at the beginning of the ul
			'list-prepend' => '',
			// old toolbox hook support (use: [ 'SkinTemplateToolboxEnd' => [ &$skin, true ] ])
			'hooks' => '',
			'extra-header' => false
		];

		// Handle the different $msg possibilities
		if ( $msg === null ) {
			$msg = $name;
		} elseif ( is_array( $msg ) ) {
			$msgString = array_shift( $msg );
			$msgParams = $msg;
			$msg = $msgString;
		}
		$msgObj = $this->getMsg( $msg );
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
			// Compatibility with extensions still using SkinTemplateToolboxEnd or similar
			if ( is_array( $options['hooks'] ) ) {
				foreach ( $options['hooks'] as $hook ) {
					if ( is_string( $hook ) ) {
						$hookOptions = [];
					} else {
						// it should only be an array otherwise
						$hookOptions = array_values( $hook )[0];
						$hook = array_keys( $hook )[0];
					}
					$contentText .= $this->deprecatedHookHack( $hook, $hookOptions );
				}
			}

			$contentText .= Html::closeElement( 'ul' );
		} elseif ( strlen( $content ) > 1 ) {
			$contentText = $content;
		} else {
			return '';
		}

		// Special handling for role=search and other weird things
		$divOptions = [
			'role' => 'navigation',
			'id' => Sanitizer::escapeIdForAttribute( $options['id'] ),
			'title' => Linker::titleAttrib( $options['id'] ),
			'aria-labelledby' => $labelId
		];
		if ( !is_array( $options['class'] ) ) {
			$class = [ $options['class'] ];
		}
		if ( !is_array( $options['extra-classes'] ) ) {
			$extraClasses = [ $options['extra-classes'] ];
		}
		$divOptions['class'] = array_merge( $class, $extraClasses );

		$labelOptions = [
			'id' => $labelId,
			'lang' => $this->get( 'userlang' ),
			'dir' => $this->get( 'dir' )
		];

		if ( $options['body-wrapper'] !== 'none' ) {
			$bodyDivOptions = [ 'class' => $options['body-class'] ];
			if ( is_string( $options['body-id'] ) ) {
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
	 * Wrapper to catch output of old hooks expecting to write directly to page
	 * We no longer do things that way.
	 *
	 * @param string $hook event
	 * @param array $hookOptions args
	 *
	 * @return string HTML
	 */
	protected function deprecatedHookHack( $hook, $hookOptions = [] ) {
		$hookContents = '';
		ob_start();
		Hooks::run( $hook, $hookOptions );
		$hookContents = ob_get_contents();
		ob_end_clean();
		if ( !trim( $hookContents ) ) {
			$hookContents = '';
		}

		return $hookContents;
	}

	/**
	 * Better renderer for getFooterIcons and getFooterLinks
	 *
	 * @param array $setOptions Miscellaneous other options
	 * * 'id' for footer id
	 * * 'order' to determine whether icons or links appear first: 'iconsfirst' or links, though in
	 *   practice we currently only check if it is or isn't 'iconsfirst'
	 * * 'link-prefix' to set the prefix for all link and block ids; most skins use 'f' or 'footer',
	 *   as in id='f-whatever' vs id='footer-whatever'
	 * * 'icon-style' to pass to getFooterIcons: "icononly", "nocopyright"
	 * * 'link-style' to pass to getFooterLinks: "flat" to disable categorisation of links in a
	 *   nested array
	 *
	 * @return string HTML
	 */
	protected function getFooterBlock( $setOptions = [] ) {
		// Set options and fill in defaults
		$options = $setOptions + [
			'id' => 'footer',
			'order' => 'iconsfirst',
			'link-prefix' => 'footer',
			'icon-style' => 'icononly',
			'link-style' => null
		];

		$validFooterIcons = $this->getFooterIcons( $options['icon-style'] );
		$validFooterLinks = $this->getFooterLinks( $options['link-style'] );

		$html = '';

		$html .= Html::openElement( 'div', [
			'id' => $options['id'],
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
			if ( $options['link-style'] == 'flat' ) {
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

		if ( $options['order'] == 'iconsfirst' ) {
			$html .= $iconsHTML . $linksHTML;
		} else {
			$html .= $linksHTML . $iconsHTML;
		}

		$html .= $this->getClear() . Html::closeElement( 'div' );

		return $html;
	}
}
