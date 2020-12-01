<?php
namespace BlueSpice\Social\Resolve\Renderer;

use BlueSpice\Renderer\Params;
use BlueSpice\Social\Resolve\Item;
use Config;
use FormatJson;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MWException;
use User;

class Resolve extends \BlueSpice\Renderer {
	const PARAM_RESOLVE_ITEM = 'resolveitem';
	const PARAM_USER = 'user';

	/**
	 *
	 * @var Item
	 */
	protected $item = null;

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @var Params
	 */
	protected $params = null;

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

		$this->item = $params->get(
			static::PARAM_RESOLVE_ITEM,
			false
		);
		if ( !$this->item instanceof Item ) {
			throw new MWException(
				'\\BlueSpice\\Social\\Resolve\\Item required'
			);
		}
		$this->user = $params->get(
			static::PARAM_USER,
			false
		);
		if ( !$this->user instanceof User ) {
			throw new MWException( '\\User required' );
		}
		$this->args[static::PARAM_TAG] = 'a';
		if ( !$this->args[static::PARAM_CLASS] ) {
			$this->args[static::PARAM_CLASS] = '';
		}
		$this->args[static::PARAM_CLASS] .= ' bs-social-entityaftercontent-resolve';
		if ( $this->item->isResolved() ) {
			$this->args[static::PARAM_CLASS] .= ' resolved';
		}
	}

	/**
	 *
	 * @return string - HTML
	 */
	public function render() {
		$content = '';
		$content .= $this->getOpenTag();
		$content .= $this->makeTagContent();
		$content .= $this->getCloseTag();

		return $content;
	}

	protected function makeTagContent() {
		return Html::element( 'span', [], \Message::newFromKey(
			$this->item->isResolved()
				? 'bs-socialresolve-status-resolved'
				: 'bs-socialresolve-status-notresolved'
			)->plain()
		);
	}

	protected function makeTagAttribs() {
		$attrbs = [];
		$attrbs['data-resolve'] = FormatJson::encode( $this->item );
		return array_merge( $attrbs, parent::makeTagAttribs() );
	}

	/**
	 *
	 * @return \User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 *
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}
}
