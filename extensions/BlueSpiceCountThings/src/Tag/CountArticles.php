<?php

namespace BlueSpice\CountThings\Tag;

class CountArticles extends \BlueSpice\Tag\Tag {

	/**
	 *
	 * @return bool
	 */
	public function needsDisabledParserCache() {
		return true;
	}

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return CountArticlesHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, \Parser $parser,
		\PPFrame $frame ) {
		return new CountArticlesHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTagNames() {
		return [
			'bs:countarticles'
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getContainerElementName() {
		return 'span';
	}
}
