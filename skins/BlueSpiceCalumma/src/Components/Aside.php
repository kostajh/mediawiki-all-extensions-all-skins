<?php
namespace BlueSpice\Calumma\Components;

use MediaWiki\MediaWikiServices;

class Aside extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		if ( $this->skipRendering() ) {
			return '';
		}

		$class = $this->getDomElement()->getAttribute( 'class' );
		$toggleBy = $this->getDomElement()->getAttribute( 'data-toggle-by' );

		$body = parent::getHtml();

		$activeClass = '';
		foreach ( $this->getSubcomponents() as $component ) {

			if ( $component->isActive() ) {
				$activeClass = ' active';
			}
		}

		$html = \Html::openElement( 'aside', [
			'class' => $class . $activeClass,
			'data-toggle-by' => $toggleBy
			] );
		$html .= $body;
		$html .= \Html::closeElement( 'aside' );

		return $html;
	}

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
