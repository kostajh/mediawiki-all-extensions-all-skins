<?php

namespace Expressions;

final class Node {
	/**
	 * @var string|null
	 */
	private $operator;

	/**
	 * @var array|null
	 */
	private $operands;

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @var int
	 */
	private $offset_start;

	/**
	 * @var int
	 */
	private $offset_end;

	/**
	 * Node constructor.
	 *
	 * @param string|null $operator
	 * @param array|null $operands
	 * @param string $value
	 * @param int $offset_start
	 * @param int $offset_end
	 */
	public function __construct( $operator, $operands, $value, $offset_start, $offset_end ) {
		$this->operator = $operator;
		$this->operands = $operands;
		$this->value = $value;
		$this->offset_start = $offset_start;
		$this->offset_end = $offset_end;
	}

	/**
	 * @return string|null
	 */
	public function getOperator() {
		return $this->operator;
	}

	/**
	 * @return array|null
	 */
	public function getOperands() {
		return $this->operands;
	}

	/**
	 * @return int
	 */
	public function getOffsetEnd() {
		return $this->offset_end;
	}

	/**
	 * @return int|bool|string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return int
	 */
	public function getOffsetStart() {
		return $this->offset_start;
	}

	/**
	 * @param int $offset
	 */
	public function setOffsetStart( $offset ) {
		$this->offset_start = $offset;
	}

	/**
	 * @param int $offset
	 */
	public function setOffsetEnd( $offset ) {
		$this->offset_end = $offset;
	}
}
