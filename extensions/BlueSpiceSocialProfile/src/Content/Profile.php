<?php
namespace BlueSpice\Social\Profile\Content;

use BlueSpice\Social\Profile\Entity\Profile as SocialProfile;
use MediaWiki\MediaWikiServices;

class Profile extends \WikitextContent {

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
	public function __construct( $text, $modelId = CONTENT_MODEL_BSSOCIALPROFILE ) {
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

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'ContentAlterParserOutput',
			[
				$this,
				$title,
				$po
			]
		);

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
	 * @param \ParserOutput &$output
	 * @param bool $bForceOrigin
	 * @return \ParserOutput $output
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
		$oUser = \User::newFromName( $title->getText() );
		if ( !$oUser ) {
			// something is very wrong here!
			return $output;
		}
		$entityFactory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileEntityFactory'
		);
		$entity = $entityFactory->newFromUser( $oUser );

		if ( !$entity instanceof SocialProfile ) {
			return $output;
		}
		// HACKY!
		// this got removed.. for now
		if ( false && !$entity->exists() && PHP_SAPI !== 'cli' ) {
			$oStatus = $entity->save();
			if ( !$oStatus->isOK() ) {
				$output->setText( $oStatus->getHTML() );
				return $output;
			}
		}

		$sText = $entity->getRenderer()->render( 'Page' );
		$sTitle = strip_tags( $entity->getHeader() );
		$output->setTitleText( $sTitle );
		if ( $generateHtml ) {
			$output->setText( $sText );
			$output->addModuleStyles( 'mediawiki.content.json' );
		} else {
			$output->setText( $sText );
		}
		return $output;
	}
}
