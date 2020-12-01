<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\AssocLinksCollector;
use BlueSpice\ExtensionAttributeBasedRegistry;
use Skins\Chameleon\IdRegistry;

class AssocLinks extends BasePanel {

	protected $bodyHtml = '';

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return new \Message( 'bs-sitetools-associated-links' );
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$this->initBodyHtml();
		return $this->bodyHtml;
	}

	protected $bodyHtmlAlreadyInitialized = false;

	private function initBodyHtml() {
		if ( $this->bodyHtmlAlreadyInitialized ) {
			return;
		}

		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationAssocLinksProviderRegistry'
		);

		$this->skintemplate instanceof \BaseTemplate;

		$collector = new AssocLinksCollector(
			$registry,
			$this->skintemplate->getSkin()->getContext(),
			$this->skintemplate->getSkin()->getConfig()
		);

		$links = new LinkList(
			'dummy-id',
			[
				'content' => $collector->getLinkDefs()
			]
		);

		$this->bodyHtml = $links->getBody();
		$this->bodyHtmlAlreadyInitialized = true;
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ) {
		$this->initBodyHtml();
		if ( empty( $this->bodyHtml ) ) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * @var string
	 */
	protected $htmlId = null;

	/**
	 * The HTML ID for the component
	 * @return string
	 */
	public function getHtmlId() {
		if ( $this->htmlId === null ) {
			$this->htmlId = IdRegistry::getRegistry()->getId( 'bs-associated-links' );
		}
		return $this->htmlId;
	}
}
