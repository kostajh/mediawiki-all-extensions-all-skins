<?php

namespace BlueSpice\Social\Tag;

use BlueSpice\Social\Entity;
use BlueSpice\Social\Renderer\Entity as EntityRenderer;
use BlueSpice\Tag\Handler;
use MediaWiki\MediaWikiServices;
use MWException;
use Parser;
use PPFrame;

class SocialEntityHandler extends Handler {
	/**
	 *
	 * @var Entity
	 */
	protected $entity = null;

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public function __construct( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$this->entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
			->newFromID( $processedArgs[Entity::ATTR_ID], NS_SOCIALENTITY );
		if ( !$this->entity instanceof Entity ) {
			new MWException(
				"Non existent or invalid entity for '" . Entity::ATTR_ID . "'"
			);
		}
	}

	/**
	 *
	 * @return string
	 */
	public function handle() {
		if ( !$this->entity->userCan( 'read', $this->parser->getUser() ) ) {
			return "";
		}
		return $this->entity->getRenderer()->render(
			$this->processedArgs[EntityRenderer::RENDER_TYPE]
		);
	}
}
