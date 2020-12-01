<?php

namespace NotesLink\Hook\ParserFirstCallInit;

class AddNDLParserHook extends \NotesLink\Hook\ParserFirstCallInit {

	protected function doProcess() {
		$this->parser->setHook( 'ndl', [ $this, 'onTagNDL' ] );
		$this->parser->setHook( 'NDL', [ $this, 'onTagNDL' ] );
	}

	/**
	 *
	 * @param string $input
	 * @return string
	 */
	public function onTagNDL( $input ) {
		$factory = $this->getServices()->getService( 'NotesLinkNDLFactory' );
		$parser = $factory->newParserFromInput( $input );
		$renderer = $factory->getLinkRenderer( $parser );
		return [
			$renderer->render(),
			'markerType' => 'nowiki',
		];
	}
}
