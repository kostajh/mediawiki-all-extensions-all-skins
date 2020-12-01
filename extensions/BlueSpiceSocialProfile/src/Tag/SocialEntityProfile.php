<?php

namespace BlueSpice\Social\Profile\Tag;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Social\Profile\Renderer\Entity\Profile as EntityRenderer;
use BlueSpice\Tag\MarkerType;
use BlueSpice\Tag\MarkerType\NoWiki;
use BlueSpice\Tag\Tag;
use Parser;
use PPFrame;

class SocialEntityProfile extends Tag {

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
				ParamType::STRING,
				'username',
				''
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
	 * @return SocialEntityProfileHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		return new SocialEntityProfileHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getTagNames() {
		return [
			'bs:socialentityprofile',
		];
	}

}
