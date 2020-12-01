<?php
namespace BlueSpice\Bookshelf\MassAdd\Handler;

class PageCollection implements \BlueSpice\Bookshelf\MassAdd\IHandler {
	/**
	 * Name of page collection
	 * without prefix
	 *
	 * @var string
	 */
	protected $root;

	/**
	 *
	 * @return array
	 */
	public function getData() {
		$pageCollectionPrefix = wfMessage( 'bs-pagecollection-prefix' )->plain();
		$pageCollectionPrefix .= "/";
		$pageCollectionTitle = \Title::makeTitle( NS_MEDIAWIKI, $pageCollectionPrefix . $this->root );

		if ( $pageCollectionTitle->exists() === false ) {
			return [];
		}

		$pageCollectionWikiPage = \WikiPage::newFromID( $pageCollectionTitle->getArticleID() );
		$pageCollectionContent = $pageCollectionWikiPage->getContent();
		$pageCollectionText = \ContentHandler::getContentText( $pageCollectionContent );

		if ( trim( $pageCollectionText ) == '' ) {
			return [];
		}

		$pageRes = [];
		$pages = explode( "\n", $pageCollectionText );
		foreach ( $pages as $page ) {
			$page = trim( trim( $page, '*' ) );

			$pageDisplayText = '';
			// Parse internal links
			if ( strpos( $page, '[[' ) !== false ) {
				$page = substr( $page, 2, -2 );
				$linkPieces = explode( '|', $page );
				if ( count( $linkPieces ) == 2 ) {
					$page = $linkPieces[0];
					$pageDisplayText = $linkPieces[1];
				}
			}
			$title = \Title::newFromText( $page );
			if ( !( $title instanceof \Title ) ) {
				continue;
			}
			if ( !$pageDisplayText ) {
				$pageDisplayText = $title->getPrefixedText();
			}
			$pageRes[] = [
				'page_id' => $title->getArticleId(),
				'page_title' => $title->getText(),
				'page_namespace' => $title->getNamespace(),
				'prefixed_text' => $pageDisplayText
			];
		}

		return $pageRes;
	}

	/**
	 * Returns an instance of this handler
	 *
	 * @param string $root Name of the collection without prefix
	 * @return \self
	 */
	public static function factory( $root ) {
		return new self( $root );
	}

	/**
	 *
	 * @param string $root
	 */
	protected function __construct( $root ) {
		$this->root = $root;
	}

}
