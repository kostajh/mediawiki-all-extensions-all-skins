<?php
/*
* @file
* @license GPL-2.0-or-later
*/

/**
 * A class that helps builds the notification message to be sent to users
 * @since 2019.10
 */
class NotificationMessageBuilder {
	/**
	 * Returns the message title to be embedded into the message
	 * @param Title $title
	 * @param string $destination
	 * @param string[] $localInterwikis
	 * @return Title|string
	 */
	public static function getMessageTitle( Title $title, $destination, $localInterwikis ) {
		$titleForMessage = $title;

		if ( $destination === 'talkpageInOtherWiki' && count( $localInterwikis ) ) {
			$titleForMessage = ":$localInterwikis[0]:$titleForMessage|$titleForMessage";
		}

		return $titleForMessage;
	}

	/**
	 * Returns the message priority clause to be embedded into the message
	 * @param Language $userFirstLanguage Language object set as first preference
	 * @param string $priority
	 * @return string
	 */
	public static function getPriorityClause( Language $userFirstLanguage, $priority ) {
		if ( $priority === 'unset' ) {
			return '';
		}

		return wfMessage(
			'translationnotifications-email-priority',
			self::getPriorityMessage(
				$priority
			)->inLanguage( $userFirstLanguage )->text()
		)->inLanguage( $userFirstLanguage )->text();
	}

	/**
	 * Return the deadline clause
	 * @param Language $userFirstLanguage
	 * @param string $deadlineDate
	 * @return string
	 */
	public static function getDeadlineClause( Language $userFirstLanguage, $deadlineDate ) {
		if ( $deadlineDate === '' ) {
			return '';
		}

		return wfMessage(
			'translationnotifications-email-deadline',
			$deadlineDate
		)->inLanguage( $userFirstLanguage )->text();
	}

	/**
	 * Wrap the text in a div based on the language
	 * @param Language $contLang
	 * @param string $notificationContent
	 * @return string
	 */
	public static function getNotificationMessage( Language $contLang, $notificationContent ) {
		// Assume that the message is in the content language
		// of the originating wiki.
		$dir = $contLang->getDir();
		// Possible classes:
		// mw-content-ltr, mw-content-rtl
		$notificationText = Html::element( 'div',
			[
				'lang' => $contLang->getCode(),
				'class' => "mw-content-$dir"
			],
			$notificationContent
		);

		return $notificationText;
	}

	/**
	 * Returns a list of URLs for page translation in every language.
	 * @param Title $translatableTitle Title of the page being translated
	 * @param string[] $languages A list of language codes and language names.
	 * @param string $contactMethod The contact method - 'talkpage' or 'email'.
	 * @param string|Language $inLanguage Language code or Language object.
	 * @param string $urlProtocol
	 * @return string
	 */
	public static function getTranslationURLs(
		Title $translatableTitle, $languages, $contactMethod, $inLanguage, $urlProtocol
	) {
		$translationURLsItems = [];

		foreach ( $languages as $code => $langName ) {
			$translationURL = self::getTranslationURL( $translatableTitle, $code, $urlProtocol );

			$translationMsg = wfMessage(
				'translationnotifications-notification-url-listitem',
				$langName
			)->inLanguage( $inLanguage )->text();

			switch ( $contactMethod ) {
				case 'talkpage':
					$translationURLsItems[] = "* [$translationURL $translationMsg]";
					break;
				case 'email':
					$translationURLsItems[] = "* $translationMsg: <$translationURL>";
					break;
				default:
					return '';
			}
		}

		return implode( "\n", $translationURLsItems );
	}

	/**
	 * Returns URL to signup and change notification preferences
	 * @param string $urlProtocol
	 * @return string Signup URL
	 */
	public static function getSignupURL( $urlProtocol ) {
		$signupURL = SpecialPage::getTitleFor( 'TranslatorSignup' )->getFullURL(
			'',
			false,
			$urlProtocol
		);

		return $signupURL;
	}

	/**
	 * Returns the user name to be used in the notification
	 * @param User $user
	 * @return string
	 */
	public static function getUserName( User $user ) {
		$name = $user->getRealName();
		if ( $name === '' ) {
			$name = $user->getName();
		}

		return $name;
	}

	/**
	 * Returns the subject of the notification
	 * @param Title $title
	 * @param string|Language $userFirstLanguage
	 * @return string
	 */
	public static function getNotificationSubject( Title $title, $userFirstLanguage ) {
		return wfMessage(
			'translationnotifications-email-subject',
			$title->getText()
		)->inLanguage( $userFirstLanguage )->text();
	}

	/**
	 * @param string $priority
	 * @return Message
	 */
	public static function getPriorityMessage( $priority ) {
		// possible messages here:
		// 'translationnotifications-priority-high'
		// 'translationnotifications-priority-medium'
		// 'translationnotifications-priority-low'
		// 'translationnotifications-priority-unset'
		return wfMessage( "translationnotifications-priority-$priority" );
	}

	/**
	 * @param Title $translatablePageTitle
	 * @param string $languageCode
	 * @param string $urlProtocol
	 * @return string Translation URL
	 */
	private static function getTranslationURL(
		Title $translatablePageTitle, $languageCode, $urlProtocol
	) {
		$page = TranslatablePage::newFromTitle( $translatablePageTitle );
		$translationURL = SpecialPage::getTitleFor( 'Translate' )->getFullURL(
			[
				'group' => $page->getMessageGroupId(),
				'language' => $languageCode,
				'action' => 'page'
			],
			false,
			$urlProtocol
		);

		return $translationURL;
	}
}
