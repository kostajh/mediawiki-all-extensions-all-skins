<?php

class SpecialSecureAuthInfo extends SpecialPage {
	public function __construct() {
		parent::__construct( 'SecureAuthInfo', 'sa-access' );
	}

	/**
	 * Show the page to the user
	 *
	 * @param string|null $sub
	 */
	public function execute( $sub = null ) {
		$this->checkPermission();
		$out = $this->getOutput();

		$out->setPageTitle( $this->msg( 'special-secureauth-info' ) );
		$out->addHelpLink( 'Extension:SecureAuth' );

		$formDescriptor = $this->showuserinfo();

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext(), 'secureauth-info' );

		$htmlForm->suppressDefaultSubmit();

		$htmlForm->show();
	}

	/**
	 * @return null
	 */
	public function checkPermission() {
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return;
		}
	}

	/**
	 * @return array $formDescriptor Array for HTMLForm
	 */
	public function showUserInfo() {
		$formDescriptor = [
			'username' => [
				'section' => 'userinfo',
				'type' => 'info',
				'label-message' => 'username',
				'default' => $this->getUser()->getName() ,
				'raw' => true
			],
			'useremail' => [
				'section' => 'userinfo',
				'type' => 'info',
				'label-message' => 'youremail',
				'default' => $this->getUser()->getEmail() ,
				'raw' => true
			],
			'usereip' => [
				'section' => 'userinfo',
				'type' => 'info',
				'label-message' => 'secureauth-current-ip',
				'default' => $this->getRequest()->getIP() ,
				'raw' => true
			],
			'userewhitelistip' => [
				'section' => 'userinfo',
				'type' => 'info',
				'label-message' => 'secureauth-whitelist-ip',
				'default' => 'Sorry, The Extension is under development. We will provide this soon.',
				'raw' => true
			]
		];

		return $formDescriptor;
	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'users';
	}
}
