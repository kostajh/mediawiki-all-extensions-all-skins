<?php

namespace MediaWiki\Extension\FlexiSkin;

use JsonSerializable;

interface IFlexiSkin extends JsonSerializable {

	/**
	 * @return int|null
	 */
	public function getId() : ?int;

	/**
	 * @return string|null
	 */
	public function getName() : ?string;

	/**
	 * @return array|null
	 */
	public function getConfig() : ?array;

	/**
	 * @return bool
	 */
	public function isActive() : bool;

	/**
	 * Get value at particular path in the skin config
	 *
	 * @param string $path
	 * @return mixed|null if not found
	 */
	public function getValueForPath( $path );
}
