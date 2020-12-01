<?php

namespace BlueSpice\Avatars\Tag;

use BlueSpice\Tag\MarkerType;
use BlueSpice\Tag\MarkerType\NoWiki;
use MediaWiki\MediaWikiServices;
use Parser;
use PPFrame;

class ProfileImage extends \BlueSpice\Tag\Tag {

	/**
	 *
	 * @return bool
	 */
	public function needsDisabledParserCache() {
		return false;
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
	 * @return array
	 */
	public function getArgsDefinitions() {
		return [];
	}

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return ProfileImageHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		return new ProfileImageHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame,
			MediaWikiServices::getInstance()->getService( 'BSDynamicFileDispatcherUrlBuilder' ),
			MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )
		);
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTagNames() {
		return [
			'bs:profileimage',
			'profileimage'
		];
	}

}
