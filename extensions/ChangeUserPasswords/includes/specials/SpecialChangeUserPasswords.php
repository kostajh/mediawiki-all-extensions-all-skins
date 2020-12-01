<?php
/**
 * SpecialPage for Changing Passwords of all Users
 * (except the ones whitelisted in The Change User Passwords Special Page
 * @author Ankita Mandal, Mirco Zick
 * @file
 * @ingroup Extensions
 */

class SpecialChangeUserPasswords extends SpecialPage {
	public static $success = false;

	/**
	 * Initialize the special page.
	 */
	public function __construct() {
		parent::__construct( 'ChangeUserPasswords', 'changeuserpasswords' );
	}

	/**
	 * Shows the page to the user.
	 * @param string $sub The subpage string argument (if any).
	 *  [[Special:ChangeUserPassword/subpage]].
	 */
	public function execute( $sub ) {
		if ( !$this->getUser()->isAllowed( 'changeuserpasswords' ) ) {
			throw new PermissionsError( 'changeuserpasswords' );
		}

		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'changeuserpassword-title' ) );
		$dbr = wfGetDB( DB_MASTER );
		$maxUserId = 0;
		$res = $dbr->select( 'user',
			[ 'user_id', 'user_name' ],
			[ 'user_id > ' . $maxUserId ],
			__METHOD__,
			[
				'ORDER BY' => 'user_id',
			]
		);

		foreach ( $res as $row ) {
			$user = User::newFromName( $row->user_name );
			$msg = htmlspecialchars( User::newFromName( $row->user_name ) );
			$options[$msg] = $row->user_name;
		}

		$formDescriptor = [
			'userNamesSelect' => [
				'class' => 'HTMLMultiSelectField',
				'options' => $options,
				'default' => $options,
			],
		];
		// $msg = $this->msg( 'changeuserpassword-topheader' );
		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext(), 'testform' );
		$htmlForm->setIntro( $this->msg( 'changeuserpassword-topheader' ) );
		$htmlForm->setSubmitTextMsg( 'changeuserpassword-title' );
		$htmlForm->setSubmitCallback( [ $this, 'trySubmit' ] );
		$htmlForm->show();
		if ( $this::$success == true ) {
			$out = $this->getOutput();
			$out->addWikiMsg( 'changeuserpassword-success' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function getGroupName() {
		return 'users';
	}

	/**
	 * @param array $formData
	 * @return mixed
	 */
	public function trySubmit( $formData ) {
		if ( $formData['userNamesSelect'] ) {
			$passwordFactory = new PasswordFactory();
			$passwordFactory->init( RequestContext::getMain()->getConfig() );
			$maxUserId = 0;

			$blackList = $formData['userNamesSelect'];
			$dbr = wfGetDB( DB_MASTER );
			$contents = '<html><body><table border = "1" cellspacing = "5"  cellpadding = "5">';
			$contents .= '<tr><th><strong>' . "Username" . '</strong></th>' . '<th><strong>' .
				"New Password" . '</strong></th></tr>';

			do {
				$res = $dbr->select( 'user',
					[ 'user_id', 'user_name' ],
					[ 'user_id > ' . $maxUserId ],
					__METHOD__,
					[
						'ORDER BY' => 'user_id',
					]
				);

				foreach ( $res as $row ) {
					$password = $passwordFactory->generateRandomPasswordString( 10 );

					$user = User::newFromName( $row->user_name );
					$user = User::newFromId( $row->user_id );

					try {

						if ( in_array( $row->user_name, $blackList ) ) {

							$status = $user->changeAuthenticationData( [
								'username' => $user->getName(),
								'password' => $password,
								'retype' => $password,
							] );
							if ( !$status->isGood() ) {
								throw new PasswordError( $status->getWikiText( null, null, 'en' ) );
							}
							$user->saveSettings();

							$contents .= '<tr><td>' . $user->getName() . "        " . '</td><td>' .
								$password . '</td></tr>';

						}

					} catch ( PasswordError $pwe ) {
						$this->fatalError( $pwe->getText() );
					}

				}

				$maxUserId = $row->user_id;

			} while ( $res->numRows() );
			$contents .= '</table></body></html>';
			$this->getOutput()->addHTML( $contents );
			$this::$success = true;
			return true;
		}

		return 'Fail';
	}
}
