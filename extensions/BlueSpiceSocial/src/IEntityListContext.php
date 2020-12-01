<?php

namespace BlueSpice\Social;

use BlueSpice\Social\Data\Entity\Schema;
use IContextSource;

interface IEntityListContext extends IContextSource {
	/**
	 * @return int
	 */
	public function getLimit();

	/**
	 * @return int
	 */
	public function getStart();

	/**
	 * @return array
	 */
	public function getSort();

	/**
	 * @return array
	 */
	public function getFilters();

	/**
	 * @return string[]
	 */
	public function getAvailableFilterFields();

	/**
	 * @return string[]
	 */
	public function getAvailableSorterFields();

	/**
	 * @return string[]
	 */
	public function getAllowedTypes();

	/**
	 * @return bool
	 */
	public function showEntityListMenu();

	/**
	 * @return bool
	 */
	public function showEntityListMore();

	/**
	 * @return bool
	 */
	public function showEntitySpawner();

	/**
	 * @return bool
	 */
	public function showHeadline();

	/**
	 * @return string
	 */
	public function getHeadlineMessageKey();

	/**
	 * @return bool
	 */
	public function useEndlessScroll();

	/**
	 * @return array
	 */
	public function getOutputTypes();

	/**
	 * @return array
	 */
	public function getPreloadTitles();

	/**
	 * @return string[]
	 */
	public function getLockedOptionNames();

	/**
	 * @return string[]
	 */
	public function getLockedFilterNames();

	/**
	 * @return Schema
	 */
	public function getSchema();

	/**
	 * @return Entity|null
	 */
	public function getParent();

	/**
	 * @return \stdClass[]
	 */
	public function getPreloadedEntities();

	/**
	 * @return sring
	 */
	public function getRendererName();
}
