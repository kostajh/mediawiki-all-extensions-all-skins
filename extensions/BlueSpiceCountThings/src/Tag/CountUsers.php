<?php

namespace BlueSpice\CountThings\Tag;

class CountUsers extends \BlueSpice\Tag\Tag {

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
	 * @return CountUsersHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, \Parser $parser,
		\PPFrame $frame ) {
		return new CountUsersHandler(
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
			'bs:countusers'
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
