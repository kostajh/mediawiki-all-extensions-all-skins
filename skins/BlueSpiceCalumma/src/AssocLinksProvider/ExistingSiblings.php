<?php

namespace BlueSpice\Calumma\AssocLinksProvider;

use BlueSpice\Html\Descriptor\TitleLink;
use MediaWiki\MediaWikiServices;
use Message;
use RawMessage;
use Title;

class ExistingSiblings extends TitleLink {

	/**
	 *
	 * @return Message
	 */
	public function getLabel() {
		if ( $this->title->getNamespace() === NS_MAIN ) {
			return $this->context->msg( 'bs-ns_main' );
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
		$namespaceBlacklist = $config->get(
			'BlueSpiceCalummaAssocLinksExistingSiblingsNamespaceBlacklist'
		);
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'page',
			'*',
			[
				'page_title' => $currentTitle->getDBkey()
			]
		);

		$links = [];
		foreach ( $res as $row ) {
			$assocTitle = Title::newFromRow( $row );
			if ( !$assocTitle->isValid() ) {
				// whenever a namespace was removed, but the entries in the page table
				// still remain
				continue;
			}
			if ( $assocTitle->equals( $currentTitle ) ) {
				continue;
			}
			if ( in_array( $assocTitle->getNamespace(), $namespaceBlacklist ) ) {
				continue;
			}
			if ( $assocTitle->isTalkPage() ) {
				continue;
			}
			$links["existing-namespace-{$assocTitle->getNamespace()}"]
				= new static( $context, $config, $assocTitle );
		}

		return $links;
	}
}
