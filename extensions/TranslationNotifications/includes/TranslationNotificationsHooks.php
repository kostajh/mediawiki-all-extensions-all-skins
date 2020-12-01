<?php
/**
 * Some hooks for TranslationNotifications extension.
 *
 * @file
 * @author Amir E. Aharoni
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012, Amir E. Aharoni
 * @license GPL-2.0-or-later
 */

/**
 * Some hooks for TranslationNotifications extension.
 */
class TranslationNotificationsHooks {
	public static function onGetPreferences( $user, &$preferences ) {
		foreach (
			[
				'translationnotifications-lang-1',
				'translationnotifications-lang-2',
				'translationnotifications-lang-3',
				'translationnotifications-cmethod-email',
				'translationnotifications-cmethod-talkpage',
				'translationnotifications-cmethod-talkpage-elsewhere-loc',
				'translationnotifications-freq',
			] as $preference
		) {
			$preferences[$preference] = [
				'type' => 'api',
			];
		}

		return true;
	}
}
