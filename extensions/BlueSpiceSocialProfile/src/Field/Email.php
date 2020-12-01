<?php

namespace BlueSpice\Social\Profile\Field;

use BlueSpice\Social\Profile\Field;

class Email extends Field {

	/**
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->user->getEmail();
	}

	/**
	 *
	 * @return bool
	 */
	public function isHidden() {
		return !$this->user->getOption(
			'bs-social-profile-infoshowemail'
		);
	}
}
