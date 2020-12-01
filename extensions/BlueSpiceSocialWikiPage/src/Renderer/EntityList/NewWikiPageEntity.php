<?php

namespace BlueSpice\Social\WikiPage\Renderer\EntityList;

use BlueSpice\Renderer\Params;
use Config;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;

class NewWikiPageEntity extends \BlueSpice\Social\Renderer\EntityList {

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

		if ( !$this->getContext()->getTitle()->exists() ) {
			$this->args[static::PARAM_CLASS] .= ' nowikipage';
		}
	}

	/**
	 * Returns a rendered template as HTML markup
	 * @return string - HTML
	 */
	public function render() {
		$content = '';
		if ( $this->args[ static::PARAM_SHOW_HEADLINE ] ) {
			$content .= $this->renderEntityListHeadline();
		}
		$content .= $this->getOpenTag();
		$content .= $this->makeTagContent();
		$content .= $this->getCloseTag();
		if ( $this->args[ static::PARAM_SHOW_ENTITY_LIST_MORE ] ) {
			$content .= $this->renderEntityListMore();
		}

		return $content;
	}

	protected function makeTagContent() {
		$content = '';
		$content .= Html::openElement( 'li' );
		$content .= $this->renderNewWikiPageEntity();
		$content .= Html::closeElement( 'li' );
		return $content;
	}

	protected function renderNewWikiPageEntity() {
		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'social-wikipage-createnewwikipageentity',
			new Params( [ 'context' => $this->getContext() ] )
		);
		return $renderer->render();
	}

}
