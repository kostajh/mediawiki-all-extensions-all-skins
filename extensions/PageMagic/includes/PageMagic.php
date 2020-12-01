<?php

namespace MediaWiki\Extension\PageMagic;

use MediaWiki\MediaWikiServices;
use Parser;
use Title;

class PageMagic {

	/**
	 * Returns a prefixed page name matching the page id provided
	 *
	 * @param Parser $parser
	 * @param null $pageId
	 *
	 * @return string
	 */
	public static function fullpagenamefromid( Parser $parser, $pageId = null ) {
		if ( $pageId === null ) {
			// Blank if no ID is provided
			return '';
		}
		$title = Title::newFromID( (int)$pageId );
		if ( !$title || !$title->exists() ) {
			// Blank if the subject page does not exist
			return '';
		}
		return $title->getPrefixedText();
	}

	/**
	 * Returns page id matching provided revision id
	 *
	 * @param Parser $parser
	 * @param null $revId
	 *
	 * @return int
	 */
	public static function pageidfromrevisionid( Parser $parser, $revId = null ) {
		if ( $revId === null ) {
			// Blank if no ID is provided
			return '';
		}
		$revision = MediaWikiServices::getInstance()->getRevisionStore()->getRevisionById( (int)$revId );
		if ( !$revision ) {
			// Blank if the revision can't be found
			return '';
		}
		return $revision->getPageId();
	}

	/**
	 * Returns prefixed page name matching provided revision id
	 *
	 * @param Parser $parser
	 * @param null $revId
	 *
	 * @return string
	 */
	public static function fullpagenamefromrevisionid( Parser $parser, $revId = null ) {
		if ( $revId === null ) {
			// Blank if no ID is provided
			return '';
		}
		$revision = MediaWikiServices::getInstance()->getRevisionStore()->getRevisionById( (int)$revId );
		if ( !$revision ) {
			// Blank if the revision can't be found
			return '';
		}
		$title = Title::newFromID( $revision->getPageId() );
		if ( !$title || !$title->exists() ) {
			// Blank if the subject page does not exist
			return '';
		}
		return $title->getPrefixedText();
	}

}
