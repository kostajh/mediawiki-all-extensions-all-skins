<?php
namespace BlueSpice\Calumma\Components;

use MediaWiki\MediaWikiServices;

class SidebarToggle extends \Skins\Chameleon\Components\Structure {

	protected $activeState = false;

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		if ( $this->skipRendering() ) {
			return '';
		}

		$data = $this->getDomElement()->getAttribute( 'data-toggle' );
		$class = $this->getDomElement()->getAttribute( 'class' );

		$html = \Html::openElement( 'a', [
			'class' => ' sidebar-toggle ' . $class,
			'data-toggle' => $data,
			'role' => 'button',
			'title' => \Message::newFromKey( "bs-calumma-navigation-toggle-tooltip" ),
			'aria-label' => \Message::newFromKey( "bs-calumma-navigation-toggle-tooltip" )
		] );

		$html .= \Html::openElement( 'i', [ 'class' => $data ] );
		$html .= \Html::closeElement( 'i' );

		$html .= \Html::closeElement( 'a' );

		return $html;
	}

	/**
	 * Is this element active
	 * @return bool
	 */
	public function isActive() {
		return $this->activeState;
	}

	/**
	 *
	 * @return bool
	 */
	protected function skipRendering() {
		$hideIfNoRead = $this->getDomElement()->getAttribute( 'hide-if-noread' );
		$hideIfNoRead = strtolower( $hideIfNoRead ) === 'true' ? true : false;
		$userHasReadPermissionsAtAll = !MediaWikiServices::getInstance()
			->getPermissionManager()->userHasRight( $this->getSkin()->getUser(), 'read' );

		if ( $hideIfNoRead && $userHasReadPermissionsAtAll ) {
			return true;
		}

		return false;
	}

}
