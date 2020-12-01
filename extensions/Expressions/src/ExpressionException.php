<?php

namespace Expressions;

/**
 * Class ExpressionException
 *
 * @package Expressions
 */
class ExpressionException extends \Exception {
	/**
	 * @var string
	 */
	private $message_name;

	/**
	 * @var array
	 */
	private $message_parameters = [];

	/**
	 * @var string
	 */
	private $submessage_name;

	/**
	 * @var array
	 */
	private $submessage_parameters = [];

	/**
	 * @var string
	 */
	private $hint_name;

	/**
	 * @var array
	 */
	private $hint_parameters = [];

	/**
	 * @var int
	 */
	private $offset_start;

	/**
	 * @var int
	 */
	private $offset_end;

	/**
	 * ExpressionException constructor.
	 *
	 * @param string $message_name
	 * @param array $message_parameters
	 * @param string $submessage_name
	 * @param array $submessage_parameters
	 * @param string $hint_name
	 * @param array $hint_parameters
	 * @param int $offset_start
	 * @param int $offset_end
	 */
	public function __construct(
		$message_name,
		$message_parameters,
		$submessage_name,
		$submessage_parameters,
		$hint_name,
		$hint_parameters,
		$offset_start,
		$offset_end
	) {
		$this->message_name = $message_name;
		$this->message_parameters = $message_parameters;

		$this->submessage_name = $submessage_name;
		$this->submessage_parameters = $submessage_parameters;

		$this->hint_name = $hint_name;
		$this->hint_parameters = $hint_parameters;

		$this->offset_start = $offset_start;
		$this->offset_end = $offset_end;

		parent::__construct();
	}

	/**
	 * @return string
	 */
	public function getMessageName() {
		return $this->message_name;
	}

	/**
	 * @return array
	 */
	public function getMessageParameters() {
		return $this->message_parameters;
	}

	/**
	 * @return string
	 */
	public function getSubmessageName() {
		return $this->submessage_name;
	}

	/**
	 * @return array
	 */
	public function getSubmessageParameters() {
		return $this->submessage_parameters;
	}

	/**
	 * @return string
	 */
	public function getHintName() {
		return $this->hint_name;
	}

	/**
	 * @return array
	 */
	public function getHintParameters() {
		return $this->hint_parameters;
	}

	/**
	 * @return int
	 */
	public function getOffsetStart() {
		return $this->offset_start;
	}

	/**
	 * @return int
	 */
	public function getOffsetEnd() {
		return $this->offset_end;
	}
}
