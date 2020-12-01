<?php

use MediaWiki\MediaWikiServices;

/**
 * This inherits from MassMessageJob, as a hacky way to get access to its protected methods.
 */
class MassMessageEmailHooks extends MassMessageJob {

	/**
	 * Hooks into MassMessage
	 *
	 * @param MassMessageJob $massMessageJob
	 * @return bool
	 */
	public static function onMassMessageJobBeforeMessageSent( MassMessageJob $massMessageJob ) {
		$title = $massMessageJob->getTitle();
		$user = User::newFromName( $title->getBaseText() );

		if ( $title->getNamespace() == NS_USER || $title->getNamespace() == NS_USER_TALK ) {
			if ( $user->canReceiveEmail() ) {
				return self::sendMassMessageEmail( $massMessageJob );
			}
		}

		// We didn't do anything. Continue execution as if we're not here.
		return true;
	}

	/**
	 * Sends the email
	 *
	 * @global string $wgArticlePath
	 * @global string $wgServer
	 * @param MassMessageJob $massMessageJob
	 * @return bool
	 */
	public static function sendMassMessageEmail( MassMessageJob $massMessageJob ) {
		$title = $massMessageJob->getTitle();
		$user = User::newFromName( $title->getBaseText() );
		$params = $massMessageJob->getParams();

		// Generate plain text ...
		$text = $massMessageJob->makeText();
		// Make sure we don't send relative links in the email. Shouldn't that be a ParserOption?
		global $wgArticlePath, $wgServer;
		$oldArticlePath = $wgArticlePath;
		$wgArticlePath = $wgServer . $wgArticlePath;
		$parser = MediaWikiServices::getInstance()->getParserFactory()->create();
		$parserOutput = $parser->parse( $text, $title, ParserOptions::newFromAnon() );
		// ... and also generate HTML from the wikitext, which makes sense since
		// we're sending an email, but it requires $wgAllowHTMLEmail
		$html = $parserOutput->getText();
		$status = $user->sendMail( $params['subject'], [ 'text' => $text, 'html' => $html ] );
		$wgArticlePath = $oldArticlePath;
		if ( !$status->isGood() ) {
			/** @todo This should really be sending a code - not a message */
			$massMessageJob->logLocalFailure( $status->getMessage() );
			return true;
			// If the status isn't good, MassMessage will proceed to post to the user's page instead.
		} else {
			// Good status - stop execution since we already emailed the user.
			return false;
		}
	}
}
