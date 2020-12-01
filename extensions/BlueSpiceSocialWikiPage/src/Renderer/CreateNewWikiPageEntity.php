<?php

namespace BlueSpice\Social\WikiPage\Renderer;

use BlueSpice\Renderer\Params;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use OutputPage;
use RequestContext;

class CreateNewWikiPageEntity extends \BlueSpice\Renderer {
	const PARAM_CONTEXT = 'context';

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

		$this->context = $params->get(
			static::PARAM_CONTEXT,
			false
		);
		if ( !$this->context instanceof IContextSource ) {
			$this->context = RequestContext::getMain();
		}
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

	protected function makeTagContent() {
		$content = '';
		OutputPage::setupOOUI();

		$title = $this->getContext()->getTitle();
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialWikiPageEntityFactory'
		);
		$entity = $factory->newFromTitle( $title );
		if ( !$entity->userCan( 'create', $this->getContext()->getUser() )->isOK() ) {
			$msg = $this->msg( 'bs-socialwikipage-nowikipageentity' );
			$content .= new \OOUI\LabelWidget( [
				'label' => $msg->plain(),
			] );
			return $content;
		}

		$label = $this->msg(
			'bs-socialwikipage-entitywikipage-header-create'
		)->plain();
		$btn = new \OOUI\ButtonWidget( [
			'infusable' => false,
			'label' => $label,
			'title' => $label,
			'framed' => false,
			'icon' => 'add',
			'invisibleLabel' => true,
			'flags' => [
				'primary',
				'progressive'
			],
			'href' => $title->getLocalURL( [
				'action' => 'edit',
			] )
		] );
		$btn->addClasses( [
			'bs-socialwikipage-wikipage-create'
		] );
		$btn->setValue(
			$this->getContext()->getTitle()->getArticleID()
		);
		$content .= $btn;
		return $content;
	}

}
