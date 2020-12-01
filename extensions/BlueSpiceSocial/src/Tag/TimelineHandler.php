<?php

namespace BlueSpice\Social\Tag;

use BlueSpice\Renderer\Params;
use BlueSpice\Social\EntityListContext;
use BlueSpice\Tag\Handler;
use MediaWiki\MediaWikiServices;
use Parser;
use PPFrame;

class TimelineHandler extends Handler {
	/**
	 *
	 * @var EntityListContext
	 */
	protected $context = null;

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param EntityListContext $context
	 */
	public function __construct( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame, EntityListContext $context ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$this->context = $context;
	}

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$params = array_merge(
			$this->processedArgs,
			[ 'context' => $this->context ]
		);
		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'entitylist',
			new Params( $params )
		);
		return $renderer->render();
	}
}
