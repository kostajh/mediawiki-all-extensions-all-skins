<?php

namespace BlueSpice\Bookshelf\LineProcessor;

use BlueSpice\Bookshelf\ILineProcessor;
use BlueSpice\Bookshelf\TreeNode;
use Content;
use Title;
use WikiPage;

class WikiTextLink extends LineProcessorBase implements ILineProcessor {
	/**
	 * @var Title
	 */
	protected $title;

	/**
	 * @var TreeNode
	 */
	protected $result;

	/**
	 * Process the given line
	 *
	 * @param string $line
	 * @return TreeNode
	 */
	public function process( $line ) {
		$this->result = new TreeNode();

		$link = $this->getLink( $line );
		$this->parseLink( $link );
		$this->parseTitle();
		if ( !$this->title instanceof Title ) {
			$this->result['type'] = 'plain-text';
			return $this->result;
		}
		$this->handleRedirect();

		// Normalizing in case of ($this->result['title'] === ':Some colon prefixed title')
		$this->result['title'] = $this->title->getPrefixedText();
		$this->result['article-id']  = $this->title->getArticleID();

		$this->result['bookshelf']['type'] = 'wikipage';
		$this->result['bookshelf']['page_id'] = $this->title->getArticleID();
		$this->result['bookshelf']['page_namespace'] = $this->title->getNamespace();
		$this->result['bookshelf']['page_title'] = $this->title->getText();

		return $this->result;
	}

	/**
	 * @param string $line
	 * @return bool
	 */
	public function applies( $line ) {
		return $this->getLink( $line ) !== false;
	}

	/**
	 *
	 * @param type $line
	 * @return bool
	 */
	protected function getLink( $line ) {
		$matches = [];
		$found = preg_match(
			'#^.*?\[\[(.*?)\]\].*?$#',
			$line,
			$matches
		);
		if ( $found ) {
			return trim( $matches[1] );
		}
		return false;
	}

	/**
	 *
	 * @param string $link
	 */
	protected function parseLink( $link ) {
		$linkHasDisplayTitle = strpos( $link, '|' );
		if ( $linkHasDisplayTitle !== false ) {
			$linkParts = explode( '|', $link );
			$this->result['type']          = 'wikilink-with-alias';
			$this->result['title']         = str_replace( '_', ' ', trim( $linkParts[0] ) );
			$this->result['display-title'] = trim( $linkParts[1] );
		} else {
			$this->result['type']          = 'wikilink';
			$this->result['title']         = str_replace( '_', ' ', $link );
			$this->result['display-title'] = $this->result['title'];
		}
	}

	protected function parseTitle() {
		$this->title = Title::newFromText( $this->result['title'] );
	}

	/**
	 *
	 */
	protected function handleRedirect() {
		$this->result['is-redirect'] = $this->title->isRedirect();
		if ( $this->title->isRedirect() ) {
			$wikiPage = WikiPage::factory( $this->title );
			$content = $wikiPage->getContent();
			if ( $content instanceof Content ) {
				$tagetTitle = $content->getUltimateRedirectTarget();
				if ( $tagetTitle instanceof Title ) {
					$this->result['redirected-from'] = $this->title->getPrefixedText();
					$this->title = $tagetTitle;
				}
			}
		}
	}
}
