<?php

class SpecialListRestrictions extends FormSpecialPage {

	protected $title;

	protected $user;

	public function __construct() {
		parent::__construct( 'ListRestrictions' );
	}

	protected function setParameter( $par ) {
		parent::setParameter( $par );
		if ( $this->par ) {
			list( $this->title, $this->user ) = SpecialPageBlock::parseParameter( $this->par );
		}
	}

	protected function alterForm( HTMLForm $form ) {
		$form->setMethod( 'GET' );
		$context = new DerivativeContext( $this->getContext() );
		$context->setTitle( $this->getPageTitle() ); // Strip subpage
		$form->setContext( $context );
	}

	protected function getFormFields() {
		return [
			'page' => [
				'type' => 'text',
				'name' => 'page',
				'default' => $this->title ?: '',
				'label-message' => 'pageblock-list-title'
			],
			'user' => [
				'type' => 'text',
				'name' => 'user',
				'default' => $this->user ?: '',
				'label-message' => 'pageblock-list-user'
			],
			'indefonly' => [
				'type' => 'check',
				'name' => 'indefonly',
				'label-message' => 'pageblock-list-indefonly',
			],
		];
	}

	public function onSubmit( array $data ) {
		$form = $this->getForm();
		$form->mFieldData = $data; // Well, this works!
		$form->displayForm( false );
		// Now the pager thingy
		$pager = new RestrictionsPager(
			$this,
			[ 'edit', 'move' ], // @todo make this a selector on the page
			0, // @todo implement namespace
			$data['indefonly'],
			$this->title ?: false,
			$this->user ?: false
		);

		if ( $pager->getNumRows() ) {
			$this->getOutput()->addHTML(
				$pager->getNavigationBar() .
				'<ul>' . $pager->getBody() . '</ul>' .
				$pager->getNavigationBar()
			);
		} else {
			$this->getOutput()->addWikiMsg( 'pageblock-list-empty' );
		}

		return true;
	}

	public function formatRow( $row ) {
		$user = User::newFromId( $row->pr_user );
		$title = Title::newFromRow( $row );
		$indef = $row->pr_expiry === 'infinity';
		$key = "pageblock-restricted-{$row->pr_type}" . ( $indef ? '-indef' : '' );
		$msg = $this->msg( $key )->rawParams(
			Linker::userLink( $user->getId(), $user->getName() ) . Linker::userToolLinks( $user->getId(), $user->getName() ),
			Linker::link( $title ),
			$this->getLanguage()->formatExpiry( $row->pr_expiry ) // If indef, this is ignored.
		);

		return "<li>{$msg->parse()}</li>";
	}

}
