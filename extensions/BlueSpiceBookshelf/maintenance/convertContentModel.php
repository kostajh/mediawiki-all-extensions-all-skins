<?php

use MediaWiki\MediaWikiServices;

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

class ConvertContentModel extends LoggedUpdateMaintenance {

	/**
	 * @param IDatabase $db
	 * @return array
	 */
	private function getPages( $db ) {
		$res = $db->select(
			'page',
			[ 'page_id', 'page_namespace', 'page_title', 'page_content_model' ],
			[
				$db->makeList( [
					'page_namespace =' . NS_BOOK,
					'page_namespace =' . NS_USER
				], $db::LIST_OR ),
			]
		);

		$pages = [];
		foreach ( $res as $row ) {
			if ( $row->page_content_model === 'book' ) {
				// Nothing to do
				continue;
			}
			if ( (int)$row->page_namespace === NS_BOOK || $this->isUserBook( $row->page_title ) ) {
				$pages[(int)$row->page_id] = $row->page_title;
			}
		}

		return $pages;
	}

	/**
	 * @param array $pages
	 * @param IDatabase $db
	 * @return bool
	 */
	private function convert( $pages, IDatabase $db ) {
		if ( empty( $pages ) ) {
			return true;
		}

		return $db->update(
			'page',
			[
				'page_content_model' => 'book'
			],
			[
				'page_id IN (' . $db->makeList( array_keys( $pages ) ) . ')'
			],
			__METHOD__
		);
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	private function isUserBook( $title ) {
		$prefix = Message::newFromKey( 'bs-bookshelf-personal-books-page-prefix' )->plain();
		$pattern = str_replace( '$1', '.*', $prefix );
		$pattern = str_replace( '/', '\/', $pattern );
		$pattern = "/^$pattern.*$/";

		if ( preg_match( $pattern, $title ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function doDBUpdates() {
		$pages = $this->getPages(
			MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA )
		);
		return $this->convert(
			$pages,
			MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_MASTER )
		);
	}

	/**
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_bookmaker_convert_content_model';
	}
}

$maintClass = "ConvertContentModel";
require_once RUN_MAINTENANCE_IF_MAIN;
