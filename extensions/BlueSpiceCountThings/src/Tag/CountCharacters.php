<?php

namespace BlueSpice\CountThings\Tag;

class CountCharacters extends \BlueSpice\Tag\Tag {

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
	 * @return CountCharactersHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, \Parser $parser,
		\PPFrame $frame ) {
		return new CountCharactersHandler(
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
			'bs:countcharacters'
		];
	}
}
