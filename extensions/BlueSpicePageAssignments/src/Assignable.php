<?php
namespace BlueSpice\PageAssignments;

abstract class Assignable implements IAssignable {

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var stirng
	 */
	protected $type = 'base';

	/**
	 *
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param string $type
	 */
	public function __construct( $context, $config, $type ) {
		$this->config = $config;
		$this->type = $type;
		$this->context = $context;
	}

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @return string
	 */
	public function getRendererKey() {
		return "assignment";
	}
}
