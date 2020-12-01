<?php

namespace BlueSpice\ContextMenu\MenuItem;

abstract class BaseUserAction extends Base {

	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 *
	 * @var \User
	 */
	protected $targetUser = null;

	/**
	 *
	 * @param \Title $title
	 */
	public function __construct( $title ) {
		parent::__construct( $title );
		if ( $title->getNamespace() === NS_USER ) {
			$this->targetUser = \User::newFromName( $title->getPrefixedDBkey() );
		}
	}

}
