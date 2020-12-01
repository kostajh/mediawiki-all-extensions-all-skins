<?php

namespace MediaWiki\Extension\FlexiSkin;

interface IFlexiSkinManager {
	/**
	 * @param string $name
	 * @param array $config
	 * @return IFlexiSkin
	 */
	public function create( $name, $config ) : IFlexiSkin;

	/**
	 * @param IFlexiSkin $flexiSkin
	 * @return bool
	 */
	public function save( IFlexiSkin $flexiSkin );

	/**
	 * @return IFlexiSkin|null
	 */
	public function getFlexiSkin() : ?IFlexiSkin;

	/**
	 * @param bool|null $active
	 * @return bool
	 */
	public function setActive( $active = true ) : bool;

	/**
	 * @return IFlexiSkin|null
	 */
	public function getActive() : ?IFlexiSkin;

	/**
	 * Get all available plugins
	 *
	 * @return IPlugin[]
	 */
	public function getPlugins(): array;
}
