<?php

namespace BlueSpice\Bookshelf\Special;

use BlueSpice\Bookshelf\BookEditData;
use Html;
use SpecialPage;

class Bookshelf extends SpecialPage {
	/** @var BookEditData */
	protected $bookEditData;

	public function __construct() {
		parent::__construct( 'Bookshelf', 'bookshelf-viewspecialpage' );
	}

	/**
	 *
	 * @param string $subPage
	 * @return void
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$this->getOutput()->addHTML(
			Html::element( 'div', [
				'id' => 'bs-bookshelf-container',
				'style' => 'height: 1000px',
				'class' => 'dynamic-graphical-list-body'
			] )
		);
		$this->getOutput()->addModules( "ext.bluespice.bookshelf.special" );
	}
}
