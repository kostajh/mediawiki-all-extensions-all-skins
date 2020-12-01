<?php

namespace MediaWiki\Extension\AddHtmlMetaAndTitle;

use MediaWiki\MediaWikiServices;
use OutputPage;
use Parser;
use PPFrame;

/**
 * Singleton class for working with this extension's metadata values.
 */
class Adder {

	/** @var static */
	protected static $instance;

	/** @var string[] */
	protected $parameters = [];

	/** @var string[] The permitted parameter names for the <seo> element. */
	protected $permittedMetaParams = [
		'metak',
		'metad',
		'metakeywords',
		'metadescription',
		'google-site-verification',
	];

	/** @var string[] Mapping of meta name to stored key. */
	protected $storeKeys = [
		'title' => 'TITLE',
		'keywords' => 'METAK',
		'description' => 'METAD',
		'google-site-verification' => 'METAGOOGLESITEVERIFICATION',
	];

	/**
	 * @return static
	 */
	public static function getInstance() {
		if ( static::$instance instanceof static ) {
			return static::$instance;
		}
		static::$instance = new static();
		return static::$instance;
	}

	/**
	 * Get the full list of parameters permitted in the <seo> element.
	 * @return array
	 */
	protected function getPermittedParams() {
		return array_merge( $this->permittedMetaParams, [ 'title' ] );
	}

	/**
	 * Encode a parameter value.
	 * @param string $val The value to encode.
	 * @param Parser $parser The parser.
	 * @param PPFrame $frame The parser frame.
	 * @return string
	 */
	protected function encodeParam( $val, Parser $parser, PPFrame $frame ) {
		$expanded_param = $parser->recursiveTagParse( $val, $frame );
		return base64_encode( $expanded_param );
	}

	/**
	 * Decode a parameter value.
	 * @param string $data The string to decode.
	 * @return bool|string
	 */
	protected function decodeParam( $data ) {
		return base64_decode( $data );
	}

	/**
	 * Validate and then save the incoming parameters for later outputting in the page HEAD.
	 * @param string $text The element contents. Not used.
	 * @param string[] $params The element parameters.
	 * @param Parser $parser The parser.
	 * @param PPFrame $frame The parser frame.
	 * @return string
	 */
	public function renderSeoElement( $text, $params = [], Parser $parser, PPFrame $frame ) {
		$permittedParams = array_intersect( array_keys( $params ), $this->getPermittedParams() );
		if ( count( $permittedParams ) === 0 ) {
			return '<p class="error">'
				. wfMessage( 'addhtmlmetaandtitle-empty-attr' )->escaped()
				. '</p>';
		}
		$out = '';
		foreach ( $params as $name => $param ) {
			$commentTitle = false;
			if ( $name === 'title' ) {
				$commentTitle = $this->storeKeys['title'];
			} elseif ( in_array( $name, [ 'metak', 'metakeywords' ] ) ) {
				$commentTitle = $this->storeKeys['keywords'];
			} elseif ( in_array( $name, [ 'metad', 'metadescription' ] ) ) {
				$commentTitle = $this->storeKeys['description'];
			} elseif ( $name === 'google-site-verification' ) {
				$commentTitle = $this->storeKeys['google-site-verification'];
			}
			// If a valid parameter was found, store its value as an HTML comment in the page text.
			if ( $commentTitle ) {
				// The string used here is deconstructed in self::extractData().
				$out .= "<!-- ADD$commentTitle "
						. $this->encodeParam( $param, $parser, $frame )
						. " -->";
			}
		}
		return $out;
	}

	/**
	 * Add meta elements and modify the title of the given OutputPage.
	 * @param OutputPage $outputPage The output page to add the metadata to.
	 */
	public function addMetaAndTitle( OutputPage $outputPage ) {
		$data = $this->extractData( $outputPage->mBodytext );

		// Title.
		$titleStoreKey = $this->storeKeys['title'];
		if ( isset( $data[ $titleStoreKey ] ) ) {
			$config = MediaWikiServices::getInstance()->getMainConfig();
			$newTitle = $data[ $titleStoreKey ] . ' - ' . $config->get( 'Sitename' );
			$outputPage->setHTMLTitle( $newTitle );
			unset( $data[ $titleStoreKey ] );
		}

		// Meta elements.
		foreach ( $data as $name => $value ) {
			$metaNames = array_flip( $this->storeKeys );
			$metaName = $metaNames[ $name ];
			$outputPage->addMeta( $metaName, $value );
		}
	}

	/**
	 * @param string $pageText The text to get the HTML comments from.
	 * @return string[]
	 */
	protected function extractData( $pageText ) {
		$pattern = '/<!-- ADD([A-Z]+) ([0-9a-zA-Z\\+\\/]+=*) -->/m';
		$matches = preg_match_all( $pattern, $pageText, $extracted );
		$data = [];
		if ( $matches === 0 ) {
			return $data;
		}
		$keys = $extracted[1];
		$values = $extracted[2];
		$numKeys = count( $keys );
		for ( $i = 0; $i < $numKeys; $i++ ) {
			$data[$keys[$i]] = $this->decodeParam( $values[$i] );
		}
		return $data;
	}
}
