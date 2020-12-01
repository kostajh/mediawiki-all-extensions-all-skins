<?php

namespace BlueSpice\Social\Profile\Field;

use BlueSpice\Social\Profile\Field;

class Name extends Field {

	/**
	 *
	 * @return string
	 */
	public function getValue() {
		return empty( $this->user->getRealName() )
			? $this->user->getName()
			: $this->user->getRealName();
	}
}
