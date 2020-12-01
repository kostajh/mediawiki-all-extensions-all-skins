<?php

namespace BlueSpice\PagesVisited\Tag;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Tag\GenericHandler;
use BlueSpice\Tag\MarkerType\NoWiki;
use Parser;
use PPFrame;

class PagesVisited extends \BlueSpice\Tag\Tag {
	const PARAM_COUNT = 'count';
	const PARAM_MAX_TITLE_LENGTH = 'maxtitlelength';
	const PARAM_NAMESPACES = 'namespaces';
	const PARAM_ORDER = 'order';

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
		return GenericHandler::TAG_DIV;
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
		$namespaces = new \BSNamespaceListParam(
			ParamType::NAMESPACE_LIST,
			static::PARAM_NAMESPACES,
			[],
			null,
			true
		);
		$namespaces->setDelimiter( ',' );
		return [
			new ParamDefinition(
				ParamType::INTEGER,
				static::PARAM_COUNT,
				5
			),
			new ParamDefinition(
				ParamType::INTEGER,
				static::PARAM_MAX_TITLE_LENGTH,
				20
			),
			$namespaces,
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_ORDER,
				'time'
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
		return new PagesVisitedHandler(
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
			'pagesvisited',
			'bs:pagesvisited',
		];
	}

}
