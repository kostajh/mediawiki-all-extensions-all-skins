<?php

namespace BlueSpice\EchoConnector\Formatter;

class EchoPlainTextEmailFormatter extends \EchoPlainTextEmailFormatter {
	/**
	 *
	 * @param \EchoEventPresentationModel $model
	 * @return array
	 */
	protected function formatModel( \EchoEventPresentationModel $model ) {
		// PresentationModel is always created with distro type 'web'
		// so let parent handle all notifs that are not presented by our PresentationModel
		// and set distro type manually usign custom function
		if ( $model instanceof \BlueSpice\EchoConnector\EchoEventPresentationModel ) {
			$model->setDistributionType( 'email' );
		}

		return parent::formatModel( $model );
	}
}
