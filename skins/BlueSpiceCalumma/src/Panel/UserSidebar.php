<?php

namespace BlueSpice\Calumma\Panel;

use QuickTemplate;

class UserSidebar extends BasePanel {

	/**
	 *
	 * @var QuickTemplate
	 */
	protected $skintemplate = null;

	/**
	 *
	 * @param QuickTemplate $skintemplate
	 */
	public function __construct( QuickTemplate $skintemplate ) {
		$this->skintemplate = $skintemplate;
	}

	/**
	 *
	 * @return bool
	 */
	public function isEmpty() {
		$user = $this->skintemplate->getSkin()->getUser();
		if ( !$user->isLoggedIn() ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$user = $this->skintemplate->getSkin()->getUser();
		if ( !$user->isLoggedIn() ) {
			return '';
		}

		$linklist = [];
		$linklist = $this->getUserSidebarArray( $title );

		$html = '';

		foreach ( $linklist as $section => $links ) {
			$linklistgroup = new \BlueSpice\Calumma\Components\SimpleLinkListGroup( $links );

			$sectionId = str_replace( ' ', '-', $section );

			$collapsibleGroup = new \BlueSpice\Calumma\Components\CollapsibleGroup( [
				'id' => $sectionId,
				'title' => $section,
				'content' => $linklistgroup->getHtml()
					] );

			$html .= $collapsibleGroup->getHtml();
		}

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-usersidebar';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitenav-usersidebar-title' );
	}

	/**
	 * @param \Title $title
	 * @return array
	 */
	public function getUserSidebarArray( $title ) {
		if ( $title->exists() === false ) {
			return [];
		}
		$wikiPage = \WikiPage::factory( $title );
		$content = $wikiPage->getContent()->getNativeData();

		$content = preg_replace( '#<noinclude>.*?<\/noinclude>#si', '', $content );

		$lines = explode( "\n", $content );
		$links = [];

		foreach ( $lines as $line ) {
			$depth = 0;
			$isIndentCharacter = true;

			do {
				if ( isset( $line[$depth] ) && $line[$depth] == '*' ) {
					$depth++;
				} else {
					$isIndentCharacter = false;
				}
			} while ( $isIndentCharacter );

			$line = trim( substr( $line, $depth ) );

			if ( empty( $line ) ) {
				continue;
			}

			if ( $depth === 1 ) {
				$section = $line;
				continue;
			}

			$item = [];

			$external = false;
			if ( substr( $line, 0, 1 ) === '[' ) {
				$external = true;
			}
			if ( substr( $line, 0, 2 ) === '[[' ) {
				$external = false;
			}

			// external link
			if ( $external === true ) {
				$line = ltrim( $line, '[' );
				$line = rtrim( $line, ']' );

				$elements = explode( ' ', $line );
				$item['classes'] = ' bs-usersidebar-external ';
				$item['href'] = $elements[0];

				array_shift( $elements );
				if ( empty( $elements ) ) {
					continue;
				}

				$element = implode( ' ', $elements );
				$item['text'] = $element;
				$item['title'] = $element;

				$links[$section][] = $item;
				continue;
			}

			// internal link
			if ( $external === false ) {
				$line = ltrim( $line, '[[' );
				$line = rtrim( $line, ']]' );
			}

			$elements = explode( '|', $line );
			$item['classes'] = ' bs-usersidebar-internal ';

			$title = \Title::newFromText( $elements[0] );

			$item['href'] = $title->getFullURL();

			if ( isset( $elements[1] ) ) {
				$item['text'] = $elements[1];
				$item['title'] = $elements[1];
			} else {
				$item['text'] = $title->getText();
				$item['title'] = $title->getText();
			}

			$links[$section][] = $item;
		}

		return $links;
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ) {
		return !$context->getUser()->isAnon();
	}

}
