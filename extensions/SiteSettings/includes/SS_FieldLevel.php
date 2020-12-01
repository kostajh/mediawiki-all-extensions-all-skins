<?php

/**
 * A class representing a single level of a site settings field (such as
 * "privacy") that has multiple levels.
 *
 * @author Yaron Koren
 */
class SSFieldLevel {

	private $id, $name, $description;

	static function create( $id, $name, $description ) {
		$fl = new SSFieldLevel();
		$fl->id = $id;
		$fl->name = $name;
		$fl->description = $description;
		return $fl;
	}

	public function getID() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getDescription() {
		return $this->description;
	}
}
