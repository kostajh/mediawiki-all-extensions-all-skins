<?php
namespace BlueSpice\Social\Topics\Content;

use MediaWiki\MediaWikiServices;

class Discussion extends \WikitextContent {

	/**
	 *
	 * @var string
	 */
	public $mModelID = CONTENT_MODEL_WIKITEXT;

	/**
	 *
	 * @return string
	 */
	public function getModel() {
		return CONTENT_MODEL_WIKITEXT;
	}

	/**
	 *
	 * @param string $text
	 * @param string $modelId
	 */
	public function __construct( $text, $modelId = CONTENT_MODEL_BSSOCIALDISCUSSION ) {
		parent::__construct( $text, CONTENT_MODEL_WIKITEXT );
	}

	/**
	 * Returns a ParserOutput object containing information derived from this content.
	 * Most importantly, unless $generateHtml was false, the return value contains an
	 * HTML representation of the content.
	 *
	 * Subclasses that want to control the parser output may override this, but it is
	 * preferred to override fillParserOutput() instead.
	 *
	 * Subclasses that override getParserOutput() itself should take care to call the
	 * ContentGetParserOutput hook.
	 *
	 * @since 1.24
	 *
	 * @param \Title $title Context title for parsing
	 * @param int|null $revId Revision ID (for {{REVISIONID}})
	 * @param \ParserOptions|null $options Parser options
	 * @param bool $generateHtml Whether or not to generate HTML
	 * @param bool $bForceOrigin
	 *
	 * @return ParserOutput Containing information derived from this content.
	 */
	public function getParserOutput( \Title $title, $revId = null, \ParserOptions $options = null,
		$generateHtml = true, $bForceOrigin = false ) {
		if ( $options === null ) {
			$options = \ParserOptions::newCanonical( 'canonical' );
		}

		$po = new \ParserOutput();

		if ( MediaWikiServices::getInstance()->getHookContainer()->run( 'ContentGetParserOutput',
			[ $this, $title, $revId, $options, $generateHtml, &$po ] ) ) {

			// Save and restore the old value, just in case something is reusing
			// the ParserOptions object in some weird way.
			$oldRedir = $options->getRedirectTarget();
			$options->setRedirectTarget( $this->getRedirectTarget() );
			$this->fillParserOutput( $title, $revId, $options, $generateHtml, $po, $bForceOrigin );
			$options->setRedirectTarget( $oldRedir );
		}

		MediaWikiServices::getInstance()->getHookContainer()->run( 'ContentAlterParserOutput', [
			$this,
			$title,
			$po
		] );

		return $po;
	}

	/**
	 * Set the HTML and add the appropriate styles
	 *
	 *
	 * @param \Title $title
	 * @param int $revId
	 * @param \ParserOptions $options
	 * @param bool $generateHtml
	 * @param ParserOutput &$output
	 * @param bool $bForceOrigin
	 * @return \ParserOutput
	 */
	protected function fillParserOutput( \Title $title, $revId, \ParserOptions $options,
		$generateHtml, \ParserOutput &$output, $bForceOrigin = false ) {
		parent::fillParserOutput(
			$title,
			$revId,
			$options,
			$generateHtml,
			$output
		);
		if ( $bForceOrigin ) {
			return $output;
		}
		if ( !$title ) {
			return $output;
		}
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialDiscussionEntityFactory'
		);
		$entity = $factory->newFromDiscussionTitle( $title );
		if ( !$entity ) {
			return $output;
		}

		$output->setTitleText(
			strip_tags( $entity->getHeader()->parse() )
		);
		$output->setText( $entity->getRenderer()->render( 'Page' ) );
		return $output;
	}
}
