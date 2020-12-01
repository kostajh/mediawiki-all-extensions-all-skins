<?php

namespace BlueSpice\WhoIsOnline\Tag;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Tag\GenericHandler;
use BlueSpice\Tag\MarkerType\NoWiki;
use Parser;
use PPFrame;

class PopUp extends \BlueSpice\Tag\Tag {
	const PARAM_ANCHOR_TEXT = 'anchortext';

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
		return [
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_ANCHOR_TEXT,
				''
			),
		];
	}

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return PopUpHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		return new PopUpHandler(
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
			'userslink',
			'bs:whoisonline:popup',
			'bs:whoisonlinepopup',
		];
	}

}
