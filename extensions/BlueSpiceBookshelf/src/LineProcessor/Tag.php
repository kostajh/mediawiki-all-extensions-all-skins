<?php

namespace BlueSpice\Bookshelf\LineProcessor;

use BlueSpice\Bookshelf\ILineProcessor;
use BlueSpice\Bookshelf\TreeNode;
use DOMDocument;

class Tag extends LineProcessorBase implements ILineProcessor {

	/**
	 * Process the given line
	 *
	 * @param string $line
	 * @return TreeNode
	 */
	public function process( $line ) {
		$result = new TreeNode();
		$oDOM = new DOMDocument();
		$oDOM->loadXML( $line );
		// This is generally dangerous. But in current context it should be okay
		$oTag = $oDOM->firstChild;
		$aAttributes = [];
		foreach ( $oTag->attributes as $sAttributName => $sAttributValue ) {
			// Filter out standard parameters 'text'
			if ( strtolower( $sAttributName ) === 'text' ) {
				continue;
			}
			$aAttributes[$sAttributName] = $sAttributValue->value;
		}
		$result['type'] = 'tag';
		$result['title'] = $oTag->getAttribute( 'text' );
		$result['display-title'] = $oTag->getAttribute( 'text' );
		$result['bookshelf']['type'] = 'tag';
		$result['bookshelf']['text'] = $oTag->getAttribute( 'text' );
		$result['bookshelf']['arguments'] = $aAttributes;

		return $result;
	}

	/**
	 * @param string $line
	 * @return bool
	 */
	public function applies( $line ) {
		if ( preg_match( '#^<.*?>$#', $line ) === 1 ) {
			return true;
		}
		return false;
	}
}
