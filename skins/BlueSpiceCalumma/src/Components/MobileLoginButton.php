<?php
namespace BlueSpice\Calumma\Components;

use MediaWiki\MediaWikiServices;

class MobileLoginButton extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		if ( $this->getSkin()->getUser()->isLoggedIn() ) {
			return '';
		}
		$isAllowed = MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
			$this->getSkin()->getUser(),
			'read'
		);
		// Todo: find and add the explaination for why the login button should
		// not be showen, when the user has read permission! commit without ticket:
		// I29e9ff9e0cf87e62c07aa21e03220289ef73059f
		if ( $isAllowed ) {
			return '';
		}
		$class = $this->getDomElement()->getAttribute( 'class' );
		$title = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getTitleForAlias( 'Userlogin' );

		$html = \Html::openElement( 'a', [
			'href' => $title->getFullURL(),
			'title' => $title->getText(),
			'class' => ' ' . $class,
			'role' => 'button'
		] );

		$html .= \Html::openElement( 'i', [ 'class' => $class ] );
		$html .= \Html::closeElement( 'i' );

		$html .= \Html::closeElement( 'a' );

		return $html;
	}
}
