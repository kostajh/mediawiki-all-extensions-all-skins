<?php

namespace BlueSpice\Bookshelf;

use BSTreeNode;
use PageHierarchyProvider;
use SkinTemplate;

class SidebarTreeNavigation extends \BSSkinTreeNavigation {

	/**
	 *
	 * @var SkinTemplate
	 */
	protected $skinTemplate = null;

	/**
	 *
	 * @param SkinTemplate $template
	 * @param \DOMElement|null $domElement
	 * @param int $indent
	 */
	public function __construct( $template, \DOMElement $domElement = null, $indent = 0 ) {
		$this->skinTemplate = $template;
	}

	/**
	 *
	 * @return SkinTemplate
	 */
	public function getSkinTemplate() {
		return $this->skinTemplate;
	}

	/**
	 *
	 * @var \PageHierarchyProvider
	 */
	protected $phProvider = null;

	/**
	 *
	 * @var string
	 */
	protected $rootNodeId = '';

	/**
	 *
	 * @return string
	 */
	protected function getContainerID() {
		return 'bs-bookshelfui-book-toc';
	}

	/**
	 *
	 * @return BSTreeNode
	 */
	protected function makeTreeRootNode() {
		$tree = $this->phProvider->getExtendedTOCJSON();

		$rootNode = new BSTreeNode( $tree->text, null, new \HashConfig( [
			BSTreeNode::CONFIG_EXPANDED => true,
			BSTreeNode::CONFIG_IS_LEAF => false,
			BSTreeNode::CONFIG_TEXT => $tree->articleDisplayTitle,
		] ) );
		$this->rootNodeId = $rootNode->getId();
		$this->addChildsToNode( $rootNode, $tree->children );

		return $rootNode;
	}

	/**
	 *
	 * @return array
	 */
	protected function getPathsToExpand() {
		$number = $this->phProvider->getNumberFor(
			$this->skinTemplate->getSkin()->getTitle()->getPrefixedText()
		);

		$path = $this->makeExpandPath( $number );

		return [ $path ];
	}

	/**
	 *
	 * @return string
	 */
	public function getHtml() {
		try {
			$this->phProvider = PageHierarchyProvider::getInstanceForArticle(
				$this->skinTemplate->getSkin()->getTitle()->getPrefixedText()
			);
		} catch ( \Exception $ex ) {
			return '';
		}

		$treeHtml = parent::getHtml();

		$tree = $this->phProvider->getExtendedTOCJSON();
		$bookTitle = $tree->bookshelf->page_title;
		$bookMeta = $this->phProvider->getBookMeta();
		if ( isset( $bookMeta['title'] ) ) {
			$bookTitle = $bookMeta['title'];
		}

		$bookEditorTitle = \Title::makeTitleSafe(
			$tree->bookshelf->page_namespace,
			$tree->bookshelf->page_title
		);

		$bookEditorLink = \Html::openElement(
			'a',
			[
				'class' => 'bs-link-edit-bookshelfui-book',
				'href' => $bookEditorTitle->getFullURL( [ 'action' => 'edit' ] ),
				'title' => wfMessage( 'bs-bookshelfui-book-title-link-edit' )->plain()
			]
		);

		$bookEditorLink .= \Html::element(
			'span',
			[
				'class' => 'label'
			],
			wfMessage( 'bs-bookshelfui-book-title-link-edit-text' )->plain()
		);

		$bookEditorLink .= \Html::closeElement( 'a' );

		$headingHtml = \Html::element(
			'h5',
			[],
			$bookTitle
		);

		return '<div class="bs-bookshelfui-book">'
			. $headingHtml
			. '</div>'
			. $treeHtml
			. $bookEditorLink;
	}

	/**
	 *
	 * @param BSTreeNode $node
	 * @param array $childs
	 */
	protected function addChildsToNode( $node, $childs ) {
		foreach ( $childs as $child ) {
			$childNode = new BSTreeNode( $child->id, $node, new \HashConfig( [
				BSTreeNode::CONFIG_EXPANDED => false,
				BSTreeNode::CONFIG_IS_LEAF => false,
				BSTreeNode::CONFIG_TEXT => $this->makeNodeText( $child )
			] ) );

			$node->appendChild( $childNode );

			if ( isset( $child->children ) && !empty( $child->children ) ) {
				$this->addChildsToNode( $childNode,  $child->children );
			}
		}
	}

	/**
	 *
	 * @param BSTreeNode $node
	 * @return string
	 */
	protected function makeNodeText( $node ) {
		if ( $node->articleType === 'plain-text' ) {
			return $this->makePlainTextNodeText( $node );
		}
		if ( $node->articleType === 'wikilink-with-alias' ) {
			return $this->makeWikiPageNodeText( $node );
		}
		if ( $node->articleType === 'wikilink' ) {
			return $this->makeWikiPageNodeText( $node );
		}
	}

	/**
	 *
	 * @param BSTreeNode $node
	 * @return string
	 */
	protected function makePlainTextNodeText( $node ) {
		return \Html::element(
			'span',
			[
				'level' => $node->articleNumber,
				'name' => $node->articleNumber,
				'title' => $node->text
			],
			$node->text
		);
	}

	/**
	 *
	 * @param BSTreeNode $node
	 * @return string
	 */
	protected function makeWikiPageNodeText( $node ) {
		$currentTitle = $this->getSkinTemplate()->getSkin();
		$target = \Title::newFromText( $node->articleTitle );

		$num = '<span class="bs-articleNumber">' . $node->articleNumber . '.</span>';
		$title = '<span class="bs-articleText">'
			. str_replace( $node->articleNumber
			. '. ', '', $node->text )
		. '</span>';

		$attribs = [
			'name' => $node->articleNumber,
			'title' => $node->text
		];

		if ( $currentTitle->getTitle()->equals( $target ) ) {
			$attribs['class'] = 'active';
		}

		return \Linker::link( $target, $num . $title, $attribs );
	}

	/**
	 *
	 * @param string $number
	 * @return string
	 */
	protected function makeExpandPath( $number ) {
		$numberParts = explode( '.', $number );
		$path = [ \Sanitizer::escapeId( $number ) ];
		$count = count( $numberParts );
		for ( $i = 0; $i <= $count; $i++ ) {
			array_pop( $numberParts );
			$id = implode( '.', $numberParts );
			if ( empty( $id ) ) {
				continue;
			}
			$path[] = \Sanitizer::escapeId( $id );
		}
		$path[] = \Sanitizer::escapeId( $this->rootNodeId );
		$path[] = '';

		return implode( '/', array_reverse( $path ) );
	}
}
