<?php

namespace BlueSpice\ContextMenu\MenuItem;

use MediaWiki\MediaWikiServices;

abstract class BaseFileAction extends Base {

	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 *
	 * @var \File
	 */
	protected $file = null;

	/**
	 *
	 * @param \Title $title
	 */
	public function __construct( $title ) {
		parent::__construct( $title );
		if ( $title->getNamespace() === NS_FILE || $title->getNamespace() === NS_MEDIA ) {
			$this->file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
		}
	}

}
