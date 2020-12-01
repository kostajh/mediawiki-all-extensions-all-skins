<?php
/**
 * Implements SpecialListSignupDisplay
 *
 * @file
 * @ingroup SpecialPage
 */
class SpecialListSignupPurge extends FormSpecialPageMessaged {

	function __construct() {
		parent::__construct( 'ListSignupPurge', '', false );
	}

	protected function getFormFields() {
		// Overriding abstract function
		return [];
	}

	public function checkExecutePermissions( User $user ) {
		if ( !$this->getUser()->isAllowed( 'purgelistsignupdisplay' ) ) {
			throw new PermissionsError( 'purgelistsignupdisplay' );
		}
		parent::checkExecutePermissions( $user );
	}

	protected function alterForm( HTMLForm $form ) {
		$form
			->setWrapperLegendMsg( 'listsignuppurge-legend' )
			->setSubmitDestructive()
			->setSubmitTextMsg( 'delete' );
	}

	public function onSubmit( array $data ) {
		return ListSignup::purge();
	}

	protected function getDisplayFormat() {
		return 'ooui';
	}
}
