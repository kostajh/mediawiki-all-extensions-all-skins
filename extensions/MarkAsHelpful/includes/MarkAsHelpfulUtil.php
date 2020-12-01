<?php

/**
 * Utility class for Mark As Helpful
 */
class MarkAsHelpfulUtil {

	/**
	 * Generate 'mark as helpful' template for current user
	 * @param User $user The current user
	 * @param bool $isAbleToMark flag to check if the user is able to mark the item as helpful
	 * @param array $helpfulUserList list of users who have marked this item as helpful
	 * @param string $type the object type
	 * @param int $item the object id
	 * @return string html or empty text
	 */
	public static function getMarkAsHelpfulTemplate( $user, $isAbleToMark, $helpfulUserList, $type, $item ) {
		if ( $user->isAnon() ) {
			$html = self::otherMarkedTemplate( $helpfulUserList );
		} else {
			$userId = $user->getId();

			if ( isset( $helpfulUserList[$userId] ) ) {
				$html = self::ownerMarkedTemplate();
			} else {
				if ( $isAbleToMark ) {
					$html = self::requestToMarkTemplate();
				} else {
					$html = self::otherMarkedTemplate( $helpfulUserList );
				}

			}
		}
		return $html;
	}

	/**
	 * The template to display in this format: {{user name or ip}} thinks this is helpful
	 * @param array $helpfulUserList List of users that mark the item as helpful
	 * @return string html or empty text
	 */
	private static function otherMarkedTemplate( $helpfulUserList ) {
		if ( count( $helpfulUserList ) == 0 ) {
			return '';
		}

		reset( $helpfulUserList );

		$user = current( $helpfulUserList );

		// show the first user 'in mark as helpful' template
		$data = wfMessage( 'mah-someone-marked-text' )->params( $user['user_name'] )->escaped();

		// Add other user in user list to a hidden div, this is for future enhancement

		return <<<HTML
			<div class='mw-mah-wrapper'>
				<span class='mah-helpful-marked-state'>
					$data
				</span>
			</div>
HTML;
	}

	/**
	 * The template to display in this format: You think this is helpful
	 * @todo future enhancement: We may pass the list of other users as well
	 *       so we can put them in hidden div
	 * @return string html
	 */
	private static function ownerMarkedTemplate() {
		$mahMarkedText = wfMessage( 'mah-you-marked-text' )->escaped();
		$undoLinkText = wfMessage( 'mah-undo-mark-text' )->escaped();

		return <<<HTML
			<div class='mw-mah-wrapper'>
				<span class='mah-helpful-marked-state'>
					$mahMarkedText
				</span>
				&nbsp;(<a class='markashelpful-undo'>$undoLinkText</a>)
			</div>
HTML;
	}

	/**
	 * The template to request a user to mark an item as helpful
	 * @return string html
	 */
	private static function requestToMarkTemplate() {
		$mahLinkText = wfMessage( 'mah-mark-text' )->escaped();
		return <<<HTML
			<div class='mw-mah-wrapper'>
				<a class='mah-helpful-state markashelpful-mark'>
					$mahLinkText
				</a>
			</div>
HTML;
	}
}
