<?php
/**
 * Implements SpecialListSignup - allows user to submit name and email
 *
 * @file
 * @ingroup SpecialPage
 */
class SpecialListSignup extends FormSpecialPageMessaged {

	function __construct() {
		parent::__construct( 'ListSignup' );
	}

	protected function getFormFields() {
		$user = $this->getUser();
		return [
			'Name' => [
				'type' => 'text',
				'label-message' => 'listfiles_name', /* may be inaccurate in non-English */
				'required' => 'true',
				'default' => $user->getRealName(),
			],
			'Email' => [
				'type' => 'email',
				'label-message' => 'email',
				'required' => 'true',
				'default' => $user->getEmail(),
			],
		];
	}

	public function onSubmit( array $data ) {
		# justincase validation - overkill?
		$info = [];
		if ( isset( $data['Name'] ) && $data['Name'] !== '' ) {
			$info['ls_name'] = $data['Name'];
		}
		if ( isset( $data['Email'] ) && $data['Email'] !== '' && Sanitizer::validateEmail( $data['Email'] ) ) {
			$info['ls_email'] = $data['Email'];
		}

		return ListSignup::addRow( $info );
	}

	protected function getDisplayFormat() {
		return 'ooui';
	}
}
