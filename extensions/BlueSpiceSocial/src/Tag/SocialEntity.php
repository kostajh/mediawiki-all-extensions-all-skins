<?php

namespace BlueSpice\Social\Tag;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Social\Entity;
use BlueSpice\Social\Renderer\Entity as EntityRenderer;
use BlueSpice\Tag\MarkerType;
use BlueSpice\Tag\MarkerType\NoWiki;
use Parser;
use PPFrame;

class SocialEntity extends \BlueSpice\Tag\Tag {

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
		return [
			new ParamDefinition(
				ParamType::INTEGER,
				Entity::ATTR_ID,
				0
			),
			new ParamDefinition(
				ParamType::STRING,
				EntityRenderer::RENDER_TYPE,
				EntityRenderer::RENDER_TYPE_SHORT
			),
		];
	}

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return SocialEntityHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		return new SocialEntityHandler(
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
			'bs:socialentity',
		];
	}

}
