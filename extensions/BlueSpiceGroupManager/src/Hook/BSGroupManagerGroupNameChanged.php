<?php

namespace BlueSpice\GroupManager\Hook;

abstract class BSGroupManagerGroupNameChanged extends \BlueSpice\Hook {
	/**
	 * @var string
	 */
	protected $group;

	/**
	 * @var string
	 */
	protected $newGroup;
	/**
	 * @var array
	 */
	protected $result;

	/**
	 * @param string $group
	 * @param string $newGroup
	 * @param array &$result
	 * @return bool
	 */
	public static function callback( $group, $newGroup, &$result ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$group,
			$newGroup,
			$result
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param string $group
	 * @param string $newGroup
	 * @param array &$result
	 */
	public function __construct( $context, $config, $group, $newGroup, &$result ) {
		parent::__construct( $context, $config );

		$this->group = $group;
		$this->newGroup = $newGroup;
		$this->result =& $result;
	}
}
