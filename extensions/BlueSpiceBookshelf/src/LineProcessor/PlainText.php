<?php

namespace BlueSpice\Bookshelf\LineProcessor;

use BlueSpice\Bookshelf\ILineProcessor;
use BlueSpice\Bookshelf\TreeNode;

class PlainText extends LineProcessorBase implements ILineProcessor {

	/**
	 * Process the given line
	 *
	 * @param string $line
	 * @return TreeNode
	 */
	public function process( $line ) {
		$result = new TreeNode();
		$result['type'] = 'plain-text';
		$result['title'] = $line;
		$result['display-title'] = $line;
		$result['bookshelf']['type'] = 'text';

		return $result;
	}

	/**
	 * @param string $line
	 * @return bool
	 */
	public function applies( $line ) {
		// Plain text, being the default, applies to all,
		// and should be overwritten if other line processors apply
		return true;
	}
}
