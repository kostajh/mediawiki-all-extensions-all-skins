<?php

namespace BlueSpice\EchoConnector;

use MediaWiki\MediaWikiServices;
use MWException;

class EchoEmailBatch extends \MWEchoEmailBatch {

	/**
	 *
	 * @param int $userId
	 * @param bool|true $enforceFrequency
	 * @return bool|EchoEmailBatch
	 */
	public static function newFromUserId( $userId, $enforceFrequency = true ) {
		$user = \User::newFromId( intval( $userId ) );

		$userEmailSetting = intval( $user->getOption( 'echo-email-frequency' ) );

		// clear all existing events if user decides not to receive emails
		if ( $userEmailSetting == -1 ) {
			$emailBatch = new self( $user );
			$emailBatch->clearProcessedEvent();

			return false;
		}

		// @Todo - There may be some items idling in the queue, eg, a bundle job is lost
		// and there is not never another message with the same hash or a user switches from
		// digest to instant.  We should check the first item in the queue, if it doesn't
		// have either web or email bundling or created long ago, then clear it, this will
		// prevent idling item queuing up.

		// user has instant email delivery
		if ( $userEmailSetting == 0 ) {
			return false;
		}

		$userLastBatch = $user->getOption( 'echo-email-last-batch' );

		// send email batch, if
		// 1. it has been long enough since last email batch based on frequency
		// 2. there is no last batch timestamp recorded for the user
		// 3. user has switched from batch to instant email, send events left in the queue
		if ( $userLastBatch ) {
			// use 20 as hours per day to get estimate
			$nextBatch = wfTimestamp( TS_UNIX, $userLastBatch ) + $userEmailSetting * 20 * 60 * 60;
			if ( $enforceFrequency && wfTimestamp( TS_MW, $nextBatch ) > wfTimestampNow() ) {
				return false;
			}
		}

		return new self( $user );
	}

	/**
	 * @throws MWException
	 */
	public function sendEmail() {
		global $wgNotificationSender, $wgNotificationReplyName;

		if ( $this->mUser->getOption( 'echo-email-frequency' ) == \EchoEmailFrequency::WEEKLY_DIGEST ) {
			$frequency = 'weekly';
			$emailDeliveryMode = 'weekly_digest';
		} else {
			$frequency = 'daily';
			$emailDeliveryMode = 'daily_digest';
		}

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$formatterClass = $config->get( 'EchoEmailFormatterClasses' )['plain-text-digest'];
		$textEmailDigestFormatter = new $formatterClass(
			$this->mUser,
			$this->language,
			$frequency
		);
		$content = $textEmailDigestFormatter->format( $this->events, 'email' );

		if ( !$content ) {
			// no event could be formatted
			return;
		}

		$format = \MWEchoNotifUser::newFromUser( $this->mUser )->getEmailFormat();
		if ( $format == \EchoEmailFormat::HTML ) {
			$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
			$formatterClass = $config->get( 'EchoEmailFormatterClasses' )['html-digest'];
			$htmlEmailDigestFormatter = new $formatterClass(
				$this->mUser,
				$this->language,
				$frequency
			);
			$htmlContent = $htmlEmailDigestFormatter->format( $this->events, 'email' );

			$content = [
				'body' => [
					'text' => $content['body'],
					'html' => $htmlContent['body'],
				],
				'subject' => $htmlContent['subject'],
			];
		}

		$toAddress = \MailAddress::newFromUser( $this->mUser );
		$fromAddress = new \MailAddress( $wgNotificationSender, \EchoHooks::getNotificationSenderName() );
		$replyTo = new \MailAddress( $wgNotificationSender, $wgNotificationReplyName );

		// @Todo Push the email to job queue or just send it out directly?
		\UserMailer::send(
			$toAddress,
			$fromAddress,
			$content['subject'],
			$content['body'],
			[ 'replyTo' => $replyTo ]
		);
		\MWEchoEventLogging::logSchemaEchoMail( $this->mUser, $emailDeliveryMode );
	}
}
