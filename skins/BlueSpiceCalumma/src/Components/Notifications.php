<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;

class Notifications extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.Notifications';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = parent::getTemplateArgs();
		$ptools = $this->getSkinTemplate()->getPersonalTools();

		if ( isset( $ptools['notifications-alert'] ) ) {
			$args['notifications-alert'] = $ptools['notifications-alert'];
		}

		if ( isset( $ptools['notifications-notice'] ) ) {
			$args['notifications-notice'] = $ptools['notifications-notice'];
		}

		return $args;
	}
}
