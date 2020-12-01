<?php

namespace BlueSpice\CustomMenu\Renderer\Menu;

use BlueSpice\Data\RecordSet;
use BlueSpice\Renderer\Params;
use Config;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;

class Item extends \BlueSpice\CustomMenu\Renderer\Menu {
	const PARAM_HREF = 'href';
	const PARAM_CHILDREN = 'children';
	const PARAM_LEVEL = 'level';
	const PARAM_EXTERNAL = 'external';
	const PARAM_CONTAINS_ACTIVE = 'containsactive';
	const PARAM_IS_ACTIVE = 'active';
	const PARAM_TEXT = 'text';

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '' ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name );
		$this->args[static::PARAM_TAG] = 'li';
		$this->args[static::PARAM_CLASS] = '';
		$this->args[static::PARAM_HREF] = $params->get(
			static::PARAM_HREF,
			'#'
		);
		$this->args[static::PARAM_CHILDREN] = $params->get(
			static::PARAM_CHILDREN,
			false
		);
		$this->args[static::PARAM_LEVEL] = $params->get(
			static::PARAM_LEVEL,
			0
		);
		$this->args[static::PARAM_EXTERNAL] = $params->get(
			static::PARAM_EXTERNAL,
			false
		);
		$this->args[static::PARAM_CONTAINS_ACTIVE] = $params->get(
			static::PARAM_CONTAINS_ACTIVE,
			false
		);
		$this->args[static::PARAM_IS_ACTIVE] = $params->get(
			static::PARAM_IS_ACTIVE,
			false
		);
		$this->args[static::PARAM_TEXT] = $params->get(
			static::PARAM_TEXT,
			''
		);
		if (
			empty( $this->args[static::PARAM_TEXT] )
			&& !empty( $this->args[static::PARAM_ID] )
		) {
			$this->args[static::PARAM_TEXT] = $this->args[static::PARAM_ID];
		}
		$this->args[static::PARAM_CLASS]
			.= " level-{$this->args[static::PARAM_LEVEL]}";
		if ( $this->hasChildren() ) {
			$this->args[static::PARAM_CLASS] .= ' contains-children';
		}
	}

	/**
	 *
	 * @return bool
	 */
	protected function hasChildren() {
		$numLevels = $this->getCustomMenu()->numberOfLevels();
		$curLevel = $this->args[static::PARAM_LEVEL];
		if ( $curLevel >= $numLevels ) {
			return false;
		}
		return !empty( $this->args[static::PARAM_CHILDREN] );
	}

	/**
	 *
	 * @return string HTML
	 */
	protected function makeTagContent() {
		$level = $this->args[static::PARAM_LEVEL] + 1;
		$content = '';
		$content .= $this->makeItemAnchor();

		if ( !$this->args[static::PARAM_CHILDREN] instanceof RecordSet ) {
			return $content;
		}
		$level = $this->args[static::PARAM_LEVEL] + 1;
		$content .= $this->makeChildMenuOpeningTag( $level );
		$counter = 0;
		$menu = $this->getCustomMenu();
		if ( $menu->numberOfLevels() >= $level ) {
			foreach ( $this->args[static::PARAM_CHILDREN]->getRecords() as $record ) {
				$counter++;
				$content .= $this->renderItem( $record );
				if ( $menu->numberOfSubEntries() == $menu::NUM_ENTRIES_UNLIMITED ) {
					continue;
				}
				if ( $menu->numberOfSubEntries() <= $counter ) {
					break;
				}
			}
		}
		$content .= $this->makeChildMenuClosingTag();

		return $content;
	}

	/**
	 *
	 * @return string HTML
	 */
	protected function makeItemAnchor() {
		return Html::element(
			'a',
			$this->makeItemAnchorAttribs(),
			$this->args[static::PARAM_TEXT]
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function makeItemAnchorAttribs() {
		$attribs = [
			static::PARAM_HREF => $this->args[static::PARAM_HREF],
			'title' => $this->args[static::PARAM_TEXT]
		];

		if ( !$this->args[static::PARAM_EXTERNAL] ) {
			return $attribs;
		}

		$attribs['target'] = $this->config->get( 'ExternalLinkTarget' );

		return $attribs;
	}

	/**
	 *
	 * @param int $level
	 * @return string HTML
	 */
	protected function makeChildMenuOpeningTag( $level ) {
		return Html::openElement(
			'ul',
			$this->makeChildMenuOpeningTagAttribs( $level )
		);
	}

	/**
	 *
	 * @return string HTML
	 */
	protected function makeChildMenuClosingTag() {
		return Html::closeElement( 'ul' );
	}

	/**
	 *
	 * @param int $level
	 * @return array
	 */
	protected function makeChildMenuOpeningTagAttribs( $level ) {
		return [
			static::PARAM_CLASS => " child-menu level-$level",
		];
	}

}
