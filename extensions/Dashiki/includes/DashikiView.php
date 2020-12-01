<?php

namespace Dashiki;

use Html;
use JsonConfig\JCContent;
use JsonConfig\JCDefaultContentView;
use MediaWiki\Shell\Shell;
use ParserOptions;
use ParserOutput;
use Title;

/**
 * Used to render Dashiki JSON configuration pages to HTML
 * @package Dashiki
 */
class DashikiView extends JCDefaultContentView {

	/**
	 * Customizes valueToHtml() for Dashiki
	 *
	 * @param JCContent $content
	 * @param Title $title Context title for parsing
	 * @param int|null $revId Revision ID (for {{REVISIONID}})
	 * @param ParserOptions $options Parser options
	 * @param bool $generateHtml Whether or not to generate HTML
	 * @param ParserOutput &$output The output object to fill (reference).
	 * @return string
	 */
	public function valueToHtml(
		JCContent $content, Title $title, $revId, ParserOptions $options, $generateHtml,
		ParserOutput &$output
	) {
		$header = $this->renderHeader( $title->getDBKey() );
		$tableDisplay = parent::valueToHtml(
			$content, $title, $revId, $options, $generateHtml, $output
		);
		return $header . $tableDisplay;
	}

	/**
	 * @param string $dbkey
	 * @return string HTML
	 */
	public function renderHeader( $dbkey ) {
		$docsLink = Html::element( 'a', [
			'href' => 'https://wikitech.wikimedia.org/wiki/Analytics/Systems/Dashiki/Configuration'
		], wfMessage( 'parentheses', wfMessage( 'dashiki-configuration-doc-link-text' ) )->text() );

		if ( $this->startsWith( $dbkey, 'Dashiki:Annotations/' ) ) {
			$message = wfMessage( 'dashiki-annotate' )->text();
			$br = Html::element( 'br' );
			$suffix = $br . $br;
		} else {
			$message = wfMessage( 'dashiki-build' )->text();
			$suffix = Html::element( 'pre', [],
				'gulp --config ' . Shell::escape( $dbkey ) . ' --layout /*...*/' );
		}

		$span = Html::element( 'span', [], $message . ' ' );

		return $span . $docsLink . $suffix;
	}

	/**
	 * @param string $text
	 * @param string $query
	 *
	 * @return bool
	 */
	private function startsWith( $text, $query ) {
		$length = strlen( $query );
		return ( substr( $text, 0, $length ) === $query );
	}
}
