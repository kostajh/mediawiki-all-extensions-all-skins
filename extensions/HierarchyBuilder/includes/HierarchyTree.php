<?php

/*
 * Copyright (c) 2014 The MITRE Corporation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

class HierarchyTree {
	protected $root = null;

	function __construct() {
		//$root = $node;
	}

	public static function fromNode( $node ) {
		$instance = new self();
		$instance->loadRootNode( $node );
		return $instance;
	}

	public static function fromWikitext( $wikitextHierarchy ) {
		$hierarchy = "[[Hierarchy_Root]]\n" . $wikitextHierarchy;
		$rootNode = HierarchyTree::parseHierarchyToTreeHelper( $hierarchy, '' );

		$instance = new self();
		$instance->loadRootNode( $rootNode );
		return $instance;
	}

	private function loadRootNode( $node ) {
		$this->root = $node;
	}

	private static function parseHierarchyToTreeHelper( $wikiTextHierarchy, $depth ) {
		$curRootAndChildren = HierarchyBuilder::splitHierarchy($wikiTextHierarchy, $depth);
		$curRootText = $curRootAndChildren[0];
		$curChildrenText = array_slice($curRootAndChildren, 1);

		$curRootNode = new TreeNode( $depth . $curRootText );

		foreach ( $curChildrenText as $childText ) {
			$childNode = self::parseHierarchyToTreeHelper( $childText, $depth . '*' );
			$curRootNode->getValue();
			$curRootNode->addChild( $childNode );
		}

		return $curRootNode;
	}

	public function __toString() {
		return $this->serialize( $this->root );
	}

	private function serialize( $node ) {
		$returnValue = '';
		if ( $node != null) {
			if ( $node != $this->root) {
				$returnValue .= $node->getValue() . "\n";
			}
			if (
				$node->getChildren() != null &&
				count( $node->getChildren() ) > 0
			) {
				foreach ( $node->getChildren() as $child ) {
					$returnValue .= $this->serialize($child);
				}
			}
		}

		return $returnValue;
	}

	/**
	 * Returns the MST that is a subtree of this hierarchy containing the
	 * specified rows and the hierarchy root.
	 *
	 * The returned MST will always contain this hierarchy's root node, each of
	 * the specified target rows, and any intermediate nodes from the hierarchy
	 * which are necessary to form a connected graph.
	 *
	 * @param array $rows: List of targeted rows within the hierarchy that must
	 *  included in the MST.
	 *
	 * @return HierarchyTree: A new HierarchyTree object representing the MST
	 *  that holds all the targeted rows in $rows
	 */
	public function getMST( array $rows ) {
		$mstRoot = $this->getMSTHelper( $this->root, $rows );
		$mst = self::fromNode( $mstRoot );
		return $mst;
	}

	/**
	 * Helper function to return a branch or subbranch of the final MST.
	 *
	 * Logically this function proceeds as follows. If the current node being
	 * examined is one of the targeted rows, then a copy of this node will be 
	 * returned (children may or may not be included). If any descendants of
	 * the current node are targeted rows, then the immediate child of the
	 * current node leading to that targeted descendant is included as a child
	 * of the returned node. This proceeds recursively until the entire tree
	 * has been explored.
	 *
	 * @param TreeNode $node: The current node being examined within this hierarchy.
	 * @param array $rows: The list of target rows to be included in the MST.
	 *
	 * @return TreeNode: A treenode that forms the root of a branch of the final
	 *  MST. If the current node being examined in the hierarchy is one of the
	 *  targeted rows or any of it's children is a targeted row, then a TreeNode
	 *  object will be returned here. Otherwise, the entire subtree of this
	 *  hierarchy that is rooted at the current node is not a part of the final
	 *  MST and null is returned instead.
	 */
	private function getMSTHelper( TreeNode $node, array $rows ) {
		// make a copy of this node
		$clone = new TreeNode( $node->getValue() );

		// if any of the children are found in $rows then add them to the copy
		if (
			$node->getChildren() != null &&
			count( $node->getChildren() ) > 0
		) {
			foreach ( $node->getChildren() as $child ) {
				$subtree = $this->getMSTHelper( $child, $rows );
				if ( $subtree != null ) {
					$clone->addChild( $subtree );
				}
			}
		}

		// if children are in $rows or self is in rows then return the copy
		if (
			$clone->getChildren() != null ||
			in_array(
				HierarchyBuilder::getPageNameFromHierarchyRow( $node->getValue() ),
				$rows
			)
		) {
			return $clone;
		} else { // otherwise this whole branch gets cut
			return null;
		}
	}
}
