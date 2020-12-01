<?php

/**
 * @brief Exception handling for the @ref Extensions-DataTable2.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup Extensions-DataTable2
 *
 * @author [RV1971](https://www.mediawiki.org/wiki/User:RV1971)
 *
 */

/**
 * @brief Exception class for the @ref Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 */
class DataTable2Exception extends MWException {
	/**
	 * @brief Constructor.
	 *
	 * @param string $message Message ID.
	 *
	 * @param ... Further parameters to wfMessage().
	 *
	 * @sa [MediaWiki Manual:Messages API]
	 * (https://www.mediawiki.org/wiki/Manual:Messages_API)
	 */
	public function __construct( $message ) {
		parent::__construct( wfMessage( $message,
				array_slice( func_get_args(), 1 ) )->text() );
	}

	/// Return formatted message as html.
	public function getHTML() {
		return wfMessage( 'datatable2-error', $this->getMessage() )->parse();
	}

	/// Return formatted message as static wikitext.
	public function getText() {
		return wfMessage( 'datatable2-error', $this->getMessage() )->text();
	}
}
