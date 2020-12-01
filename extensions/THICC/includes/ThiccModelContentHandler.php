<?php

/**
 * Content handler for ThiccModelContent.
 *
 * We extend TextContentHandler instead of JsonContentHandler since
 * we do not display this as JSON code except upon request.
 *
 * @file
 */

class ThiccModelContentHandler extends TextContentHandler {

	public function __construct(
		$modelId = 'ThiccModelContent',
		$formats = [ CONTENT_FORMAT_JSON ]
	) {
		parent::__construct( $modelId, $formats );
	}

	/**
	 * @return ThiccModelContent
	 */
	public function makeEmptyContent() {
		$empty = <<<JSON
			{
				"display_name": "",
				"metadata": [],
				"comment": {
					"content": "",
					"metadata": {
						"author": "",
						"timestamp": ""
					},
					"children": []
				}
			}
JSON;
		return new ThiccModelContent( $empty );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return 'ThiccModelContent';
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
	 * Turns ThiccModelContent page into redirect
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
