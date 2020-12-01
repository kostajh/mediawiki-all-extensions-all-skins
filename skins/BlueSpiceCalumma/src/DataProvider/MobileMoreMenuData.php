<?php
namespace BlueSpice\Calumma\DataProvider;

use BlueSpice\Calumma\SkinDataFieldDefinition;
use BlueSpice\SkinData;
use QuickTemplate;

class MobileMoreMenuData {

	/**
	 *
	 * @param Skin $skin
	 * @param QuickTemplate $skintemplate
	 * @param array &$data
	 */
	public static function populate( $skin, QuickTemplate $skintemplate, &$data ) {
		foreach ( $data[SkinData::FEATURED_ACTIONS]['edit'] as $item ) {
			$class = '';
			if ( array_key_exists( 'classes', $item ) ) {
				$class = implode( ' ', $item['classes'] );
			}
			if ( array_key_exists( 'id', $item ) ) {
				$item['class'] = $class . ' calumma-mobile-more-menu-' . $item['id'];
				unset( $item['id'] );
			}
			if ( !array_key_exists( 'text', $item ) ) {
				$item['text'] = $item['title'];
			}
			$data[SkinDataFieldDefinition::MOBILE_MORE_MENU][] = $item;
		}
	}
}
