<?php

namespace BlueSpice\Bookshelf;

interface ILineProcessor {
	/**
	 * Process the given line
	 *
	 * @param string $line
	 * @return TreeNode
	 */
	public function process( $line );

	/**
	 * Determine if the given line should be processed
	 *
	 * @param string $line
	 * @return bool
	 */
	public function applies( $line );

	/**
	 * Should processing be ended after this
	 * processor runs?
	 *
	 * @return bool
	 */
	public function isFinal();
}
