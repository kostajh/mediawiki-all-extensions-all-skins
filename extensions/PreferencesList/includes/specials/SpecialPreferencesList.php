<?php
/*
 * Copyright (C) 2017 Ike Hecht <tosfos@yahoo.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * PreferencesList SpecialPage for PreferencesList extension
 *
 * @file
 * @ingroup Extensions
 */
class SpecialPreferencesList extends SpecialPage {
	/** @var string */
	protected $subpage;

	/** @var FormOptions */
	protected $options;

	/** @var array Form Field descriptors */
	protected $allPreferences;

	/**
	 * Result message to be displayed to the user
	 *
	 * @var string
	 */
	protected $resultMessage;

	/**
	 * The PreferencesList object
	 *
	 * @var PreferencesList
	 */
	protected $preferencesList;

	public function __construct() {
		parent::__construct( 'PreferencesList', 'preferenceslist' );
	}

	/**
	 * Show the page to the user
	 *
	 * @param string $subpage The subpage string argument (if any).
	 *  [[Special:PreferencesList/subpage]].
	 */
	public function execute( $subpage ) {
		parent::execute( $subpage );
		$this->subpage = $subpage;

		$out = $this->getOutput();

		$out->setPageTitle( $this->msg( 'preferenceslist-preferenceslist' ) );
		// Use the current user's info to figure out which fields are usually shown on the
		// Special:Preferences page
		$this->allPreferences = Preferences::getPreferences( $this->getUser(), $this->getContext() );

		$this->preferencesList = new PreferencesList( $this->getContext(), $this->allPreferences );
		$formFields = $this->preferencesList->getFormFields( $this->getOptions() );

		$htmlForm = new HTMLForm( $formFields, $this->getContext(), 'prefs' );
		$htmlForm->addHiddenField( 'submitted', true );
		$htmlForm->setSubmitCallback( [ $this, 'tryUISubmit' ] );
		$htmlForm->setMethod( 'get' );

		// Is there a better way to do this?
		if ( !$this->getRequest()->getVal( 'submitted' ) ) {
			$out->addWikiMsg( 'preferenceslist-preferenceslist-intro' );
			$htmlForm->displayForm( '' );
		} else {
			$htmlForm->loadData();
			$htmlForm->trySubmit();
			$out->addHTML( $this->resultMessage );
			$out->addReturnTo( SpecialPage::getTitleFor( 'PreferencesList' ) );
		}
	}

	/**
	 * Do Submit
	 *
	 * @param array $formData
	 * @param HTMLForm $form
	 * @return bool
	 */
	public function tryUISubmit( $formData, HTMLForm $form ) {
		// Special handling for the CSV field, which is not a preference
		$showCSV = $formData['csv'];
		unset( $formData['csv'] );

		// Remove false values
		$filteredFormData = array_filter( $formData );
		$preferencesToShow = array_keys( $filteredFormData );

		if ( empty( $preferencesToShow ) ) {
			$this->resultMessage = $this->msg( 'preferenceslist-nofields' );
			return false;
		}

		if ( $showCSV ) {
			$this->getOutput()->disable();
			$this->preferencesList->getResults(
				$preferencesToShow, PreferencesList::CSV, $form->getContext() );
			return true;
		} else {
			$this->resultMessage = $this->preferencesList->getResults( $preferencesToShow,
				PreferencesList::TABLE, $form->getContext() );
			return true;
		}

		// We should never get here.
		return false;
	}

	/**
	 * Returns group name
	 * @return string
	 */
	protected function getGroupName() {
		return 'users';
	}

	/**
	 * Get the FormOptions object
	 *
	 * @return FormOptions
	 */
	protected function getOptions() {
		if ( $this->options === null ) {
			$this->options = $this->setup();
		}

		return $this->options;
	}

	/**
	 * Set up the FormOptions object
	 *
	 * @return FormOptions
	 */
	protected function setup() {
		$opts = new FormOptions();
		foreach ( $this->allPreferences as $key => $params ) {
			if ( isset( $params['type'] ) && $params['type'] === 'api' ) {
				continue;
			}
			$opts->add( $key, false );
		}
		$opts->add( 'csv', false );

		return $opts;
	}
}
