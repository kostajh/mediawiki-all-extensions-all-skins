<?php

/**
 * Stores which threads go on the page and thicckens them into a usable display
 */
class ThiccModelAggregateContent extends JsonContent {

	/** @var bool Whether contents have been populated */
	protected $decoded = false;

	/** @var array */
	protected $metadata;

	/** @var string */
	protected $introduction;

	/** @var array Threads associated to this page */
	protected $thiccness;

	/** @var string How to display contents */
	protected $displaymode;

	/** @var string Error message text */
	protected $errortext;

	/**
	 * @param string $text
	 */
	public function __construct( $text ) {
		parent::__construct( $text, 'ThiccModelAggregateContent' );
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
				$this->metadata = $data->metadata ?? [];
				$this->introduction = $data->introduction ?? '';
				$this->thiccness = $data->thiccness ?? [];
			}
		}

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
			$html .= $this->introduction;
			$html .= Html::element( 'br' );

			foreach ( $this->thiccness as $thicc ) {
				$title = Title::newFromText( $thicc->thread, NS_THICC );
				$page = WikiPage::factory( $title );
				$content = $page->getContent();

				$html .= $content->getContent( $title, $options );
				// register as template
				$output->addTemplate( $title, $title->getArticleID(), null );
			}

			// Thread styles, check later if we even have any threads to load this
			$output->addModuleStyles( [ 'ext.thicc.threads' ] );
		}

		$output->setText( $html );
	}
}
