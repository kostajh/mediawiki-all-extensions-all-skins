<?php

namespace BlueSpice\SignHere\Tag;

class SignHere {
	protected static $counter = 0;

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return string
	 */
	public function render( $input, $args, $parser, $frame ) {
		$parser->getOutput()->addModuleStyles( 'ext.blueSpice.signHere.styles' );
		$parser->getOutput()->addModules( 'ext.blueSpice.signHere' );

		$html = \Html::rawElement(
			'div',
			[
				'class' => 'bs-signhere',
				'title' => wfMessage( 'bs-signhere-tooltip' )->plain(),
				'data-bs-counter' => self::$counter++
			],
			\Html::element(
				'i',
				[
					'class' => 'bs-signhere-icon'
				]
			) . wfMessage( 'bs-signhere-signatures' )->escaped()
		);

		return $html;
	}
}
