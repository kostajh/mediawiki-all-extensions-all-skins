<?php

namespace BlueSpice\Social\Tag;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\Social\EntityListContext\Tag as TagListContext;
use BlueSpice\Tag\MarkerType;
use BlueSpice\Tag\MarkerType\NoWiki;
use FormatJson;
use MediaWiki\MediaWikiServices;
use MWException;
use Parser;
use PPFrame;
use RequestContext;

class Timeline extends \BlueSpice\Tag\Tag {

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
		return 'div';
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
	 * @return MarkerType
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
	 * @return TimelineHandler
	 * @throws MWException
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		$cfg = FormatJson::decode( $processedInput );

		if ( !empty( $cfg ) ) {
			$processedArgs = array_merge( $processedArgs, (array)$cfg );
		} elseif ( !empty( $processedInput ) ) {
			// TODO: make generic TagError handler!
			throw new MWException( 'Invalid JSON' );
		}

		$context = new TagListContext(
			RequestContext::getMain(),
			MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' )
		);

		return new TimelineHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame,
			$context
		);
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTagNames() {
		return [
			'bs:timeline',
			'bs:activitystream',
			'timeline'
		];
	}

}
