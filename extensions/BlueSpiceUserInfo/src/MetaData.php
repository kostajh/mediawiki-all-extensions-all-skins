<?php
namespace BlueSpice\UserInfo;

use Config;
use JsonSerializable;
use User;

abstract class MetaData implements IMetaData, JsonSerializable {

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
	 * @var bool
	 */
	protected $hidden = false;

	/**
	 * Constructor
	 * @param Config $config
	 * @param string $name
	 * @param User $user
	 * @param bool $hidden
	 */
	protected function __construct( $config, $name, $user, $hidden ) {
		$this->config = $config;
		$this->name = $name;
		$this->user = $user;
		$this->hidden = $hidden;
	}

	/**
	 *
	 * @param Config $config
	 * @param string $name
	 * @param User $user
	 * @param bool $hidden
	 * @return ConfigDefinition
	 */
	public static function getInstance( $config, $name, $user, $hidden ) {
		$callback = static::class;
		$instance = new $callback(
			$config,
			$name,
			$user,
			$hidden
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
	 * @return bool
	 */
	public function isHidden() {
		return $this->hidden;
	}

	/**
	 *
	 * @return bool
	 */
	public function isTitle() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	public function isSubTitle() {
		return false;
	}

	/**
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'name' => $this->getName(),
			'value' => $this->getValue(),
			'label' => $this->getLabel(),
			'hidden' => $this->isHidden(),
			'title' => $this->isTitle(),
			'subtitle' => $this->isSubTitle(),
		];
	}
}
