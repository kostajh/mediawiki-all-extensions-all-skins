<?php

namespace BlueSpice\CustomMenu;

interface ICustomMenu {
	const NUM_ENTRIES_UNLIMITED = -1;

	/**
	 * @return \BlueSpice\Renderer
	 */
	public function getRenderer();

	/**
	 * @return \BlueSpice\Data\RecordSet
	 */
	public function getData();

	/**
	 * @return string
	 */
	public function getKey();

	/**
	 * @return null
	 */
	public function invalidate();

	/**
	 * @return int
	 */
	public function numberOfLevels();

	/**
	 * @return int
	 */
	public function numberOfMainEntries();

	/**
	 * @return int
	 */
	public function numberOfSubEntries();

	/**
	 * @return string|null
	 */
	public function getEditURL();
}
