<?php

use \google\appengine\api\mail\Message as GMessage;

class GoogleAppEngineHooks {
	public static function onAlternateUserMailer( $headers, $to, $from, $subject, $body ) {
		try {
			$message = new GMessage();
			$message->setSender( $from );
			$message->addTo( $to );
			$message->setSubject( $subject );
			if ( is_array( $body ) ) {
				$message->setHtmlBody( $body['html'] );
				$message->setTextBody( $body['text'] );
			} else {
				$message->setTextBody( $body );
			}
			$message->send();
		} catch ( InvalidArgumentException $e ) {
			wfDebugLog( 'GoogleAppEngine', 'Error sending mail: ' . $e->getMessage() );
		}

		return false;
	}
}