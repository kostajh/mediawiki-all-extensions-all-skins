<?php

namespace BlueSpice\Calumma;

use FormatJson;
use WebRequest;

class CookieHandler {
	/** @var WebRequest */
	protected $request;
	/**
	 * This needs to be kept in sync with:
	 * - client-side script BlueSpiceCalummaCookieHandler->cookieName
	 * - skin.json => attributes => BlueSpicePrivacyCookieConsentNativeMWCookies
	 * @var string
	 */
	private $cookieName = 'Calumma_state';

	/**
	 * @param WebRequest $request
	 */
	public function __construct( WebRequest $request ) {
		$this->request = $request;
	}

	/**
	 * @param string $name
	 * @param mixed|null $default Default value to return if cookie is not present
	 * @return mixed|null Cookie value or null if not present
	 */
	public function getCookie( $name, $default = null ) {
		$parsed = $this->parse();
		if ( isset( $parsed[$name] ) ) {
			return $parsed[$name];
		}

		return $default;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function setCookie( $name, $value ) {
		$parsed = $this->parse();
		$parsed[$name] = $value;

		$this->setToRequest( $parsed );
	}

	/**
	 * @param array $values
	 */
	private function setToRequest( $values ) {
		$this->request->response()->setCookie( $this->cookieName, FormatJson::encode( $values ) );
	}

	/**
	 * @return array
	 */
	private function parse() {
		$raw = $this->request->getCookie( $this->cookieName, null, null );

		if ( !$raw ) {
			return [];
		}

		return FormatJson::decode( $raw, true );
	}
}
