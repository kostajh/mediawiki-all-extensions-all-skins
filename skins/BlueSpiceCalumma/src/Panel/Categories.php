<?php

namespace BlueSpice\Calumma\Panel;

use MediaWiki\MediaWikiServices;
use Skins\Chameleon\IdRegistry;

class Categories extends BasePanel {

	protected $bodyHtml = '';

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return new \Message( 'bs-sitetools-categories' );
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

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$categoryNames = $this->skintemplate->getSkin()->getOutput()->getCategories( 'normal' );
		$categoryLinks = [];
		foreach ( $categoryNames as $categoryName ) {
			$title = \Title::makeTitle( NS_CATEGORY, $categoryName );
			$link = $linkRenderer->makeLink( $title, $title->getText(), [
				'class' => 'pill'
			] );
			$categoryLinks[] = $link;
		}

		$this->bodyHtml = implode( '', $categoryLinks );

		if ( !empty( $this->bodyHtml ) ) {
			// Workaround for styling
			$this->bodyHtml = "<div class=\"flyout-body-hint\">{$this->bodyHtml}</div>";
		}

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
	 * The HTML ID for thie component
	 * @return string
	 */
	public function getHtmlId() {
		if ( $this->htmlId === null ) {
			$this->htmlId = IdRegistry::getRegistry()->getId( 'bs-category-links' );
		}
		return $this->htmlId;
	}
}
