<?php

namespace BlueSpice\Calumma\AssocLinksProvider;

use BlueSpice\Html\Descriptor\TitleLink;
use MediaWiki\MediaWikiServices;
use Message;
use RawMessage;
use Title;

class RootPageSibling extends TitleLink {

	/**
	 *
	 * @return Message
	 */
	public function getLabel() {
		return new RawMessage( $this->title->getPrefixedText() );
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @return ILink[]
	 */
	public static function factory( $context, $config ) {
		if ( !$context->getTitle()->isSubpage() ) {
			return [];
		}

		$rootPageTitle = $context->getTitle()->getRootTitle();
		$namespaceBlacklist = $config->get(
			'BlueSpiceCalummaAssocLinksRootPageSiblingNamespaceBlacklist'
		);
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'page',
			'*',
			[
				'page_title' => $rootPageTitle->getDBkey()
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
			if ( $assocTitle->equals( $rootPageTitle ) ) {
				continue;
			}
			if ( in_array( $assocTitle->getNamespace(), $namespaceBlacklist ) ) {
				continue;
			}
			$links["rootpage-namespace-{$assocTitle->getNamespace()}"]
				= new static( $context, $config, $assocTitle );
		}

		return $links;
	}
}
