<?php

namespace MediaWiki\Extension\Tei\Model;

/**
 * @license GPL-2.0-or-later
 *
 * Definition of a TEI tag
 */
class ElementSpec {

	/**
	 * @var string
	 */
	private $ident;

	/**
	 * @var mixed[]
	 */
	private $data;

	/**
	 * @param string $ident tag name
	 * @param string[] $data the element spec data
	 */
	public function __construct( $ident, array $data ) {
		$this->ident = $ident;
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->ident;
	}

	/**
	 * @return array
	 */
	public function getContent() {
		return $this->data['content'];
	}
}
