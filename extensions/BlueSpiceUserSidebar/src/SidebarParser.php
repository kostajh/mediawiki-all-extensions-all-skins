<?php

namespace BlueSpice\UserSidebar;

class SidebarParser {
	const LINK_INTERNAL = 'internal';
	const LINK_EXTERNAL = 'external';
	const WIDGET = 'widget';
	const SECTION = 'section';

	/**
	 *
	 * @var \Title
	 */
	protected $title;

	/**
	 *
	 * @var array
	 */
	protected $widgetRegistry;

	/**
	 *
	 * @var array
	 */
	protected $textLines = [];

	/**
	 *
	 * @var array
	 */
	protected $links = [];

	/**
	 *
	 * @param \Title $title
	 * @param array $widgetRegistry
	 */
	public function __construct( \Title $title, $widgetRegistry ) {
		$this->title = $title;
		$this->widgetRegistry = $widgetRegistry;
	}

	public function parse() {
		$this->getPageTextLines();
		$this->parseInternaly();
	}

	/**
	 *
	 * @return array
	 */
	public function getItems() {
		return $this->links;
	}

	/**
	 *
	 * @return array|null
	 */
	protected function getPageTextLines() {
		if ( $this->title->exists() === false ) {
			return [];
		}
		$wikiPage = \WikiPage::factory( $this->title );
		$content = $wikiPage->getContent()->getNativeData();

		$content = preg_replace( '#<noinclude>.*?<\/noinclude>#si', '', $content );

		$this->textLines = explode( "\n", $content );
	}

	protected function parseInternaly() {
		$section = '';
		$this->links = [];
		foreach ( $this->textLines as $line ) {
			$depth = 0;
			$isIndentCharacter = true;

			do {
				if ( isset( $line[$depth] ) && $line[$depth] == '*' ) {
					$depth++;
				} else {
					$isIndentCharacter = false;
				}
			}
			while ( $isIndentCharacter );

			$line = trim( substr( $line, $depth ) );

			if ( empty( $line ) ) {
				continue;
			}

			$type = $this->getLineType( $line );
			if ( $depth === 1 ) {
				if ( $type === self::SECTION ) {
					$section = $line;
					continue;
				}
				$section = '';
			}

			if ( $type === self::LINK_INTERNAL ) {
				$this->links[$section][] = $this->parseInternalLink( $line );
			} elseif ( $type === self::LINK_EXTERNAL ) {
				$this->links[$section][] = $this->parseExternalLink( $line );
			} else {
				$widgetData = $this->parseWidget( $line );
				$this->links[$widgetData['name']] = $widgetData;
			}
		}
	}

	/**
	 *
	 * @param string $line
	 * @return array
	 */
	protected function parseInternalLink( $line ) {
		$item = [];
		$line = ltrim( $line, '[[' );
		$line = rtrim( $line, ']]' );

		$elements = explode( '|', $line );
		$item['classes'] = ' bs-usersidebar-internal ';

		$title = \Title::newFromText( $elements[0] );
		if ( !$title ) {
			return [];
		}

		$item['href'] = $title->getFullURL();

		if ( isset( $elements[1] ) ) {
			$item['text'] = $elements[1];
			$item['title'] = $elements[1];
		} else {
			$item['text'] = $title->getText();
			$item['title'] = $title->getText();
		}

		return $item;
	}

	/**
	 *
	 * @param string $line
	 * @return array|false
	 */
	protected function parseExternalLink( $line ) {
		$item = [];
		if ( strpos( $line, '[' ) === 0 ) {
			$line = ltrim( $line, '[' );
			$line = rtrim( $line, ']' );
			$elements = explode( ' ', $line );
		} else {
			$elements = explode( '|', $line );
		}

		$item['classes'] = ' bs-usersidebar-external ';
		$item['href'] = $elements[0];

		array_shift( $elements );
		if ( empty( $elements ) ) {
			return false;
		}

		$element = implode( ' ', $elements );
		$item['text'] = $element;
		$item['title'] = $element;

		return $item;
	}

	/**
	 *
	 * @param string $line
	 * @return array|false
	 */
	protected function parseWidget( $line ) {
		return $this->parseWidgetLine( $line );
	}

	/**
	 *
	 * @param string $line
	 * @return string
	 */
	protected function getLineType( $line ) {
		if ( substr( $line, 0, 1 ) === '[' ) {
			if ( substr( $line, 0, 2 ) === '[[' ) {
				return self::LINK_INTERNAL;
			}
			return self::LINK_EXTERNAL;
		}

		// If its not a link it can be widget keyword or section
		$widgetKeyword = $this->parseWidgetLine( $line );
		if ( $widgetKeyword !== false ) {
			return self::WIDGET;
		}

		// Its a link without square brackets
		$bits = explode( '|', $line );
		if ( count( $bits ) > 1 ) {
			// Maybe better to check for double slash,
			// but its no impossible for page to have such name
			if ( substr( $line, 0, 4 ) === 'http' ) {
				return self::LINK_EXTERNAL;
			}
			return self::LINK_INTERNAL;
		}

		return self::SECTION;
	}

	/**
	 *
	 * @param string $line
	 * @return array|false
	 */
	protected function parseWidgetLine( $line ) {
		if ( substr( $line, 0, 1 ) === '[' ) {
			return false;
		}
		$bits = explode( '|', $line );
		$name = array_shift( $bits );

		if ( !isset( $this->widgetRegistry[$name] ) ) {
			return false;
		}

		$res = [
			'name' => $name,
			'callback' => $this->widgetRegistry[$name]['callback'],
			'params' => []
		];
		if ( empty( $bits ) ) {
			return $res;
		}

		$params = [];
		foreach ( $bits as $bit ) {
			$paramBits = explode( '=', $bit );
			$paramName = array_shift( $paramBits );
			$params[$paramName] = !empty( $paramBits ) ? $paramBits[0] : '';

		}

		$res['params'] = $params;
		return $res;
	}
}
