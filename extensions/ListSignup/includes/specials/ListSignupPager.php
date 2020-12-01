<?php

class ListSignupPager extends TablePager {
	public $mFieldNames = null;
	public $mQueryConds = [];
	public $mIncluding = false;
	public $mTableName = 'list_signup';

	/**
	 * @return array
	 */
	function getFieldNames() {
		if ( !$this->mFieldNames ) {
			$this->mFieldNames = [
				'ls_timestamp' => $this->msg( 'listfiles_date' )->text(),
				'ls_name' => $this->msg( 'listfiles_name' )->text(),
				'ls_email' => $this->msg( 'email' )->text(),
			];
		}

		return $this->mFieldNames;
	}

	function isFieldSortable( $field ) {
		$sortable = [ 'ls_timestamp' ];

		return in_array( $field, $sortable );
	}

	function getQueryInfo() {
		$fields = array_keys( $this->getFieldNames() );
		return [
			'tables' => $this->mTableName,
			'fields' => $fields
		];
	}

	function getDefaultSort() {
		return 'ls_timestamp';
	}

	function formatValue( $field, $value ) {
		switch ( $field ) {
			case 'ls_timestamp':
				return htmlspecialchars( $this->getLanguage()->userTimeAndDate( $value,
						$this->getUser() ) );
			case 'ls_name':
			case 'ls_email':
				return $value;
		}
	}

	function getForm() {
		$formDescriptor = [
			'select' => [
				'type' => 'select',
				'name' => 'limit',
				'label-message' => 'table_pager_limit_label',
				'options' => $this->getLimitSelectList(),
				'default' => $this->getLimit(),
			]
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm
			->setId( 'mw-listsignup-form' )
			->setMethod( 'get' )
			->setSubmitTextMsg( 'table_pager_limit_submit' )
			->setWrapperLegend( $this->msg( 'listsignupdisplay' )->text() )
			->prepareForm()
			->displayForm( false );

		return true;
	}
}
