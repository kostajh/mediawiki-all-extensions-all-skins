<?php
/**
 * Hooks for SwiftMailer extension
 *
 * Copyright (c) 2014, Tony Thomas <01tonythomas@gmail.com>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @author Tony Thomas <01tonythomas@gmail.com>
 * @author Legoktm <legoktm@gmail.com>
 * @author Jeff Green <jgreen@wikimedia.org>
 * @license GPL-2.0-or-later
 * @ingroup Extensions
 */
class SwiftMailerHooks {
	/**
	 * Creates and sends a mail using SwiftMailer
	 *
	 * @param array $headers
	 * @param MailAddress $to
	 * @param MailAddress $from
	 * @param string $subject
	 * @param string $body
	 * @return bool|Exception|Swift_SwiftException|Swift_TransportException
	 */
	public static function onAlternateUserMailer(
		array $headers,
		array $to,
		MailAddress $from,
		$subject, $body
	) {
		$message = Swift_Message::newInstance()
				->setSubject( $subject )
				->setFrom( [ $from->address => $from->name ] )
				->setBody( $body );

		$returnPath = $headers['Return-Path'];
		$message->setReturnPath( $returnPath );

		try {
			$transport = self::getSwiftMailer();
		} catch ( Swift_TransportException $e ) {
			wfDebugLog( 'SwiftMailer', "Transport configuration for SwiftMailer failed: $e" );
			return $e;
		}

		// Create the SwiftMailer::Mailer Object using the above Transport
		$mailer = Swift_Mailer::newInstance( $transport );

		wfDebug( "Sending mail via Swift::Mail\n" );

		foreach ( $to as $recip ) {
			$message->setTo( [ $recip->address => $recip->name ] );
			try {
				$mailer->send( $message );
			} catch ( Swift_SwiftException $e ) {
				wfDebugLog( 'SwiftMailer', "Swift Mailer failed: $e" );
				return $e;
			}
		}

		// Alternate Mailer hooks should return false to skip regular false sending
		return false;
	}

	/**
	 * Generate the SwiftMailer transport object
	 *
	 * @return SwiftMailer::Transport object Swift_MailTransport|Swift_SmtpTransport
	 */
	protected static function getSwiftMailer() {
		global $wgSMTP, $wgSMTPAuthenticationMethod;
		static $swiftTransport = null;
		if ( !$swiftTransport ) {
			if ( is_array( $wgSMTP ) ) {
				// Create the Transport with Swift_Message::newInstance() method
				$swiftTransport = Swift_SmtpTransport::newInstance( $wgSMTP['host'], $wgSMTP['port'] )
					->setUsername( $wgSMTP['username'] )
					->setPassword( $wgSMTP['password'] );
				if ( $wgSMTP['auth'] === true && $wgSMTPAuthenticationMethod ) {
					$swiftTransport->setEncryption( $wgSMTPAuthenticationMethod );
				}
			} else {
				$swiftTransport = Swift_MailTransport::newInstance();
			}
		}

		return $swiftTransport;
	}
}
