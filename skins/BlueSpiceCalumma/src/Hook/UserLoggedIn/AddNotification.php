<?php
namespace BlueSpice\Calumma\Hook\UserLoggedIn;

use BlueSpice\Hook\UserLoggedIn;
use BlueSpice\SimpleDeferredNotification;

class AddNotification extends UserLoggedIn {

	protected function doProcess() {
		$this->getServices()->getService( 'BSDeferredNotificationStack' )->push(
			new SimpleDeferredNotification(
				[
					'message' => $this->msg( 'bs-calumma-notification-logged-in' ),
					'options' => [
						'type' => 'info'
					]
				]
			)
		);

		return true;
	}
}
