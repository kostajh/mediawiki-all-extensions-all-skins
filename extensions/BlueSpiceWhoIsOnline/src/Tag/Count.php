<?php

namespace BlueSpice\WhoIsOnline\Tag;

use BlueSpice\Tag\GenericHandler;
use BlueSpice\Tag\MarkerType\NoWiki;
use Parser;
use PPFrame;

class Count extends \BlueSpice\Tag\Tag {

	/**
	 *
	 * @return bool
	 */
	public function needsDisabledParserCache() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getContainerElementName() {
		return GenericHandler::TAG_SPAN;
	}

	/**
	 *
	 * @return bool
	 */
	public function needsParsedInput() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	public function needsParseArgs() {
		return true;
	}

	/**
	 *
	 * @return NoWiki
	 */
	public function getMarkerType() {
		return new NoWiki();
	}

	/**
	 *
	 * @return null
	 */
	public function getInputDefinition() {
		return null;
	}

	/**
	 *
	 * @return ParamDefinition[]
	 */
	public function getArgsDefinitions() {
		return [];
	}

	/**
	 *
	 * @param type $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return CountHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		return new CountHandler(
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
			'userscount',
			'bs:whoisonline:count',
			'bs:whoisonlinecount',
		];
	}

}
