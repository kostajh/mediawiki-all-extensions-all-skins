<?php

namespace BlueSpice\CustomMenu\Renderer;

use BlueSpice\CustomMenu\ICustomMenu;
use BlueSpice\Data\Record;
use BlueSpice\Renderer\Params;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use MWException;

class Menu extends \BlueSpice\Renderer {
	const PARAM_CUSTOM_MENU = 'custommenu';

	/**
	 * @var ICustomMenu
	 */
	protected $customMenu = null;

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
		$this->customMenu = $params->get(
			static::PARAM_CUSTOM_MENU,
			false
		);
		if ( !$this->customMenu instanceof ICustomMenu ) {
			throw new MWException(
				"param '" . static::PARAM_CUSTOM_MENU . "' must be an instance of '" . ICustomMenu::class . "'"
			);
		}
		$this->args[static::PARAM_TAG] = 'ul';
		if ( empty( $this->args[static::PARAM_CLASS] ) ) {
			$this->args[static::PARAM_CLASS] = '';
		}
		$this->args[static::PARAM_CLASS]
			.= " bs-custom-menu {$this->customMenu->getKey()}";
	}

	/**
	 * Returns a rendered template as HTML markup
	 * @return string - HTML
	 */
	public function render() {
		$content = '';
		$content .= $this->getOpenTag();
		$content .= $this->makeTagContent();
		$content .= $this->getCloseTag();

		return $content;
	}

	/**
	 *
	 * @return string HTML
	 */
	protected function makeTagContent() {
		$content = '';
		$menu = $this->getCustomMenu();
		$counter = 0;
		foreach ( $menu->getData()->getRecords() as $record ) {
			$counter++;
			$content .= $this->renderItem( $record );
			if ( $menu->numberOfMainEntries() == $menu::NUM_ENTRIES_UNLIMITED ) {
				continue;
			}
			if ( $menu->numberOfMainEntries() <= $counter ) {
				break;
			}
		}

		return $content;
	}

	/**
	 * @param Record $record
	 * @return string
	 */
	protected function renderItem( Record $record ) {
		$params = array_merge(
			(array)$record->getData(),
			[ static::PARAM_CUSTOM_MENU => $this->getCustomMenu() ]
		);
		return MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			$this->makeItemRendererKey(),
			new Params( $params )
		)->render();
	}

	/**
	 * @return array
	 */
	protected function makeTagAttribs() {
		$attribs = [];
		return array_merge( $attribs, parent::makeTagAttribs() );
	}

	/**
	 * @return ICustomMenu
	 */
	public function getCustomMenu() {
		return $this->customMenu;
	}

	/**
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 *
	 * @return string
	 */
	protected function makeItemRendererKey() {
		return 'custommenuitem';
	}
}
