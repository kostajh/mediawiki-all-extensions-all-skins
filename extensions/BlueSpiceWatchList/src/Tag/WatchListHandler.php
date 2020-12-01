<?php

namespace BlueSpice\WatchList\Tag;

use BlueSpice\Tag\Handler;
use BsStringHelper;
use Title;

class WatchListHandler extends Handler {

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setProperty( 'bs-tag-watchlist', 1 );
		$list = '';
		if ( $this->parser->getUser()->isAnon() ) {
			return $list;
		}
		foreach ( $this->getWatchlistTitles() as $title ) {
			$displayText = BsStringHelper::shorten( $title->getPrefixedText(), [
				'max-length' => $this->processedArgs[WatchList::ATTR_MAX_TITLE_LENGTH],
				'position' => 'middle'
			] );
			$list .= "* [[{$title->getPrefixedText()}|$displayText]]\n";
		}
		return $this->parser->recursiveTagParseFully( $list );
	}

	/**
	 *
	 * @return Title[]
	 */
	protected function getWatchlistTitles() {
		$watchlist = [];

		$options = [];
		if ( $this->processedArgs[WatchList::ATTR_ORDER] === WatchList::ATTR_ORDER_PAGE_NAME ) {
			$options['ORDER BY'] = 'wl_title';
		}
		$options['LIMIT'] = $this->processedArgs[WatchList::ATTR_COUNT];

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			[ 'watchlist', 'page' ],
			[ 'wl_namespace', 'wl_title' ],
			[
				'wl_user' => $this->parser->getUser()->getId(),
				'NOT wl_notificationtimestamp' => null,
				'wl_title = page_title',
				'wl_namespace = page_namespace',
				"page_content_model" => [ "", "wikitext" ]
			],
			__METHOD__,
			$options
		);

		foreach ( $res as $row ) {
			$watchedTitle = Title::newFromText( $row->wl_title, $row->wl_namespace );
			if ( !$watchedTitle || !$watchedTitle->exists() ) {
				continue;
			}

			$watchlist[] = $watchedTitle;
		}

		return $watchlist;
	}
}
