<?php

namespace NotesLink;

class Config extends \MultiConfig {

	public function __construct() {
		parent::__construct( [
			new \GlobalVarConfig( 'noteslink' ),
			new \GlobalVarConfig( 'wg' ),
		] );
	}

	/**
	 * Factory method used by \ConfigFactory
	 * @return \Config
	 */
	public static function newInstance() {
		return new self();
	}

}
