<?php

namespace BlueSpice\CountThings\Tag;

use BlueSpice\Tag\Handler;

class CountArticlesHandler extends Handler {

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 */
	public function __construct( $processedInput, array $processedArgs, \Parser $parser,
		\PPFrame $frame ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
	}

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$count = \CoreParserFunctions::numberofarticles( $this->parser );
		return " $count ";
	}
}
