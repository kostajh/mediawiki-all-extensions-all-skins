<?php

/**
 * Exports messages to their native format with embedded textarea.
 */
class ExportSVGMessagesTask extends ExportMessagesTask {
	protected $id = 'export-as-svg';

	/**
	 * Override
	 */
	protected function preinit() {
	}

	/**
	 * No paging should be done
	 */
	protected function doPaging() {
	}

	/**
	 * Override
	 */
	protected function postinit() {
	}

	public function output() {
		if ( !$this->group instanceof SVGMessageGroup ) {
			$link = Html::element(
				'a',
				[ 'href' => 'https://phabricator.wikimedia.org/maniphest/task/create/?projects=TranslateSVG' ],
				'phabricator.wikimedia.org'
			);
			return $this->errorOutput( wfMessage( 'translate-svg-export-unsupported', $link ) );
		}

		$writer = new SVGFormatWriter( $this->group );
		$ret = $writer->exportToSVG( $this->context->getUser() );
		if ( $ret === true ) {
			global $wgOut;
			$wgOut->redirect( $this->group->getUrl() );
			return true;
		} else {
			// Error output
			return $this->errorOutput( $ret );
		}
	}

	/**
	 * Takes an error message and wraps it nicely, with a header
	 *
	 * @param string $content Error message, as string
	 * @return string
	 */
	protected function errorOutput( $content ) {
		$output = '<h2>' . wfMessage( 'uploadwarning' )->plain() . "</h2>\n" .
			'<div class="error">' . $content . "</div>\n";
		return $output;
	}
}
