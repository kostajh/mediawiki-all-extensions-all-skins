<?php
class SpecialSecureHTML extends SpecialPage {
	function __construct() {
		global $wgSecureHTMLSpecialRight;

		parent::__construct( 'SecureHTML', $wgSecureHTMLSpecialRight );
	}

	function execute( $par ) {
		global $wgSecureHTMLSecrets;
		global $wgSecureHTMLTag;
		global $wgSecureHTMLSpecialDropdown;

		$request = $this->getRequest();
		$output = $this->getOutput();
		$user = $this->getUser();

		if ( !$this->userCanExecute( $user ) ) {
			$this->displayRestrictionError();
			return;
		}

		$this->setHeaders();

		if ( count( $wgSecureHTMLSecrets ) == 0 ) {
			$output->addWikiTextAsInterface( wfMessage( 'securehtml-nokeys' ) );
			return;
		}

		$keyname = $request->getText( 'wpsecurehtmlkeyname' );
		$keysecret = $request->getText( 'wpsecurehtmlkeysecret' );
		$html = $request->getText( 'wpsecurehtmlraw' );
		$html_lf = str_replace( "\r\n", "\n", $html );

		if ( !array_key_exists( $keyname, $wgSecureHTMLSecrets ) ) {
			$keyname = '';
		}

		$keyalgorithm = 'sha256';
		if ( is_array( $wgSecureHTMLSecrets[$keyname] ) ) {
			if ( array_key_exists( 'algorithm', $wgSecureHTMLSecrets[$keyname] ) ) {
				$keyalgorithm = $wgSecureHTMLSecrets[$keyname]['algorithm'];
			}
		}

		if ( $keysecret && $html ) {
			$generated = '<' . $wgSecureHTMLTag . ' ' . ( $keyname ? 'keyname="' . htmlspecialchars( $keyname ) . '" ' : '' ) . 'hash="' . hash_hmac( $keyalgorithm, $html_lf, $keysecret ) . '">';
			$generated .= $html_lf;
			$generated .= '</' . $wgSecureHTMLTag . '>';
			$output->addWikiTextAsInterface( '== ' . wfMessage( 'securehtml-generatedoutput-title' ) . ' ==' );
			$params = array(
				'readonly' => 'readonly',
			);
			$output->addHTML( Html::element( 'textarea', $params, $generated ) );
			$output->addWikiTextAsInterface( wfMessage( 'securehtml-outputinstructions' ) );
			$output->addWikiTextAsInterface( '== ' . wfMessage( 'securehtml-renderedhhtml-title' ) . ' ==' );
			$output->addWikiTextAsInterface( $generated );
		}

		$output->addWikiTextAsInterface( '== ' . wfMessage( 'securehtml-input-title' ) . ' ==' );
		$output->addWikiTextAsInterface( wfMessage( 'securehtml-inputinstructions' ) );

		$formDescriptor = array();

		if ( $wgSecureHTMLSpecialDropdown ) {
			$keyname_labels = array(
				'' => '',
			);
			foreach ( array_keys( $wgSecureHTMLSecrets ) as $skeyname ) {
				$keyname_labels[$skeyname] = $skeyname;
			}
			$formDescriptor['securehtmlkeyname'] = array(
				'type' => 'select',
				'label-message' => 'securehtml-form-keyname',
				'options' => $keyname_labels,
			);
		} else {
			$formDescriptor['securehtmlkeyname'] = array(
				'type' => 'text',
				'label-message' => 'securehtml-form-keyname',
			);
		}
		$formDescriptor['securehtmlkeysecret'] = array(
			'type' => 'password',
			'label-message' => 'securehtml-form-keysecret',
			'required' => true,
		);
		$formDescriptor['securehtmlraw'] = array(
			'type' => 'textarea',
			'label-message' => 'securehtml-form-html',
			'id' => 'wpSecureHTMLRawHTML',
			'cols' => 80,
			'rows' => 25,
			'required' => true,
		);

		$htmlForm = new HTMLForm( $formDescriptor, $this->getContext(), 'securehtml' );
		$htmlForm->setSubmitCallback( array( 'SpecialSecureHTML', 'trySubmit' ) );
		$htmlForm->show();
	}

	function trySubmit( $formData ) {
		# Always display the form.
		return false;
	}

	protected function getGroupName() {
		return 'wiki';
	}
}
