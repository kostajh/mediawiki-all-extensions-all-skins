<?php

namespace BlueSpice\QrCode\Panel;

use BlueSpice\Calumma\Panel\BasePanel;
use Endroid\QrCode\QrCode;
use Skins\Chameleon\IdRegistry;

class Panel extends BasePanel {
	/**
	 * @return \Message
	 */
	public function getTitleMessage() {
		return \Message::newFromKey( 'bs-qr-code-title' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		$url = $this->skintemplate->getSkin()->getTitle()->getFullURL();

		$qrCode = new QrCode( $url );
		$qrCode->setSize( 120 );

		$src = 'data:image/png;base64,' . base64_encode( $qrCode->writeString() );

		$msg = \Message::newFromKey( 'bs-qr-code-text' );
		$alttext = \Message::newFromKey( 'bs-qr-code-title' );

		$span = \Html::element( 'p', [], $msg->plain() );

		return \Html::rawElement( 'div', [ 'class' => 'scanQrCode' ], $span )
			. \Html::element( 'img', [ 'class' => 'qrCodeImage', 'src' => $src, 'alt' => $alttext ] );
	}

	/**
	 *
	 * @var string
	 */
	protected $htmlId = null;

	/**
	 * The HTML ID for thie component
	 * @return string
	 */
	public function getHtmlId() {
		if ( $this->htmlId === null ) {
			$this->htmlId = IdRegistry::getRegistry()->getId( 'bs-qr-code-panel' );
		}

		return $this->htmlId;
	}

	/**
	 *
	 * @return bool
	 */
	public function getPanelCollapseState() {
		$cookieName = $this->getCookiePrefix() . $this->htmlId;
		$request = $this->skintemplate->getSkin()->getRequest();
		$cookie = $request->getCookie( $cookieName );

		if ( $cookie === 'false' ) {
			return false;
		} elseif ( $cookie === 'true' ) {
			return true;
		}

		$states = $this->skintemplate->getSkin()->getConfig()->get(
				'BlueSpiceCalummaPanelCollapseState'
			);

		if ( !$states || !array_key_exists( $this->htmlId, $states ) ) {
			return true;
		}

		if ( $states[$this->htmlId] === true || $states[$this->htmlId] === 1 ) {
			return true;
		}

		return false;
	}
}
