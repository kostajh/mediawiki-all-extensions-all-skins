<?php

namespace BlueSpice\CountThings\Tag;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Tag\GenericHandler;
use BlueSpice\Tag\Tag;
use MediaWiki\MediaWikiServices;

class CountFiles extends Tag {

	const ATTR_NODUPLICATES = 'noduplicates';

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return \BlueSpice\CountThings\Tag\CountFilesHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, \Parser $parser,
		\PPFrame $frame ) {
		$loadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();
		return new CountFilesHandler(
			$loadBalancer,
			$processedArgs[ static::ATTR_NODUPLICATES ]
		);
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTagNames() {
		return [ 'bs:countfiles', 'countfiles' ];
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
	public function disableParserCache() {
		return true;
	}

	/**
	 *
	 * @return ParamDefinition[]
	 */
	public function getArgsDefinitions() {
		return [
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::ATTR_NODUPLICATES,
				true
			)
		];
	}
}
