<?php

namespace BlueSpice\Social\Profile\Field;

use BlueSpice\Social\Profile\Field;
use Message;

class Title extends Field {

	/**
	 *
	 * @return string
	 */
	public function getValue() {
		$msg = Message::newFromKey( 'bs-social-profile-field-title-value' );
		return $msg->params( $this->user->getName() )->text();
	}
}
