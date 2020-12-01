<?php
/**
 * A special page for uploading avatars
 * This page is a big hack -- it's just the image upload page with some changes
 * to upload the actual avatar files.
 * The avatars are not held as MediaWiki images, but
 * rather based on the user_id and in multiple sizes
 *
 * Requirements: Need writable directory $wgUploadDirectory/avatars
 *
 * @file
 * @ingroup Extensions
 * @author David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license GPL-2.0-or-later
 */

class SpecialUploadAvatar extends SpecialUpload {
	public $avatarUploadDirectory;
	public $mUploadCopyStatus;
	public $mUploadSource;

	public function __construct( $request = null ) {
		parent::__construct();
		$this->mName = 'UploadAvatar';
	}

	public function isListed() {
		return false;
	}

	/**
	 * Let the parent handle most of the request, but specify the Upload
	 * class ourselves
	 */
	protected function loadRequest() {
		$request = $this->getRequest();
		parent::loadRequest();
		$this->mUpload = new UploadAvatar();
		$this->mUpload->initializeFromRequest( $request );
	}

	/**
	 * Show the special page. Let the parent handle most stuff, but handle a
	 * successful upload ourselves
	 *
	 * @param string|null $params
	 */
	public function execute( $params ) {
		$out = $this->getOutput();

		// Add CSS
		$out->addModuleStyles( [
			'ext.socialprofile.userprofile.tabs.css',
			'ext.socialprofile.special.updateprofile.css', // Need this module for .profile-update-title styles at least, probably more
			'ext.socialprofile.special.uploadavatar.css'
		] );

		// Add JS
		$out->addModules( 'ext.socialprofile.special.uploadavatar.js' );

		// Let the parent class do most of the heavy lifting.
		parent::execute( $params );

		if ( $this->mUploadSuccessful ) {
			// Cancel redirect
			$out->redirect( '' );

			$this->showSuccess( $this->mUpload->mExtension );
			// Run a hook on avatar change
			Hooks::run( 'NewAvatarUploaded', [ $this->getUser() ] );
		}
	}

	/**
	 * Override the parent method because our getUploadForm() does _not_ return
	 * a(n) HTMLForm yet the parent version of this method assumes it does.
	 *
	 * This is called at least when $wgEmailConfirmToEdit is set to true and a user
	 * with an unconfirmed email tries to upload their avatar.
	 * (Alternatively we _could_ allow that action since, unlike with regular file
	 * uploads, our avatars do not indeed have an associated File: page or anything
	 * like that, but IMO it makes sense to /not/ allow avatar uploads before
	 * confirming their email address in this particular case.)
	 *
	 * @see https://phabricator.wikimedia.org/T239447
	 *
	 * @param string $message HTML message to be passed to mainUploadForm
	 */
	protected function showRecoverableUploadError( $message ) {
		$stashStatus = $this->mUpload->tryStashFile( $this->getUser() );
		if ( $stashStatus->isGood() ) {
			$sessionKey = $stashStatus->getValue()->getFileKey();
			$uploadWarning = 'upload-tryagain';
		} else {
			$sessionKey = null;
			$uploadWarning = 'upload-tryagain-nostash';
		}
		$message = // '<h2>' . $this->msg( 'uploaderror' )->escaped() . "</h2>\n" .
			'<div class="error">' . $message . "</div>\n";

		$form = $this->getUploadForm( $message, $sessionKey );
		// $form->setSubmitText( $this->msg( $uploadWarning )->escaped() );
		$this->showUploadForm( $form );
	}

	/**
	 * Show some text and linkage on successful upload.
	 *
	 * @param string $ext File extension (gif, jpg or png)
	 */
	private function showSuccess( $ext ) {
		global $wgAvatarKey, $wgUploadBaseUrl, $wgUploadPath, $wgUploadAvatarInRecentChanges;

		$uploadPath = $wgUploadBaseUrl ? $wgUploadBaseUrl . $wgUploadPath : $wgUploadPath;

		$user = $this->getUser();
		$log = new LogPage( 'avatar' );
		if ( !$wgUploadAvatarInRecentChanges ) {
			$log->updateRecentChanges = false;
		}
		$log->addEntry(
			'avatar',
			$user->getUserPage(),
			$this->msg( 'user-profile-picture-log-entry' )->inContentLanguage()->text(),
			[],
			$user
		);

		$uid = $user->getId();
		$ts = rand();

		$output = UserProfile::getEditProfileNav( $this->msg( 'user-profile-section-picture' )->plain() );
		$output .= '<div class="profile-info">';
		$output .= '<p class="profile-update-title">' .
			$this->msg( 'user-profile-picture-yourpicture' )->plain() . '</p>';
		$output .= '<p>' . $this->msg( 'user-profile-picture-yourpicturestext' )->plain() . '</p>';

		$output .= '<table class="avatar-success-page">';
		$output .= '<tr>
			<td class="title-cell" valign="top">' .
				$this->msg( 'user-profile-picture-large' )->plain() .
			'</td>
			<td class="image-cell">
				<img src="' . $uploadPath . '/avatars/' . $wgAvatarKey . '_' . $uid . '_l.' . $ext . '?ts=' . $ts . '" alt="" />
			</td>
		</tr>';
		$output .= '<tr>
			<td class="title-cell" valign="top">' .
				$this->msg( 'user-profile-picture-medlarge' )->plain() .
			'</td>
			<td class="image-cell">
				<img src="' . $uploadPath . '/avatars/' . $wgAvatarKey . '_' . $uid . '_ml.' . $ext . '?ts=' . $ts . '" alt="" />
			</td>
		</tr>';
		$output .= '<tr>
			<td class="title-cell" valign="top">' .
				$this->msg( 'user-profile-picture-medium' )->plain() .
			'</td>
			<td class="image-cell">
				<img src="' . $uploadPath . '/avatars/' . $wgAvatarKey . '_' . $uid . '_m.' . $ext . '?ts=' . $ts . '" alt="" />
			</td>
		</tr>';
		$output .= '<tr>
			<td class="title-cell" valign="top">' .
				$this->msg( 'user-profile-picture-small' )->plain() .
			'</td>
			<td class="image-cell">
				<img src="' . $uploadPath . '/avatars/' . $wgAvatarKey . '_' . $uid . '_s.' . $ext . '?ts=' . $ts . '" alt="" />
			</td>
		</tr>';
		$output .= '<tr>
			<td>
				<input type="button" onclick="javascript:history.go(-1)" class="site-button" value="' . $this->msg( 'user-profile-picture-uploaddifferent' )->plain() . '" />
			</td>
		</tr>';
		$output .= '</table>';
		$output .= '</div>';

		$this->getOutput()->addHTML( $output );
	}

	/**
	 * Displays the main upload form, optionally with a highlighted
	 * error message up at the top.
	 *
	 * @param string $message Error message as HTML
	 * @param string $sessionKey Session key in case this is a stashed upload
	 * @param bool $hideIgnoreWarning Whether to hide "ignore warning" check box
	 * @return string HTML
	 */
	protected function getUploadForm( $message = '', $sessionKey = '', $hideIgnoreWarning = false ) {
		global $wgUseCopyrightUpload, $wgUserProfileDisplay;

		if ( $wgUserProfileDisplay['avatar'] === false ) {
			$message = $this->msg( 'socialprofile-uploads-disabled' )->plain();
		}

		if ( $message != '' ) {
			$sub = $this->msg( 'uploaderror' )->plain();
			$this->getOutput()->addHTML( "<h2>{$sub}</h2>\n" .
				"<h4 class='error'>{$message}</h4>\n" );
		}

		if ( $wgUserProfileDisplay['avatar'] === false ) {
			return '';
		}

		$ulb = $this->msg( 'uploadbtn' );

		$source = null;

		if ( $wgUseCopyrightUpload ) {
			$source = "
				<td align='right' nowrap='nowrap'>" . $this->msg( 'filestatus' )->plain() . "</td>
				<td><input tabindex='3' type='text' name=\"wpUploadCopyStatus\" value=\"" .
				htmlspecialchars( $this->mUploadCopyStatus ) . "\" size='40' /></td>
				</tr><tr>
				<td align='right'>" . $this->msg( 'filesource' )->plain() . "</td>
				<td><input tabindex='4' type='text' name='wpUploadSource' id='wpUploadSource' value=\"" .
				htmlspecialchars( $this->mUploadSource ) . "\" /></td>
				";
		}

		$output = UserProfile::getEditProfileNav( $this->msg( 'user-profile-section-picture' )->plain() );
		$output .= '<div class="profile-info">';

		if ( $this->getAvatar( 'l' ) != '' ) {
			$output .= '<table>
				<tr>
					<td>
						<p class="profile-update-title">' .
							$this->msg( 'user-profile-picture-currentimage' )->plain() .
						'</p>
					</td>
				</tr>';
				$output .= '<tr>
					<td>' . $this->getAvatar( 'l' ) . '</td>
				</tr>
			</table>';
		}

		$output .= '<form id="upload" method="post" enctype="multipart/form-data" action="">';
		// The following two lines are delicious copypasta from HTMLForm.php,
		// function getHiddenFields() and they are required; wpEditToken is, as
		// of MediaWiki 1.19, checked _unconditionally_ in
		// SpecialUpload::loadRequest() and having the hidden title doesn't
		// hurt either
		// @see https://phabricator.wikimedia.org/T32953
		$output .= Html::hidden( 'wpEditToken', $this->getUser()->getEditToken(), [ 'id' => 'wpEditToken' ] ) . "\n";
		$output .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) . "\n";
		$output .= '<table>
				<tr>
					<td>
						<p class="profile-update-title">' .
							$this->msg( 'user-profile-picture-choosepicture' )->plain() .
						'</p>
						<p style="margin-bottom:10px;">' .
							$this->msg( 'user-profile-picture-picsize' )->plain() .
						'</p>
						<input tabindex="1" type="file" name="wpUploadFile" id="wpUploadFile" size="36"/>
					</td>
				</tr>
				<tr>' . $source . '</tr>
				<tr>
					<td>
						<input tabindex="5" type="submit" name="wpUpload" class="site-button" value="' . $ulb . '" />
					</td>
				</tr>
			</table>
			</form>' . "\n";

		$output .= '</div>';

		return $output;
	}

	/**
	 * Gets an avatar image with the specified size
	 *
	 * @param string $size Size of the image
	 * - 's' for small
	 * - 'm' for medium
	 * - 'ml' for medium-large
	 * - 'l' for large
	 * @return string HTML
	 */
	function getAvatar( $size ) {
		global $wgAvatarKey, $wgUploadDirectory, $wgUploadBaseUrl, $wgUploadPath;

		$uploadPath = $wgUploadBaseUrl ? $wgUploadBaseUrl . $wgUploadPath : $wgUploadPath;

		$files = glob(
			$wgUploadDirectory . '/avatars/' . $wgAvatarKey . '_' .
			$this->getUser()->getId() . '_' . $size . '*'
		);
		if ( isset( $files[0] ) && $files[0] ) {
			return "<img src=\"{$uploadPath}/avatars/" .
				basename( $files[0] ) . '" alt="" border="0" />';
		}
	}

}
