<?php

namespace BlueSpice\Calumma\Renderer\PageHeader;

use BlueSpice\Calumma\AssocLinksCollector;
use BlueSpice\Calumma\Controls\SplitButtonDropdown;
use BlueSpice\Calumma\Renderer\PageHeader;
use BlueSpice\ExtensionAttributeBasedRegistry;
use Html;
use Message;

class Context extends PageHeader {

	protected $html = '';

	public function render() {
		if ( $this->getContext()->getTitle()->isSpecialPage() ) {
			return '';
		}

		$this->makePageButton();
		$this->makeDiscussionButton();
		return $this->html;
	}

	private function makePageButton() {
		$items = $this->getAssocLinks();
		$data = [
			'id' => 'bs-page-context-button',
			'title' => Message::newFromKey( 'bs-calumma-context-pagebutton-tooltip' )->plain(),
			'text' => Message::newFromKey( 'bs-calumma-context-pagebutton-label' )->plain(),
			'href' => $this->context->getTitle()->getSubjectPage()->getLinkURL(),
			'hasItems' => !empty( $items ),
			'items' => $items,
			'class' => 'btn',
			'isLabel' => !$this->getContext()->getTitle()->isTalkPage()
		];
		$splitButton = new SplitButtonDropdown( $this->skinTemplate, $data );

		$this->html .= $splitButton->getHtml();
	}

	private function makeDiscussionButton() {
		if ( $this->getContext()->getTitle()->isTalkPage() ) {
			$this->html .= $this->getDiscussionSpan();
		} else {
			$this->html .= $this->getDiscussionLink();
		}
	}

	/**
	 * @return string
	 */
	private function getDiscussionSpan() {
		$span = Html::element(
			'span',
			[ 'id' => 'bs-page-discussion-button' ],
			Message::newFromKey( 'bs-calumma-context-discussionbutton-label' )->plain()
		);

		return $span;
	}

	/**
	 * @return string
	 */
	private function getDiscussionLink() {
		$link = $this->linkRenderer->makeLink(
			$this->getContext()->getTitle()->getTalkPage(),
			Message::newFromKey( 'bs-calumma-context-discussionbutton-label' )->plain(),
			[
				'id' => 'bs-page-discussion-button',
				'title' => Message::newFromKey( 'bs-calumma-context-discussionbutton-tooltip' )
					->plain(),
				'class' => 'btn'
			]
		);

		return $link;
	}

	private function getAssocLinks() {
		$linkDefs = [];

		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationAssocLinksProviderRegistry'
		);
		$collector = new AssocLinksCollector( $registry, $this->context, $this->config );

		foreach ( $collector->getLinkDefs() as $linkDef ) {
			$linkDefs[] = [
				'link' => $linkDef
			];
		}

		return $linkDefs;
	}

}
