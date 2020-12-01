<?php

namespace BlueSpice\TagCloud\Tag;

use BlueSpice\Tag\MarkerType\NoWiki;

class TagCloud extends \BlueSpice\Tag\Tag {

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
	 * @return array
	 */
	public function getArgsDefinitions() {
		return [];
	}

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return TagCloudHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, \Parser $parser,
		\PPFrame $frame ) {
		return new TagCloudHandler(
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
			'bs:tagcloud',
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getResourceLoaderModuleStyles(): array {
		return [
			'ext.bluespice.tagcloud.text.styles'
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getResourceLoaderModules(): array {
		return [
			'ext.bluespice.tagcloud.canvas3d'
		];
	}

}
