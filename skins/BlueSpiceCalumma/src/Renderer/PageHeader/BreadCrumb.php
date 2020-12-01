<?php

namespace BlueSpice\Calumma\Renderer\PageHeader;

use BlueSpice\Calumma\Controls\SplitButtonDropdown;
use BlueSpice\Calumma\IBreadcrumbRootNode;
use BlueSpice\Calumma\Renderer\PageHeader;
use BlueSpice\ExtensionAttributeBasedRegistry;
use Exception;
use Html;
use Title;
use WebRequest;

class BreadCrumb extends PageHeader {

	/**
	 * @return string
	 */
	public function render() {
		$html = '';
		$title = $this->getContext()->getTitle();
		if ( !$title ) {
			return $html;
		}
		if ( $title->isSpecialPage() ) {
			$title = $this->maybeSwapTitle( $title, $this->getContext()->getRequest() );
		}

		$html .= $this->makePortalLink( $title );
		$html .= $this->makeTitleLinks( $title );

		return $html;
	}

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	private function makePortalLink( Title $title ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationBreadcrumbRootNodeRegistry'
		);

		$enabledProviders = $this->config->get(
			'BlueSpiceCalummaBreadcrumbRootNodeEnabledProviders'
		);

		$breadcrumbRootNodes = [];
		foreach ( $registry->getAllValues() as $callbackKey => $callback ) {
			if ( !in_array( $callbackKey, $enabledProviders ) ) {
				continue;
			}
			$breadcrumbRootNode = call_user_func_array( $callback, [ $this->config ] );
			if ( $breadcrumbRootNode instanceof IBreadcrumbRootNode === false ) {
				throw new Exception( "Factory callback for '$callbackKey' did not return a "
					. "IBreadcrumbRootNode object" );
			}

			$rootNodeHtml = $breadcrumbRootNode->getHtml( $title );
			if ( !empty( $rootNodeHtml ) ) {
				return Html::rawElement(
					'div',
					[
						'id' => 'bs-breadcrumb-rootnode'
					],
					"$rootNodeHtml"
				);
			}
		}

		return '';
	}

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	private function makeTitleLinks( Title $title ) {
		$titleParts = explode( '/', $title->getText() );
		$splitButtons = [];

		$root = '';
		foreach ( $titleParts as $titlePart ) {
			$root .= $titlePart;
			$part = Title::newFromText( $root, $title->getNamespace() );
			$root .= '/';

			$path = '';
			if ( $part->getNamespace() === NS_MAIN ) {
				$path = ':';
			}
			$path .= $part->getPrefixedDBkey();
			$classes = [ 'bs-breadcrumb-node' ];
			if ( !$part->exists() && !$part->isSpecialPage() ) {
				$classes[] = 'new';
			}

			$splitButtons[] = new SplitButtonDropdown( $this->skinTemplate, [
				'classes' => $classes,
				'href' => $part->getLinkURL(),
				'text' => $part->getSubpageText(),
				'title' => $part->getPrefixedText(),
				// We assume that _all_ nodes have subpages, as also non existing ones will be listed
				// Only the leaf node must be checked explicitly
				'hasItems' => $title->equals( $part ) ? $part->hasSubpages() : true,
				'data' => [
					[
						'key' => 'bs-path',
						'value' => $path
					]
				]
			] );
		}

		$html = [];
		foreach ( $splitButtons as $splitButton ) {
			$html[] = $splitButton->getHtml();
		}

		$seperator = '';

		return implode( $seperator, $html );
	}

	/**
	 *
	 * @param Title $title
	 * @param WebRequest $request
	 * @return Title
	 */
	private function maybeSwapTitle( $title, $request ) {
		$newTitle = $title;

		// e.g Special:CiteThisPage&page=Main_Page
		$pageQueryString = $request->getVal( 'page', '' );
		if ( !empty( $pageQueryString ) ) {
			$newTitle = Title::newFromText( $pageQueryString );
		}

		// e.g Special:Duplicator&source=Main_Page
		$sourceQueryString = $request->getVal( 'source', '' );
		if ( !empty( $sourceQueryString ) ) {
			$newTitle = Title::newFromText( $sourceQueryString );
		}

		// e.g. "Special:MovePage/Help:Some/Page/With/Subpage"
		$titleParts = explode( '/', $title->getPrefixedText(), 2 );
		if ( !empty( $titleParts[1] ) ) {
			// e.g. "Help:Some/Page/With/Subpage"
			$newTitle = Title::newFromText( $titleParts[1] );
		}

		// e.g. Special:Browse/:Main-5FPage
		// TODO: This should be in `BlueSpiceSMWConnector`
		if ( $newTitle && class_exists( 'SMW\Encoder' ) ) {
			$newTitle = Title::newFromText(
				\SMW\Encoder::decode( $newTitle->getPrefixedText() )
			);
		}

		return $newTitle instanceof Title ? $newTitle : $title;
	}

}
