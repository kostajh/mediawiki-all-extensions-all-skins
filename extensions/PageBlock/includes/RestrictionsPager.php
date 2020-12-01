<?php

class RestrictionsPager extends AlphabeticPager {
	/** @var SpecialListRestrictions */
	public $mForm;
	public $mConds;
	private $type, $namespace, $indefonly;
	/** @var User */
	private $user;
	/** @var Title */
	private $title;

	function __construct( SpecialPage $form, $type, $namespace, $indefonly = false, $title = false, $user = false ) {
		$this->mForm = $form;
		$this->mConds = [];
		$this->type = ( $type ) ? $type : 'edit';
		$this->user = $user;
		$this->title = $title;
		$this->namespace = $namespace;
		$this->indefonly = (bool)$indefonly;
		parent::__construct( $form->getContext() );
	}

	function getStartBody() {
		# Do a link batch query
		$lb = new LinkBatch;
		foreach ( $this->mResult as $row ) {
			$lb->add( $row->page_namespace, $row->page_title );
		}
		$lb->execute();

		return '';
	}

	function formatRow( $row ) {
		return $this->mForm->formatRow( $row );
	}

	function getQueryInfo() {
		$conds = $this->mConds;
		$conds[] = 'pr_expiry>' . $this->mDb->addQuotes( $this->mDb->timestamp() );
		$conds[] = 'page_id=pr_page';
		$conds['pr_type'] = $this->type;

		if ( $this->title ) {
			$conds[] = 'pr_page=' . $this->title->getArticleID();
		}

		if ( $this->user ) {
			$conds[] = 'pr_user=' . $this->mDb->addQuotes( $this->user->getId() );
		}

		if ( $this->indefonly ) {
			$infinity = $this->mDb->addQuotes( $this->mDb->getInfinity() );
			$conds[] = "pr_expiry = $infinity";
		}

		return [
			'tables' => [ 'page_restrictions', 'page' ],
			'fields' => [ 'pr_id', 'page_namespace', 'page_title', 'page_len',
				'pr_type', 'pr_user', 'pr_expiry' ],
			'conds' => $conds
		];
	}

	function getIndexField() {
		return 'pr_id';
	}
}
