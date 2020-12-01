<?php

/**
 * Extension that allows textual diffs of page changes to be emailed
 *
 * @file
 * @ingroup Extensions
 * @author Greg Sabino Mullane <greg@endpoint.com>
 * @link https://www.mediawiki.org/wiki/Extension:EmailDiff Documentation
 *
 * @copyright Copyright © 2015-2017, Greg Sabino Mullane
 * @license MIT
 */

class EmailDiff {

	private static $emaildiff_text, $emaildiff_subject_original;

	private static $emaildiff_hasdiff = false;

	/**
	 * SendNotificationEmail hook
	 *
	 * Allows adding text diff to the body of the outgoing email
	 *
	 * @param User $watchingUser current user (null if impersonal)
	 * @param int $oldid the old revision id
	 * @param Title $title current article title
	 * @param string &$header Additional headers - not used by this extension
	 * @param string &$subject Subject line
	 * @param string &$body Actual email body to be sent
	 * @return bool
	 */
	public static function SendNotificationEmailDiff( $watchingUser, $oldid, $title, &$header, &$subject, &$body ) {
		global $wgEmailDiffCommand, $wgEmailDiffSubjectSuffix;

		// Store the original subject line the first time we are called
		if ( self::$emaildiff_subject_original === null ) {
			self::$emaildiff_subject_original = $subject;
		} else {
			// Reset the subject line in case we changed it last time
			$subject = self::$emaildiff_subject_original;
		}

		// Only show diffs if the user has set in in their preferences
		// This will appear in the "Email options" section
		// watchingUser can be null if this is called via sendImpersonal
		if ( $watchingUser === null || $watchingUser->getOption( 'enotifshowdiff' ) ) {

			// The goal is to only generate the diff once, no matter how many users we are emailing
			if ( self::$emaildiff_text === null ) {

				// Find the revision of the new page
				$newrev = Revision::newFromTitle( $title );
				if ( !$newrev ) {
					// This can happen is the page is an image, for example
					self::$emaildiff_text = '';
				} else {
					// Get the actual text of the new page
					$newtext = $newrev->getText();

					// The page may be new (or unchanged?)
					if ( !$oldid ) {
						self::$emaildiff_text = wfMessage( 'emaildiff-newpage', $newtext )->plain() . "\n";
					} else {
						// Begin generating the actual diff
						$tempdir = wfTempDir();

						// Put the new page text into a temporary file:
						$new_file = tempnam( $tempdir, 'mediawikiemaildiffnew' );
						$fh = fopen( $new_file, 'w' ) || die( "Could not open file $new_file" );
						fputs( $fh, "$newtext\n" );
						fclose( $fh );

						// Put the old page text into a different temporary file:
						$oldrev = Revision::newFromId( $oldid );
						$oldtext = $oldrev->getText();
						$old_file = tempnam( $tempdir, 'mediawikiemaildiffold' );
						$fh = fopen( $old_file, 'w' ) || die( "Could not open file $old_file" );
						fputs( $fh, "$oldtext\n" );
						fclose( $fh );

						// Create a destination file, then run the diff command
						$diff_file = tempnam( $tempdir, 'mediawikiemaildiff' );
						$diffcom = str_replace(
							[ 'OLDFILE', 'NEWFILE', 'DIFFFILE' ],
							[ "$old_file", "$new_file", "$diff_file" ],
							$wgEmailDiffCommand );
						// phpcs:ignore MediaWiki.Usage.ForbiddenFunctions.system
						system( $diffcom );

						// Put the generated diff into our variable
						self::$emaildiff_text = "\n" . wfMessage( 'emaildiff-intro', file_get_contents( $diff_file ) )->plain() . "\n";

						// Clean up our temporary files:
						unlink( $old_file );
						unlink( $new_file );
						unlink( $diff_file );

						self::$emaildiff_hasdiff = true;
					} // end generating a diff

				} // end if we have a new revision

			} // end if we have not created a diff yet

			// Possibly modify the subject line
			if ( self::$emaildiff_hasdiff && strlen( $wgEmailDiffSubjectSuffix ) ) {
				$subject .= $wgEmailDiffSubjectSuffix;
			}
		} // end if this user wants a diff

		// Replace the $PAGEDIFF placeholder with our generated diff
		// If there is no diff, simply remove the placeholder
		// This requires you edit MediaWiki:Enotif_body - see the documentation
		$body = str_replace( '$PAGEDIFF', ( self::$emaildiff_text === null ? '' : self::$emaildiff_text ), $body );

		return true;
	}

	/**
	 * GetPreferences hook
	 *
	 * Puts a new preference in the "User profile / Email options" section
	 *
	 * @param User $user current user
	 * @param array &$prefs list of default user preference controls
	 * @return bool
	 */
	public static function SetEmailDiffPref( $user, &$prefs ) {
		$prefs['enotifshowdiff'] = [
			'type' => 'toggle',
			'section' => 'personal/email',
			'label-message' => 'tog-emaildiff'
		];

		return true;
	}
}
