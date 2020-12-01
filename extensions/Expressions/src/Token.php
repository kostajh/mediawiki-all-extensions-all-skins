<?php

namespace Expressions;

class Token {
	/**
	 * @var string
	 */
	private $match;

	/**
	 * @var string
	 */
	private $token_type;

	/**
	 * @var int
	 */
	private $offset;

	/**
	 * Token constructor.
	 *
	 * @param string $match
	 * @param string $token_type
	 * @param int $offset
	 */
	public function __construct( $match, $token_type, $offset ) {
		$this->match = $match;
		$this->token_type = $token_type;
		$this->offset = $offset;
	}

	/**
	 * @return string
	 */
	public function getMatch() {
		return $this->match;
	}

	/**
	 * @return string
	 */
	public function getTokenType() {
		return $this->token_type;
	}

	/**
	 * @return int
	 */
	public function getOffset() {
		return $this->offset;
	}
}
