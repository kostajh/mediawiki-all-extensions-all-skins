<?php

namespace BlueSpice\Expiry\AlertProvider;

use BlueSpice\AlertProviderBase;
use DateTime;

class ExpiryState extends AlertProviderBase {

	/**
	 * @return html|false
	 */
	public function getHTML() {
		if ( $this->skipForContextReasons() ) {
			return false;
		}

		$expiry = \BlueSpice\Expiry\Extension::getExpiryForPage(
			$this->skin->getTitle()->getArticleID()
		);

		if ( !$expiry || empty( $expiry['exp_date'] ) ) {
			return false;
		}

		$date = DateTime::createFromFormat( 'Y-m-d', $expiry['exp_date'] );
		if ( !$date ) {
			// something is very wrong in the database!
			return false;
		}

		$ts = $this->skin->getContext()->getLanguage()->userDate(
			$date->format( 'YmdHis' ),
			$this->getUser()
		);

		return \Message::newFromKey( 'bs-expiry-alert-body-expired' )
			->params( $ts )->text();
	}

	/**
	 * @return string
	 */
	public function getType() {
		return static::TYPE_DANGER;
	}

	/**
	 * @return bool
	 */
	protected function skipForContextReasons() {
		if ( !$this->skin->getTitle()->exists() ) {
			return true;
		}

		$action = $this->skin->getRequest()->getVal( 'action', 'view' );
		if ( !in_array( $action, [ 'view', 'submit' ] ) ) {
			return true;
		}

		return false;
	}

}
