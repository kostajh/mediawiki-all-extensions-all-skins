<?php

/**
 * Content handler for ThiccModelAggregateContent.
 *
 * ...this'll do something later.
 *
 * @file
 */

class ThiccModelAggregateContentHandler extends JsonContentHandler {

	public function __construct(
		$modelId = 'ThiccModelAggregateContent',
		$formats = [ CONTENT_FORMAT_JSON ]
	) {
		parent::__construct( $modelId, $formats );
	}

	/**
	 * @return ThiccModelAggregateContent
	 */
	public function makeEmptyContent() {
		$empty = <<<JSON
			{
				"metadata": [],
				"introduction": "",
				"thiccness": []
			}
JSON;
		return new ThiccModelAggregateContent( $empty );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return 'ThiccModelAggregateContent';
	}

	/**
	 * @return bool
	 */
	public function isParserCacheSupported() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function supportsRedirects() {
		return true;
	}

	/**
	 * Turns ThiccModelAggregateContent page into redirect
	 *
	 * Note that wikitext redirects are created, as generally, this content model
	 * is used in namespaces that support wikitext, and wikitext redirects are
	 * expected.
	 *
	 * @param Title $destination The page to redirect to
	 * @param string $text Text to include in the redirect.
	 * @return Content
	 */
	public function makeRedirectContent( Title $destination, $text = '' ) {
		$handler = new WikitextContentHandler();
		return $handler->makeRedirectContent( $destination, $text );
	}
}
