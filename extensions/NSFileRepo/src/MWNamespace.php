<?php

namespace NSFileRepo;

class MWNamespace implements \JsonSerializable {

	/**
	 *
	 * @var int
	 */
	protected $id = '';

	/**
	 *
	 * @var string
	 */
	protected $canonicalName = '';

	/**
	 *
	 * @var string
	 */
	protected $displayName = '';

	/**
	 *
	 * @param int $id
	 * @param string $canonicalName
	 * @param string $displayName
	 */
	public function __construct( $id, $canonicalName, $displayName ) {
		$this->id = (int)$id;
		$this->canonicalName = $canonicalName;
		$this->displayName = $displayName;
	}

	/**
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @return string
	 */
	public function getCanonicalName() {
		return $this->canonicalName;
	}

	/**
	 *
	 * @return string
	 */
	public function getDisplayName() {
		return $this->displayName;
	}

	/**
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'canonicalName' => $this->getCanonicalName(),
			'displayName' => $this->getDisplayName()
		];
	}

}