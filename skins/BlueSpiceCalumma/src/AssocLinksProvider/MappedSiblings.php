<?php

namespace BlueSpice\Calumma\AssocLinksProvider;

use BlueSpice\Html\Descriptor\TitleLink;
use Message;
use RawMessage;
use Title;

class MappedSiblings extends TitleLink {

	/**
	 *
	 * @return Message
	 */
	public function getLabel() {
		if ( $this->title->getNamespace() === NS_MAIN ) {
			return Message::newFromKey( 'bs-ns_main' );
		}
		return new RawMessage( $this->title->getNsText() );
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @return ILink[]
	 */
	public static function factory( $context, $config ) {
		$currentTitle = $context->getTitle();
		$namespaces = $config->get(
			'BlueSpiceCalummaAssocLinksMappedSiblingsNamespaceMap'
		);
		$links = [];
		foreach ( $namespaces as $namespaceId ) {
			$assocTitle = Title::makeTitle( $namespaceId, $currentTitle->getDBkey() );
			if ( $assocTitle->equals( $currentTitle ) ) {
				continue;
			}
			$links["mapped-namespace-$namespaceId"] = new static( $context, $config, $assocTitle );
		}

		return $links;
	}
}
