<?php

namespace BlueSpice\Social\Topics\Renderer;

use BlueSpice\Renderer\Params;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use OutputPage;
use RequestContext;

class CreateNewDiscussionPage extends \BlueSpice\Renderer {
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

		$title = $this->getContext()->getTitle()->getTalkPage();
		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'create', $this->getContext()->getUser(), $title )
		) {
			$msg = $this->msg( 'bs-socialtopics-nodiscussionpage' );
			$content .= new \OOUI\LabelWidget( [
				'label' => $msg->pLain(),
			] );
			return $content;
		}

		$label = $this->msg(
			'bs-socialtopics-entitydiscussion-header-create'
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
			'bs-socialtopics-discussionpage-create'
		] );
		$content .= $btn;
		return $content;
	}

}
