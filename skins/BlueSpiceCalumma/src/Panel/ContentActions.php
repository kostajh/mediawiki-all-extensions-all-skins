<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\SkinDataFieldDefinition as SDFD;

class ContentActions extends BasePanel {

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitetools-default' );
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$content_navigation_data = $this->skintemplate->get( SDFD::CONTENT_NAVIGATION_DATA );

		$list = [];
		foreach ( $content_navigation_data as $key => $value ) {
			if ( $value['bs-group'] !== 'default' ) { continue;
			}
			$list[] = $value;
		}

		$links = new LinkList( 'dummy-id', [
			'content' => array_values( $list )
		] );

		return $links->getBody();
	}

	/**
	 *
	 * @return bool
	 */
	public function isEmpty() {
		$linkDefs = $this->skintemplate->get( 'content_navigation' );
		return empty( $linkDefs['actions'] );
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ) {
		$contentActions = $this->skintemplate->get( 'content_navigation' );
		return !empty( $contentActions['actions'] );
	}
}
