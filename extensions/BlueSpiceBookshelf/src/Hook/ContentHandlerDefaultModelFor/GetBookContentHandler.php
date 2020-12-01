<?php

namespace BlueSpice\Bookshelf\Hook\ContentHandlerDefaultModelFor;

use Message;
use Title;

class GetBookContentHandler {
	/**
	 * @param Title $title
	 * @param string &$model
	 * @return bool
	 */
	public static function callback( Title $title, &$model ) {
		// By namespace
		if ( $title->getNamespace() === NS_BOOK ) {
			$model = 'book';
			return false;
		}
		// By naming pattern - user books
		if ( $title->getNamespace() !== NS_USER ) {
			return true;
		}

		$prefix = Message::newFromKey( 'bs-bookshelf-personal-books-page-prefix' )->plain();
		$pattern = str_replace( '$1', '.*', $prefix );
		$pattern = str_replace( '/', '\/', $pattern );
		$pattern = "/^$pattern.*$/";

		if ( preg_match( $pattern, $title->getText() ) ) {
			$model = 'book';
			return false;
		}

		return true;
	}
}
