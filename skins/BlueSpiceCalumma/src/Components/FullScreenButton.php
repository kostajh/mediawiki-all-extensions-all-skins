<?php

namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\CookieHandler;

class FullScreenButton extends \Skins\Chameleon\Components\Structure {
	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$data = 'bs-full-screen-mode';
		$class = $this->getDomElement()->getAttribute( 'class' );

		$cookieHandler = new CookieHandler( $this->getSkin()->getRequest() );
		$cookieFullScreenMode = $cookieHandler->getCookie( 'Calumma_bs-full-screen-mode' );
		if ( isset( $cookieFullScreenMode ) && ( $cookieFullScreenMode === 'true' ) ) {
			$class .= ' bs-full-screen-mode ';
		}

		$html = \Html::openElement( 'a', [
			'title' => wfMessage( 'bs-calumma-full-screen-button-tooltip' )->plain(),
			'class' => ' calumma-full-screen-button ' . $class,
			'data-toggle' => $data,
			'role' => 'button',
			'aria-label' => wfMessage( 'bs-calumma-full-screen-button-tooltip' )->plain()
		] );

		$html .= \Html::openElement( 'i', [ 'class' => $data ] );
		$html .= \Html::closeElement( 'i' );

		$html .= \Html::closeElement( 'a' );

		return $html;
	}
}
