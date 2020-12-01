<?php

namespace BlueSpice\EchoConnector\Hook\EchoGetNotificationTypes;

use BlueSpice\EchoConnector\Hook\EchoGetNotificationTypes;

class GetNotificationTypesForEvent extends EchoGetNotificationTypes {

	protected function doProcess() {
		$type = $this->event->getType();
		if ( $type == "bs-adduser" ) {
			$arrUserOptions = $this->user->getOptions();
			$this->userNotifyTypes = array_diff( $this->userNotifyTypes,  [ 'web', 'email' ] );

			if ( isset( $arrUserOptions[ 'echo-subscriptions-web-bs-admin-cat' ] ) &&
				$arrUserOptions[ 'echo-subscriptions-web-bs-admin-cat' ] == 1 ) {
				$this->userNotifyTypes[] = 'web';
			}
			if ( isset( $arrUserOptions[ 'echo-subscriptions-email-bs-admin-cat' ] ) &&
				$arrUserOptions[ 'echo-subscriptions-email-bs-admin-cat' ] == 1 ) {
				$this->userNotifyTypes[] = 'email';
			}
		}

		return true;
	}

}
