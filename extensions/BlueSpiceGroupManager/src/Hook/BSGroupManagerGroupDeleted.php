<?php

namespace BlueSpice\GroupManager\Hook;

abstract class BSGroupManagerGroupDeleted extends \BlueSpice\Hook {
	/**
	 * @var string
	 */
	protected $group;

	/**
	 * @var array
	 */
	protected $result;

	/**
	 * @param string $group
	 * @param array &$result
	 * @return bool
	 */
	public static function callback( $group, &$result ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$group,
			$result
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param string $group
	 * @param array &$result
	 */
	public function __construct( $context, $config, $group, &$result ) {
		parent::__construct( $context, $config );

		$this->group = $group;
		$this->result =& $result;
	}
}
