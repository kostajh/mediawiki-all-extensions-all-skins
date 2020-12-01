<?php

namespace AutoCatUploads;

use Config;
use File;
use getID3;
use getid3_exception;
use MediaWiki\MediaWikiServices;
use MWException;
use Parser;
use PPFrame;
use SimpleXMLElement;
use Title;
use WikiFilePage;
use WikiPage;

class Hooks {
	/**
	 * Register our {{FILECATEGORIES}} variable
	 *
	 * @param string[] &$customVariables Array of custom variables that MediaWiki recognizes
	 */
	public static function onMagicWordwgVariableIDs( &$customVariables ) {
		$customVariables[] = 'filecategories';
	}

	/**
	 * Retrieve the value of our variable
	 *
	 * @param Parser $parser
	 * @param array &$varCache Optional variable cache used to avoid processing multiple invocations
	 * @param string $magicWordId Internal magic word id (as defined in MagicWordwgVariableIds)
	 * @param string|null &$ret Value of the variable (in the page's content model, generally wikitext)
	 * @param PPFrame|bool $frame Frame for the current parse
	 * @throws MWException
	 */
	public static function onParserGetVariableValueSwitch(
		Parser $parser,
		&$varCache,
		$magicWordId,
		&$ret,
		$frame
	) {
		if ( $magicWordId !== 'filecategories' ) {
			// not us, don't care
			return;
		}

		// from this point on we're checking filesystem, db, and possibly foreign resources
		// as such, increment the expensive function count
		$parser->incrementExpensiveFunctionCount();

		// we never return output, we simply add categories directly via the parser
		$ret = '';

		$title = $frame instanceof PPFrame ? $frame->getTitle() : $parser->getTitle();
		$page = WikiPage::factory( $title );
		if ( !$page instanceof WikiFilePage ) {
			// not a file page, bail out
			return;
		}

		$file = $page->getFile();
		if ( $file === false || !$file->exists() ) {
			return;
		}

		// check our cache; we cache with a tuple of our magic word id (since it's a shared cache)
		// as well as the hash for the associated file.
		$cachekey = 'filecategories-' . $file->getSha1();
		if ( isset( $varCache[$cachekey] ) ) {
			foreach ( $varCache[$cachekey] as $category ) {
				/** @var Title $category */
				$parser->getOutput()->addCategory( $category->getDBkey(), $parser->getDefaultSort() );
			}

			return;
		}

		$keywords = self::getKeywords( $file );
		$categories = [];
		foreach ( $keywords as $potentialCat ) {
			$category = Title::makeTitleSafe( NS_CATEGORY, $potentialCat );

			// don't make cats for invalid titles or titles which happen
			// to include namespace names or interwiki.
			if ( $category === null
				|| $category->getNamespace() !== NS_CATEGORY
				|| $category->isExternal()
			) {
				continue;
			}

			$parser->getOutput()->addCategory( $category->getDBkey(), $parser->getDefaultSort() );
			$categories[] = $category;
		}

		// cache the result for future invocations on this same file
		$varCache[$cachekey] = $categories;
	}

	/**
	 * Alter the initial page text of the Special:Upload form to include {{FILECATEGORIES}}.
	 * This also impacts the importImages.php maintenance script.
	 *
	 * @param string &$pageText Full wikitext for the file description page
	 * @param array $msg Array of localized headings
	 * @param Config $config
	 */
	public static function onGetInitialPageText( &$pageText, array $msg, Config $config ) {
		$magicWord = MediaWikiServices::getInstance()->getMagicWordFactory()->get( 'filecategories' );
		$localized = $magicWord->getSynonym( 0 );
		$pageText .= "\n{{{$localized}}}";
	}

	/**
	 * Get the keywords from file metadata. We parse XMP, ITCP (for JPG),
	 * and ID3 (for MP3).
	 *
	 * @param File $file The file to get keywords for
	 * @return array Array of keywords
	 */
	private static function getKeywords( $file ) {
		$fsfile = $file->getRepo()->getLocalReference( $file->getPath() );
		$path = $fsfile->getPath();

		$keywords = self::parseXMP( $path );
		$metadata = [];
		try {
			$getId3 = new getID3();
			$metadata = $getId3->analyze( $path );
		} catch ( getid3_exception $e ) {
			// no-op
		}

		// IPTC metadata (jpg)
		self::merge( $keywords, $metadata, 'iptc.comments.IPTCApplication.Keywords' );

		// ID3 metadata (mp3, etc.)
		self::merge( $keywords, $metadata, 'id3v2.comments.comment', true );

		return array_unique( $keywords );
	}

	/**
	 * Merge the values given in the metadata path into the keywords
	 *
	 * @param array &$keywords Keywords array, will be merged with found values from metadata
	 * @param array $metadata Metadata array to obtain values from
	 * @param string $path Dot-separated path of array keys in metadata. If any given point of the path
	 * 		does not exist, this function no-ops (it does not throw any errors or exceptions).
	 * @param bool $split If true, each member in the result is further split on commas/semicolons.
	 */
	private static function merge( array &$keywords, array $metadata, $path, $split = false ) {
		$parts = explode( '.', $path );
		$current = $metadata;
		foreach ( $parts as $p ) {
			if ( !array_key_exists( $p, $current ) ) {
				return;
			}

			$current = $current[$p];
		}

		if ( !is_array( $current ) ) {
			$current = [ $current ];
		}

		if ( $split ) {
			foreach ( $current as $combinedString ) {
				$keywords = array_merge( $keywords, self::splitKeywordString( $combinedString ) );
			}
		} else {
			$keywords = array_merge( $keywords, $current );
		}
	}

	/**
	 * Parse XMP metadata from a file
	 *
	 * @param string $fname The file to process
	 * @return array Array of keywords
	 */
	private static function parseXMP( $fname ) {
		// check for XMP metadata
		$f = fopen( $fname, 'rb' );
		$tail = '';
		$xmp = '';
		$xmpState = 0;

		/* XMP States
		 * 0 = look for beginning tag
		 * 1 = beginning tag found in this chunk (don't strip $tail)
		 * 2 = beginning tag found but not in this chunk (strip $tail)
		 * 3 = beginning and end tag found in same chunk (don't strip $tail)
		 * 4 = end tag found, not in same chunk as beginning (strip $tail)
		 */
		while ( !feof( $f ) ) {
			$data = $tail . fread( $f, 4096 );

			if ( $xmpState === 0 ) {
				// looking for beginning tag
				$spos = strpos( $data, '<x:xmpmeta' );
				if ( $spos !== false ) {
					$xmpState = 1;
					$data = substr( $data, $spos );
				}
			}

			if ( $xmpState === 1 || $xmpState === 2 ) {
				$epos = strpos( $data, '</x:xmpmeta>' );
				if ( $epos !== false ) {
					$xmpState += 2;
					$data = substr( $data, 0, $epos + 12 );
				}

				if ( $xmpState === 1 || $xmpState === 3 ) {
					$xmp .= $data;
				} else {
					// don't duplicate $tail
					$xmp .= substr( $data, strlen( $tail ) );
				}
			}

			if ( $xmpState === 1 ) {
				$xmpState = 2;
			} elseif ( $xmpState === 3 || $xmpState === 4 ) {
				break;
			}

			// catch tags split across chunk boundaries by overlapping buffer
			$tail = substr( $data, -16 );
		}

		fclose( $f );

		if ( $xmp === '' ) {
			// no XMP data found
			return [];
		}

		$parsed = new SimpleXMLElement( $xmp );
		$parsed->registerXPathNamespace(
			'dc',
			'http://purl.org/dc/elements/1.1/' );
		$subject = $parsed->xpath( '//dc:subject' );
		$keywords = [];

		if ( $subject !== false && $subject !== [] ) {
			// check if this is an rdf:bag or if it's just a text node
			$subject = $subject[0];
			$subject->registerXPathNamespace(
				'rdf',
				'http://www.w3.org/1999/02/22-rdf-syntax-ns#' );
			$lis = $subject->xpath( './/rdf:li' );

			if ( $lis !== false ) {
				foreach ( $lis as $li ) {
					$keywords[] = trim( $li->__toString() );
				}
			} else {
				$strdata = trim( $subject->__toString() );
				$keywords = self::splitKeywordString( $strdata );
			}
		}

		return $keywords;
	}

	/**
	 * Splits a comma or semicolon separated list of keywords into an array.
	 *
	 * @param string $strdata String to split
	 * @return array Array of keywords
	 */
	private static function splitKeywordString( $strdata ) {
		$keywords = [];

		if ( strpos( $strdata, ';' ) !== false ) {
			$keywords = explode( ';', $strdata );
			foreach ( $keywords as &$key ) {
				$key = trim( $key );
			}
		} elseif ( $strdata !== '' ) {
			$keywords = explode( ',', $strdata );
			foreach ( $keywords as &$key ) {
				$key = trim( $key );
			}
		}

		return $keywords;
	}
}
