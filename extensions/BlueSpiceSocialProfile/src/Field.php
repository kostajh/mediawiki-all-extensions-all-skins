<?php
namespace BlueSpice\Social\Profile;

use Config;
use Message;
use User;

abstract class Field implements IField {
	const KEY_I18N = 'i18n';
	const KEY_HIDDEN = 'hidden';

	const DFLT_FILTERABLE = false;
	const DFLT_SORTABLE = false;
	const DFLT_STORABLE = true;
	const DFLT_INDEXABLE = true;
	const DFLT_TYPE = 'string';

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @var string
	 */
	protected $i18n = '';

	/**
	 *
	 * @var bool
	 */
	protected $hidden = false;

	/**
	 *
	 * @param Config $config
	 * @param string $name
	 * @param array $definition
	 * @param User $user
	 */
	protected function __construct( $config, $name, $definition, $user ) {
		$this->config = $config;
		$this->name = $name;
		$this->user = $user;
		if ( isset( $definition[static::KEY_I18N] ) ) {
			$this->i18n = $definition[static::KEY_I18N];
		}
		if ( isset( $definition[static::KEY_HIDDEN] )
			&& $definition[static::KEY_HIDDEN] === true ) {
			$this->hidden = true;
		}
	}

	/**
	 *
	 * @return array
	 */
	public static function getFieldSchemeDefaults() {
		return [
			Schema::FILTERABLE => static::DEF_FILTERABLE,
			Schema::SORTABLE => false,
			Schema::STORABLE => true,
			Schema::INDEXABLE => true,
			Schema::TYPE => static::DFLT_TYPE,
		];
	}

	/**
	 *
	 * @param Config $config
	 * @param string $name
	 * @param array $definition
	 * @param User $user
	 * @return ConfigDefinition
	 */
	public static function getInstance( $config, $name, $definition, $user ) {
		$callback = static::class;
		$instance = new $callback(
			$config,
			$name,
			$definition,
			$user
		);
		return $instance;
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		if ( empty( $this->i18n ) ) {
			return $this->getName();
		}
		return Message::newFromKey( $this->i18n )->plain();
	}

	/**
	 *
	 * @return bool
	 */
	public function isHidden() {
		return $this->hidden;
	}
}
