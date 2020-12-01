<?php

namespace BlueSpice\CustomMenu\Hook\EditFormPreloadText;

use BlueSpice\Data\IRecord;
use BlueSpice\Data\RecordSet;

class PrealoadHederMenu extends \BlueSpice\Hook\EditFormPreloadText {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = \Title::makeTitle(
			NS_MEDIAWIKI,
			// 'TopBarMenu' in the past
			"CustomMenu/Header"
		);
		if ( !$this->title || !$this->title->equals( $title ) ) {
			return true;
		}
		if ( $title->exists() ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$menu = $this->getServices()->getService( 'BSCustomMenuFactory' )
			->getMenu( 'header' );
		if ( !$menu ) {
			return true;
		}

		$items = [];
		foreach ( $menu->getData()->getRecords() as $record ) {
			$items[] = $this->recordToLegacyParserItem( $record );
		}

		$this->text = \MenuParser::toWikiText( $items );
		return true;
	}

	/**
	 *
	 * @param IRecord $record
	 * @return array
	 */
	protected function recordToLegacyParserItem( $record ) {
		$item = (array)$record->getData();
		if ( !isset( $item['children'] ) || !$item['children'] instanceof RecordSet ) {
			return $item;
		}
		$children = [];
		foreach ( $item['children']->getRecords() as $childRecord ) {
			$children[] = $this->recordToLegacyParserItem( $childRecord );
		}
		$item['children'] = $children;
		return $item;
	}

}
