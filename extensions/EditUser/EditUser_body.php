<?php
/* Shamelessly copied and modified from /includes/specials/SpecialPreferences.php v1.27alpha */
/**
 * Implements Special:Preferences
 *
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
 * @ingroup SpecialPage
 */

use MediaWiki\MediaWikiServices;

/**
 * A special page that allows users to change their preferences
 *
 * @ingroup SpecialPage
 */
class EditUser extends SpecialPage {
	public function __construct() {
		parent::__construct( 'EditUser', 'edituser' );
	}

	public function doesWrites() {
		return true;
	}

	public function execute( $par ) {
		$user = $this->getUser();
		$out = $this->getOutput();

		if ( !$user->isAllowed( 'edituser' ) ) {
			throw new PermissionsError( 'edituser' );
		}

		$this->setHeaders();

		$request = $this->getRequest();
		$this->target = ( isset( $par ) ) ? $par : $request->getText( 'username', '' );
		if ( $this->target === '' ) {
			$this->makeSearchForm();
			return;
		}
		$targetuser = User::NewFromName( $this->target );
		if ( $targetuser->getID() == 0 ) {
			$out->addWikiMsg( 'edituser-nouser', htmlspecialchars( $this->target ) );
			return;
		}
		$this->targetuser = $targetuser;
		# Allow editing self via this interface
		if ( $targetuser->isAllowed( 'edituser-exempt' ) && $targetuser->getName() != $user->getName() ) {
			$out->addWikiMsg( 'edituser-exempt', $targetuser->getName() );
			return;
		}

		$this->setHeaders();
		$this->outputHeader();
		$out->disallowUserJs(); # Prevent hijacked user scripts from sniffing passwords etc.

		$this->checkReadOnly();

		if ( $request->getCheck( 'reset' ) ) {
			$this->showResetForm();

			return;
		}

		$out->addModules( 'mediawiki.special.preferences' );
		$out->addModuleStyles( 'mediawiki.special.preferences.styles' );

		// $this->loadGlobals( $this->target );
		$this->makeSearchForm();
		$out->addHtml( '<br>' );
		# End EditUser additions

		if ( $this->getRequest()->getCheck( 'success' ) ) {
			$out->wrapWikiMsg(
				Html::rawElement(
					'div',
					[
						'class' => 'mw-preferences-messagebox successbox',
						'id' => 'mw-preferences-success'
					],
					Html::element( 'p', [], '$1' )
				),
				'savedprefs'
			);
		}

		if ( $this->getRequest()->getCheck( 'eauth' ) ) {
			$out->wrapWikiMsg(
				Html::rawElement(
					'div',
					[
						'class' => 'error',
						'style' => 'clear: both;'
					],
					Html::element( 'p', [], '$1' )
				),
				'eauthentsent',
				$this->target
			);
		}

		$this->addHelpLink( 'Help:Preferences' );

		$preferencesFactory = MediaWikiServices::getInstance()->getPreferencesFactory();
		$htmlForm = $preferencesFactory->getForm( $targetuser,
			$this->getContext(),
			EditUserPreferencesForm::class,
			[ 'password' ]
		);

		$htmlForm->addHiddenField( 'username', $this->target );

		$htmlForm->show();
	}

	private function showResetForm() {
		if ( !$this->getUser()->isAllowed( 'editmyoptions' ) ) {
			throw new PermissionsError( 'editmyoptions' );
		}

		if ( !$this->getUser()->isAllowed( 'edituser' ) ) {
			throw new PermissionsError( 'edituser' );
		}

		$this->getOutput()->addWikiMsg( 'prefs-reset-intro' );

		$htmlForm = HTMLForm::factory( 'ooui', [], $this->getContext(), 'prefs-restore' );
		$htmlForm
			->addHiddenField( 'username', $this->target )
			->addHiddenField( 'reset', '1' )
			->setSubmitTextMsg( 'restoreprefs' )
			->setSubmitCallback( [ $this, 'submitReset' ] )
			->setSubmitDestructive()
			->suppressReset()
			->show();
	}

	public function submitReset( $formData ) {
		if ( !$this->getUser()->isAllowed( 'editmyoptions' ) ) {
			throw new PermissionsError( 'editmyoptions' );
		}

		if ( !$this->getUser()->isAllowed( 'edituser' ) ) {
			throw new PermissionsError( 'edituser' );
		}

		$this->targetuser->resetOptions( 'all', $this->getContext() );
		$this->targetuser->saveSettings();

		$url = $this->getPageTitle()->getFullURL( [ 'success' => 1, 'username' => $this->target ] );

		$this->getOutput()->redirect( $url );

		return true;
	}

	public function makeSearchForm() {
		global $wgScript;

		$formDescriptor = [
			'textbox' => [
				'type' => 'user',
				'name' => 'username',
				'label-message' => 'edituser-username',
				'default' => $this->target,
				'required' => true,
			]
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm
			->addHiddenField( 'title', $this->getPageTitle()->getPrefixedDBkey() )
			->setAction( $wgScript )
			->setMethod( 'get' )
			->setSubmitTextMsg( 'edituser-dosearch' )
			->prepareForm()
			->displayForm( false );

		return true;
	}

	protected function getGroupName() {
		return 'users';
	}
}
