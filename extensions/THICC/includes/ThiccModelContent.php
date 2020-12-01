<?php

use MediaWiki\MediaWikiServices;

/**
 * Threads for our thicckener
 */
class ThiccModelContent extends JsonContent {

	/** @var bool Whether contents have been populated */
	protected $decoded = false;

	/** @var string */
	protected $displayName;

	/** @var array */
	protected $metadata;

	/** @var array */
	protected $comment;

	/** @var string How to display contents */
	protected $displaymode;

	/** @var string Error message text */
	protected $errortext;

	protected $parser;

	/**
	 * @param string $text
	 */
	public function __construct( $text ) {
		parent::__construct( $text, 'ThiccModelContent' );
	}

	/**
	 * Decode and validate the contents
	 * @return bool Whether the contents are valid
	 */
	public function isValid() {
		$jsonParse = $this->getData();
		if ( $jsonParse->isGood() ) {
			// TODO: Check schema, etc
			return true;
		}
		return false;
	}

	/**
	 * Decode the JSON contents and populate protected variables
	 */
	protected function decode() {
		if ( $this->decoded ) {
			return;
		}

		$jsonParse = $this->getData();
		$data = $jsonParse->isGood() ? $jsonParse->getValue() : null;
		if ( $data ) {
			if ( !$this->isValid() ) {
				$this->displaymode = 'error';
				if ( !parent::isValid() ) {
					// It's not even valid json
					$this->errortext = htmlspecialchars(
						$this->getText()
					);
				} else {
					$this->errortext = FormatJson::encode(
						$data,
						true,
						FormatJson::ALL_OK
					);
				}
			} else {
				$this->displayName = $data->display_name ?? '';
				$this->metadata = $data->metadata ?? [];
				$this->comment = $data->comment ?? [];

				// TODO: sort out metadata
				// TODO: sort out comment data
			}
		}

		// because we use this a lot...
		$this->parser = MediaWikiServices::getInstance()->getParser();

		$this->decoded = true;
	}

	/**
	 * Fill $output with information derived from the content.
	 *
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOptions $options
	 * @param bool $generateHtml
	 * @param ParserOutput &$output
	 */
	protected function fillParserOutput( Title $title, $revId,
		ParserOptions $options, $generateHtml, ParserOutput &$output
	) {
		$this->decode();
		$html = '';

		// If error, then bypass all this and just show the offending JSON
		if ( $this->displaymode == 'error' ) {
			$html = '<div class=errorbox>'
			. wfMessage( 'invalid' )
			. "</div>\n<pre>"
			. $this->errortext
			. '</pre>';
		} else {
			$html = $this->getContent( $title, $options );

			// Add some style stuff
			$output->addModuleStyles( [ 'ext.thicc.threads' ] );
		}

		$output->setText( $html );
	}

	/**
	 * Helper function for fillParserOutput
	 *
	 * @param object $comment from the json
	 * @return string html
	 */
	private function renderComment( $comment, $title, $options ) {
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();

		$author = $comment->metadata->author; // get user with name
		$authorLinks = Html::rawElement(
			'span',
			[ 'class' => 'thicc-user' ],
			$linkRenderer->makeLink( Title::newFromText( $author, NS_USER ), $author ) .
				' (' .
				$linkRenderer->makeLink( Title::newFromText( $author, NS_USER_TALK ), wfMessage( 'talkpagelinktext' )->text() ) .
				') '
		);

		$timestamp = $comment->metadata->timestamp; // convert/render
		$renderedTimestamp = Html::rawElement(
			'span',
			[ 'class' => 'thicc-timestamp' ],
			MWTimestamp::getInstance( $timestamp )->getHumanTimestamp()
		);

		$content = $comment->content;
		$options->enableLimitReport( false );
		$tempOutput = $this->parser->parse( $content, $title, $options );
		$parsedContent = $tempOutput->getText( [ 'unwrap' => true ] );

		$html = '';

		$html .= Html::openElement( 'div', [ 'class' => 'thicc-comment' ] );
		$html .= Html::openElement( 'div', [ 'class' => 'thicc-comment-content' ] );
		$html .= Html::rawElement( 'div', [ 'class' => 'thicc-comment-text' ], $parsedContent );
		$html .= Html::rawElement(
			'div',
			[ 'class' => 'thicc-comment-postinfo' ],
			$authorLinks . $renderedTimestamp
		);

		$html .= Html::closeElement( 'div' );

		if ( property_exists( $comment, 'children' ) && is_array( $comment->children ) ) {
			foreach ( $comment->children as $childComment ) {
				$html .= $this->renderComment( $childComment, $title, $options );
			}
		}

		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Helper function for fillParserOutput/for letting other pages 'transclude' this...
	 *
	 * @return string html
	 */
	public function getContent( $title, $options ) {
		$this->decode();

		$html = '';

		$html .= Html::openElement(
			'div',
			[ 'class' => 'thicc-thread' ]
		);
		$html .= Html::element(
			'h2',
			[ 'class' => 'thicc-headline' ],
			$this->displayName
		);

		// do first comment
		$html .= Html::rawElement(
			'div',
			[ 'class' => 'thicc-comments' ],
			$this->renderComment( $this->comment, $title, $options )
		);

		$html .= Html::closeElement( 'div' );

		return $html;
	}
}
