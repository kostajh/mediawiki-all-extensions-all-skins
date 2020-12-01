<?php

namespace BlueSpice\Expiry\Reminder;

use BlueSpice\Reminder\Reminder;

class Expiry extends Reminder {

	/**
	 *
	 * @return string
	 */
	public function getLabelMsgKey() {
		return 'bs-expiry-reminder-type-expiry';
	}

}
