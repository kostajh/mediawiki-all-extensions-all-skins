<?php

namespace BlueSpice\Social\EntityListContext;

class Tag extends \BlueSpice\Social\EntityListContext {

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 5;
	}

	/**
	 *
	 * @return bool
	 */
	public function useEndlessScroll() {
		return false;
	}
}
