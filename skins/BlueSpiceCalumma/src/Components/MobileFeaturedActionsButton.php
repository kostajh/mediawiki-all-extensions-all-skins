<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\SkinData;

class MobileFeaturedActionsButton extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$class = $this->getDomElement()->getAttribute( 'class' );
		$type = $this->getDomElement()->getAttribute( 'data-type' );
		$action = $this->getDomElement()->getAttribute( 'data-action' );

		$title = $this->getSkin()->getTitle();
		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'edit', $this->getSkin()->getUser(), $title )
		) {
			return '';
		}

		$featuredActionsData = $this->getSkinTemplate()->get( SkinData::FEATURED_ACTIONS );
		if ( !isset( $featuredActionsData[$type][$action] ) ) {
			return '';
		}
		$menuData = $featuredActionsData[$type][$action];

		$classes = $class;
		if ( isset( $menuData['classes'] ) ) {
			$classes .= ' ' . implode( ' ', $menuData['classes'] );
		}
		$id = isset( $menuData[ 'id' ] ) ? $menuData[ 'id' ] : '';
		$text = isset( $menuData[ 'text' ] ) ? $menuData[ 'text' ] : '';
		$title = isset( $menuData[ 'title' ] ) ? $menuData[ 'title' ] : $text;

		$html = \Html::openElement(
				'a',
				[
					'class' => 'bs-mobile-featured-actions-button ' . $classes,
					'id' => $id,
					'title' => $title,
					'href' => $menuData[ 'href' ]
				]
			);
		$html .= \Html::element( 'i' );
		$html .= \Html::closeElement( 'a' );

		return $html;
	}
}
