<?php
/**
 * This file contains classes for manipulating the contents of an SVG file.
 * Intended to centralise references to PHP's byzantine DOM manipulation system.
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2014 Harry Burt
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

class SVGFile {
	/**
	 * @var DOMDocument
	 */
	private $document;

	/**
	 * @var DOMXpath
	 */
	protected $xpath = null;

	protected $isTranslationReady = false;
	protected $savedLanguages;
	protected $inFileTranslations;
	protected $filteredTextNodes;
	protected $fallbackLanguage;

	/**
	 * Construct an SVGFile object.
	 *
	 * @see self::newFromMessageGroup
	 * @param string $path
	 * @param string $fallbackLanguage
	 * @todo Handle DOM warnings
	 */
	public function  __construct( $path, $fallbackLanguage ) {
		// Save sourceLanguage for later (mostly so we can understand which language is the fallback)
		$this->fallbackLanguage = $fallbackLanguage;

		$this->document = new DOMDocument( '1.0' );

		// Warnings need to be suppressed in case there are DOM warnings
		Wikimedia\suppressWarnings();
		$this->document->load( $path );
		$this->xpath = new DOMXpath( $this->document );
		Wikimedia\restoreWarnings();

		$this->xpath->registerNamespace( 'svg', 'http://www.w3.org/2000/svg' );

		// $this->isTranslationReady() can be used to test if construction was a success
		$this->makeTranslationReady();
	}

	/**
	 * Was the file successfully made translation ready i.e. is it translatable?
	 *
	 * @return bool
	 */
	public function isTranslationReady() {
		return $this->isTranslationReady;
	}

	/**
	 * Makes $this->document ready for translation by inserting <switch> tags where they need to be, etc.
	 * Also works as a check on the compatibility of the file since it will return false if it fails.
	 *
	 * @todo Find a way of making isTranslationReady a proper check
	 * @todo add interlanguage consistency check
	 * @return bool False on failure, DOMDocument on success
	 */
	protected function makeTranslationReady() {
		if ( $this->isTranslationReady ) {
			return true;
		}

		if ( $this->document->documentElement === null ) {
			// Empty or malformed file
			return false;
		}

		// Automated editors have a habit of using XML entity references in the SVG namespace
		// declaration or simply forgetting to set one at all. Both need to be fixed.
		$defaultNS = $this->document->documentElement->lookupnamespaceURI( null );
		if ( $defaultNS === null || preg_match( '/^(&[^;]+;)+$/', $defaultNS, $match ) ) {
			// Bad or nonexistent default namespace set, fill in sensible default
			$this->document->documentElement->setAttributeNS(
				'http://www.w3.org/2000/xmlns/',
				'xmlns',
				'http://www.w3.org/2000/svg'
			);
			$defaultNS = 'http://www.w3.org/2000/svg';
		}

		$texts = $this->document->getElementsByTagName( 'text' );
		$textLength = $texts->length;
		if ( $textLength === 0 ) {
			// Nothing to translate!
			return false;
		}

		$styles = $this->document->getElementsByTagName( 'style' );
		$styleLength = $styles->length;
		for ( $i = 0; $i < $styleLength; $i++ ) {
			$style = $styles->item( $i );
			$CSS = $style->textContent;
			if ( strpos( $CSS, '#' ) !== false ) {
				if ( !preg_match( '/^([^{]+\{[^}]*\})*[^{]+$/', $CSS ) ) {
					// Can't easily understand the CSS to check it, so exit
					return false;
				}
				$selectors = preg_split( '/\{[^}]+\}/', $CSS );
				foreach ( $selectors as $selector ) {
					if ( strpos( $selector, '#' ) !== false ) {
						// IDs in CSS will break when we clone things, should be classes
						return false;
					}
				}
			}
		}

		if ( $this->document->getElementsByTagName( 'tref' )->length !== 0 ) {
			// Tref tags not (yet) supported
			return false;
		}

		// Strip empty tspans, texts, fill $idsInUse
		$idsInUse = [ 0 ];
		$translatableNodes = [];
		$tspans = $this->document->getElementsByTagName( 'tspan' );
		$texts = $this->document->getElementsByTagName( 'text' );
		foreach ( $tspans as $tspan ) {
			if ( $tspan->childNodes->length > 1
				|| ( $tspan->childNodes->length == 1 && $tspan->childNodes->item( 0 )->nodeType !== XML_TEXT_NODE )
			) {
				return false; // Nested tspans not (yet) supported
			}
			$translatableNodes[] = $tspan;
		}
		foreach ( $texts as $text ) {
			$translatableNodes[] = $text;
		}
		foreach ( $translatableNodes as $translatableNode ) {
			/** @var DOMElement $translatableNode */
			if ( $translatableNode->hasAttribute( 'id' ) ) {
				$id = trim( $translatableNode->getAttribute( 'id' ) );
				$translatableNode->setAttribute( 'id', $id );
				if ( strpos( $id, '|' ) !== false || strpos( $id, '/' ) !== false ) {
					// Will cause problems later
					return false;
				}
				if ( preg_match( '/^trsvg([0-9]+)/', $id, $matches ) ) {
					$idsInUse[] = $matches[1];
				}
				if ( is_numeric( $id ) ) {
					$translatableNode->removeAttribute( 'id' );
				}
			}
			if ( !$translatableNode->hasChildNodes() ) {
				// Empty tag, will just confuse translators if we leave it in
				$translatableNode->parentNode->removeChild( $translatableNode );
			}
		}

		// Reset $translatableNodes
		$translatableNodes = [];
		$tspans = $this->document->getElementsByTagName( 'tspan' );
		$texts = $this->document->getElementsByTagName( 'text' );
		foreach ( $tspans as $tspan ) {
			array_push( $translatableNodes, $tspan );
		}
		foreach ( $texts as $text ) {
			array_push( $translatableNodes, $text );
		}

		// Create id attributes for text, tspan nodes missing it
		foreach ( $translatableNodes as $translatableNode ) {
			if ( !$translatableNode->hasAttribute( 'id' ) ) {
				$newId = ( max( $idsInUse ) + 1 );
				$translatableNode->setAttribute( 'id', 'trsvg' . $newId );
				$idsInUse[] = $newId;
			}
		}

		$textLength = $this->document->getElementsByTagName( 'text' )->length;
		for ( $i = 0; $i < $textLength; $i++ ) {
			/** @var DOMElement $text */
			$text = $this->document->getElementsByTagName( 'text' )->item( $i );

			// Text strings like $1, $2 will cause problems later because
			// self::replaceIndicesRecursive() will try to replace them
			// with (non-existent) child nodes.
			if ( preg_match( '/$[0-9]/', $text->textContent ) ) {
				return false;
			}

			// Sort out switches
			if ( $text->parentNode->nodeName !== 'switch'
				&& $text->parentNode->nodeName !== 'svg:switch'
			) {
				// Every text should now be in a switch
				$switch = $this->document->createElementNS( $defaultNS, 'switch' );
				$text->parentNode->insertBefore( $switch, $text );
				// Move node into new sibling <switch> element
				$switch->appendChild( $text );
			}

			// Transforms on individual texts are particular problematic, should move it to the <switch>
			if ( $text->hasAttribute( 'transform' ) ) {
				$text->parentNode->setAttribute( 'transform', $text->getAttribute( 'transform' ) );
				$text->removeAttribute( 'transform' );
			}

			// Non-translatable style elements on texts get lost, so bump up to switch
			if ( $text->hasAttribute( 'style' ) ) {
				$style = $text->getAttribute( 'style' );
				$text->parentNode->setAttribute( 'style', $style );
			}

			$numChildren = $text->childNodes->length;
			for ( $j = 0; $j < $numChildren; $j++ ) {
				$child = $text->childNodes->item( $j );
				if ( $child->nodeType !== XML_TEXT_NODE
					&& $child->nodeName !== 'tspan'
					&& $child->nodeName !== 'svg:tspan'
				) {
					// Tags other than tspan inside text tags are not (yet) supported
					return false;
				}
			}
		}

		$switchLength = $this->document->getElementsByTagName( 'switch' )->length;
		for ( $i = 0; $i < $switchLength; $i++ ) {
			$switch = $this->document->getElementsByTagName( 'switch' )->item( $i );
			$siblings = $switch->childNodes;
			foreach ( $siblings as $sibling ) {
				/** @var DOMElement $sibling */

				$languagesPresent = [];
				if ( $sibling->nodeType === XML_TEXT_NODE ) {
					if ( trim( $sibling->textContent ) !== '' ) {
						// Text content inside switch but outside text tags is awkward.
						return false;
					}
					continue;
				} elseif ( $sibling->nodeType !== XML_ELEMENT_NODE ) {
					// Only text tags are allowed inside switches
					return false;
				}

				if ( $sibling->nodeName !== 'text' && $sibling->nodeName !== 'svg:text' ) {
					return false;
				}

				$language = $sibling->hasAttribute( 'systemLanguage' ) ?
					$sibling->getAttribute( 'systemLanguage' ) : 'fallback';
				$realLangs = preg_split( '/, */', $language );
				foreach ( $realLangs as $realLang ) {
					if ( in_array( $realLang, $languagesPresent ) ) {
						// Two tags for the same language
						return false;
					}
					$languagesPresent[] = $realLang;
				}
				if ( count( $realLangs ) === 1 ) {
					continue;
				}
				foreach ( $realLangs as $realLang ) {
					// Although the SVG spec supports multi-language text tags (e.g. "en,fr,de")
					// these are a really poor idea since (a) they are confusing to read and (b) the
					// desired translations could diverge at any point. So get rid.
					$singleLanguageNode = $sibling->cloneNode( true );
					$singleLanguageNode->setAttribute( 'systemLanguage', $realLang );

					// @todo: Should also go into tspans and change their ids, too.
					// $prefix = implode( '-', explode( '-', $singleLanguageNode->getAttribute( 'id' ), -1 ) );
					// $singleLanguageNode->setAttribute( 'id', "$prefix-$realLang" );

					// Add in new element
					$switch->appendChild( $singleLanguageNode );
				}
				$switch->removeChild( $sibling );
			}
		}

		$this->reorderTexts();

		$this->isTranslationReady = true;
		return true;
	}

	/**
	 * Analyse the SVG file, extracting translations and other metadata. Expects the file to
	 * be in a certain format: see self::makeTranslationReady() for details.
	 *
	 * @return array Array of translations (indexed by ID, then langcode, then property)
	 */
	protected function analyse() {
		$switches = $this->document->getElementsByTagName( 'switch' );
		$number = $switches->length;
		$translations = [];
		$this->filteredTextNodes = []; // Reset

		for ( $i = 0; $i < $number; $i++ ) {
			/** @var DOMElement $switch */
			$switch = $switches->item( $i );

			$texts = $switch->getElementsByTagName( 'text' );
			$count = $texts->length;
			if ( $count === 0 ) {
				continue;
			}
			$fallback = $this->xpath->query(
				"text[not(@systemLanguage)]|svg:text[not(@systemLanguage)]", $switch
			);
			if ( $fallback->length === 0 ) {
				// Some sort of deep hierarchy, can't translate
				continue;
			}

			/** @var DOMElement $fallbackText */
			$fallbackText = $fallback->item( 0 );
			$fallbackTextId = $fallbackText->getAttribute( 'id' );

			for ( $j = 0; $j < $count; $j++ ) {
				// Don't want to manipulate actual node
				/** @var DOMElement $actualNode */
				$actualNode = $texts->item( $j );
				$text = $actualNode->cloneNode( true );
				$numChildren = $text->childNodes->length;
				$hasActualTextContent = self::hasActualTextContent( $text );
				$lang = $text->hasAttribute( 'systemLanguage' ) ? $text->getAttribute( 'systemLanguage' ) : 'fallback';
				$langCode = TranslateSvgUtils::osToLangCode( $lang );

				$counter = 1;
				for ( $k = 0; $k < $numChildren; $k++ ) {
					$child = $text->childNodes->item( $k );
					if ( $child->nodeType === 1 ) {
						// Per the checks in makeTranslationReady() this is a tspan so
						// register it as a child node.

						/** @var DOMElement $childTspan */
						$childTspan = $fallbackText->getElementsByTagName( 'tspan' )->item( $counter - 1 );

						$childId = $childTspan->getAttribute( 'id' );
						$translations[$childId][$langCode] = $this->nodeToArray( $child );
						$translations[$childId][$langCode]['data-parent'] = $fallbackTextId;
						if ( $text->hasAttribute( 'data-children' ) ) {
							$existing = $text->getAttribute( 'data-children' );
							$text->setAttribute( 'data-children', "$existing|$childId" );
						} else {
							$text->setAttribute( 'data-children', $childId );
						}

						// Replace with $1, $2 etc.
						$text->replaceChild( $this->document->createTextNode( '$' . $counter ), $child );
						$counter++;
					}
				}
				if ( $hasActualTextContent ) {
					// If the <text> has *its own* text content, rather than just <tspan>s, register it
					// for translation.
					$translations[$fallbackTextId][$langCode] = $this->nodeToArray( $text );
				} else {
					$this->filteredTextNodes[$fallbackTextId][$langCode] = $this->nodeToArray( $text );
				}
				$savedLang = ( $langCode === 'fallback' ) ? $this->fallbackLanguage : $langCode;
				$this->savedLanguages[] = $savedLang;
			}
		}
		$this->inFileTranslations = $translations;
		$this->savedLanguages = array_unique( $this->savedLanguages );
	}

	/**
	 * Try to return $this->inFileTranslations. If it is not cached, analyse the SVG
	 * and hence generate it.
	 *
	 * @return array
	 */
	public function getInFileTranslations() {
		if ( $this->inFileTranslations === null ) {
			$this->analyse();
		}
		return $this->inFileTranslations;
	}

	/**
	 * Try to return $this->savedLanguages (a list of languages which have one or more
	 * translations in-file). If it is not cached, analyse the SVG and hence generate it.
	 *
	 * @return array
	 */
	public function getSavedLanguages() {
		if ( $this->savedLanguages === null ) {
			$this->analyse();
		}
		return $this->savedLanguages;
	}

	/**
	 * Get a list of languages which have one or more translations in-file
	 *
	 * @return array Array of languages, split into 'full' and 'partial' subarrays
	 */
	public function getSavedLanguagesFiltered() {
		$translations = $this->getInFileTranslations();
		$savedLanguages = $this->getSavedLanguages();

		$full = [];
		$partial = [];
		foreach ( $savedLanguages as $savedLanguage ) {
			$fullSoFar = true;
			foreach ( $translations as $languages ) {
				if ( !isset( $languages[$savedLanguage] ) ) {
					$fullSoFar = false;
					break;
				}
			}
			if ( $fullSoFar || $savedLanguage == $this->fallbackLanguage ) {
				$full[] = $savedLanguage;
			} else {
				$partial[] = $savedLanguage;
			}
		}
		return [ 'full' => $full, 'partial' => $partial ];
	}

	/**
	 * Try to return $this->filteredTextNodes (an array of <text> nodes that contain only
	 * child elements). If it is not cached, analyse the SVG and hence generate it.
	 *
	 * @return array
	 */
	public function getFilteredTextNodes() {
		if ( $this->filteredTextNodes === null ) {
			$this->analyse();
		}
		return $this->filteredTextNodes;
	}

	/**
	 * Compile an updated DOM model of the SVG using the provided set of translations
	 *
	 * @param array[] $translations
	 * @return array[] Array with keys 'expanded' and 'started', each an array of language names
	 */
	public function switchToTranslationSet( array $translations ) {
		$currentLanguages = $this->getSavedLanguages();
		$expanded = $started = [];

		$switches = $this->document->getElementsByTagName( 'switch' );
		$number = $switches->length;
		for ( $i = 0; $i < $number; $i++ ) {
			$switch = $switches->item( $i );
			$fallback = $this->xpath->query(
				"text[not(@systemLanguage)]|svg:text[not(@systemLanguage)]", $switch
			);
			if ( $fallback->length === 0 ) {
				// Some sort of deep hierarchy, can't translate
				continue;
			}

			/** @var DOMElement $fallbackText */
			$fallbackText = $fallback->item( 0 );
			$textId = $fallbackText->getAttribute( 'id' );

			foreach ( $translations[$textId] as $language => $translation ) {
				// Sort out systemLanguage attribute
				if ( $language !== 'fallback' ) {
					$translation['systemLanguage'] = TranslateSvgUtils::langCodeToOs( $language );
				}

				// Prepare an array of "children" (sub-messages)
				$children = [];
				if ( isset( $translation['data-children'] ) ) {
					$children = explode( '|', $translation['data-children'] );
					foreach ( $children as &$child ) {
						if ( isset( $translations[$child][$language] ) ) {
							$child = $translations[$child][$language];
						} else {
							$child = $translations[$child]['fallback'];
						}
						$child = $this->arrayToNode( $child, 'tspan' );
					}
				}

				// Set up text tag
				$text = $translation['text'];
				unset( $translation['text'] );
				$newTextTag = $this->arrayToNode( $translation, 'text' );

				// Add text, replacing $1, $2 etc. with translations
				$this->replaceIndicesRecursive( $text, $children, $newTextTag, $this->document );

				// Put text tag into document
				$path = ( $language === 'fallback' ) ?
					"svg:text[not(@systemLanguage)]|text[not(@systemLanguage)]" :
					"svg:text[@systemLanguage='$language']|text[@systemLanguage='$language']";
				$existing = $this->xpath->query( $path, $switch );
				if ( $existing->length == 1 ) {
					// Only one matching text node, replace if different
					if ( $this->nodeToArray( $newTextTag ) === $this->nodeToArray( $existing->item( 0 ) ) ) {
						continue;
					}
					$switch->replaceChild( $newTextTag, $existing->item( 0 ) );
				} elseif ( $existing->length == 0 ) {
					// No matching text node for this language, so we'll create one
					$switch->appendChild( $newTextTag );
				}

				// To have got this far, we must have either updated or started a new language
				$langName = TranslateSvgUtils::fetchLanguageName( $language, $this->fallbackLanguage );
				if ( in_array( $language, $currentLanguages ) || $language == 'fallback' ) {
					$expanded[] = $langName;
				} else {
					$started[] = $langName;
				}
			}
		}
		$this->reorderTexts();

		return [
			'started' => array_unique( $started ),
			'expanded' => array_unique( $expanded )
		];
	}

	/**
	 * Export the SVG as a string, i.e. as "<?xml version...</svg>"
	 *
	 * @return string
	 */
	public function saveToString() {
		// Could have simply overridden __toString() but probably not a good idea with
		// no clear benefit.
		return $this->document->saveXML();
	}

	/**
	 * Export the SVG to the desired filepath
	 *
	 * @param string $path
	 * @return int|bool The number of bytes written or false if an error occurred.
	 */
	public function saveToPath( $path ) {
		return $this->document->save( $path );
	}

	/**
	 * One of several functions used to convert between TranslateSvg's
	 * three main formats for handling data (nodes, translations and arrays).
	 * This one converts between the node and array translation. The function
	 * assumes that the node does not have any child nodes that need to be
	 * converted.
	 *
	 * @param DOMNode $node A DOMNode object (probably a <text> or <tspan>)
	 * @return array An associative array of properties, including 'text'
	 */
	public function nodeToArray( DOMNode $node ) {
		$array = [ 'text' => $node->textContent ];
		$attributes = ( $node->hasAttributes() ) ? $node->attributes : [];
		foreach ( $attributes as $attribute ) {
			$prefix = ( $attribute->prefix === '' ) ? '' : ( $attribute->prefix . ':' );
			if ( $attribute->name === 'space' ) {
				// XML namespace prefix seems to disappear: TODO?
				$prefix = 'xml:';
			}
			list( $attrName, $attrValue ) = TranslateSvgUtils::mapFromAttribute(
				$prefix . $attribute->name, $attribute->value
			);
			if ( $attrName === false || $attrValue === false ) {
				continue;
			}
			list( $attrName, $attrValue ) = TranslateSvgUtils::mapToAttribute( $attrName, $attrValue );
			if ( $attrName === false || $attrValue === false ) {
				continue;
			}
			$array[ $attrName ] = $attrValue;
		}
		return $array;
	}

	/**
	 * One of several functions used to convert between TranslateSvg's
	 * three main formats for handling data (nodes, translations and arrays).
	 * This one converts between the array and node formats.
	 *
	 * @param array $array An associative array of properties, inc 'text'
	 * @param string $nodeName (optional) The name of the node (no <>), default 'text'
	 * @return DOMNode A new DOMNode ready to be inserted, complete with text child
	 */
	public function arrayToNode( $array, $nodeName = 'text' ) {
		$defaultNS = $this->document->documentElement->lookupnamespaceURI( null );
		$newNode = $this->document->createElementNS( $defaultNS, $nodeName );

		// Handle the text property first...
		if ( isset( $array['text'] ) ) {
			$textContent = $this->document->createTextNode( $array['text'] );
			$newNode->appendChild( $textContent );
			unset( $array['text'] );
		}

		// ...then all other properties
		foreach ( $array as $attrName => $attrValue ) {
			if ( $attrName !== false && !preg_match( '/^data\-/', $attrName ) ) {
				$newNode->setAttribute( $attrName, $attrValue );
			}
		}
		return $newNode;
	}

	/**
	 * Checks whether a given DOMNode has some non-negligible text content (as
	 * opposed to just whitespace or other tags. Whitespace *between* tags
	 * counts, as it does get rendered.
	 *
	 * @param DOMNode $node The node to check for text content
	 * @return bool True if content found, false if not
	 */
	public static function hasActualTextContent( DOMNode $node ) {
		// No text nodes means no text content
		if ( !$node->hasChildNodes() ) {
			return false;
		}

		// Search child nodes looking for matching content
		$children = $node->childNodes;
		$numChildren = $children->length;
		for ( $i = 0; $i < $numChildren; $i++ ) {
			if ( $children->item( $i )->nodeType == XML_TEXT_NODE ) {
				// Whitespace at beginning and end doesn't count, but
				// otherwise we have a match
				if ( !( $i === 0 || $i === ( $numChildren - 1 ) )
					|| !( strlen( trim( $children->item( $i )->textContent ) ) === 0 )
				) {
					return true;
				}
			}
		}

		// Didn't find any
		return false;
	}

	/**
	 * Recursively replaces $1, $2, etc. with text tags, if required. Text content
	 * is formalised as actual text nodes
	 *
	 * @param string $text The text to search for $1, $2 etc.
	 * @param DOMNode[] &$newNodes An array of DOMNodes, indexed by which $ number they represent
	 * @param DOMNode &$parentNode A node to fill with the generated content
	 * @param DOMDocument $document Base document to use
	 * @return void
	 */
	public static function replaceIndicesRecursive(
		$text,
		array &$newNodes,
		DOMNode &$parentNode,
		DOMDocument $document
	) {
		// If nothing to replace, just fire back a text node
		if ( count( $newNodes ) === 0 ) {
			if ( strlen( $text ) > 0 ) {
				$parentNode->appendChild( $document->createTextNode( $text ) );
			}
		}

		// Otherwise, loop through $1, $2, etc. replacing each
		preg_match_all( '/\$([0-9]+)/', $text, $matches );
		foreach ( $newNodes as $index => $node ) {
			// One-indexed (no $0)
			$realIndex = $index + 1;
			if ( !in_array( $realIndex, $matches[1] ) ) {
				// Sanity check
				continue;
			}
			list( $before, $after ) = preg_split( '/\$' . $realIndex . '(?=[^0-9]|$)/', $text );
			$newNodeToProcess = $newNodes[$index];
			unset( $newNodes[$index] );
			self::replaceIndicesRecursive( $before, $newNodes, $parentNode, $document );
			$parentNode->appendChild( $newNodeToProcess );
			self::replaceIndicesRecursive( $after, $newNodes, $parentNode, $document );
		}
	}

	/**
	 * Factory method for getting the related SVGFile for an SVGMessageGroup
	 *
	 * @param SVGMessageGroup $group
	 * @throws MWException
	 * @return SVGFile
	 *
	 * @todo Separate the concepts of source and fallback languages
	 */
	public static function newFromMessageGroup( SVGMessageGroup $group ) {
		$title = Title::makeTitleSafe( NS_FILE, $group->getId() );
		$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
		if ( !$file || !$file->exists() ) {
			// Double-check it definitely exists
			throw new MWException( 'File not found' );
		}

		return new SVGFile( $file->getLocalRefPath(), $group->getSourceLanguage() );
	}

	protected function reorderTexts() {
		// Move sublocales to the beginning of their switch elements
		$sublocales = $this->xpath->query(
			"//text[contains(@systemLanguage,'_')]" . "|" . "//svg:text[contains(@systemLanguage,'_')]"
		);
		$count = $sublocales->length;
		for ( $i = 0; $i < $count; $i++ ) {
			$firstSibling = $sublocales->item( $i )->parentNode->childNodes->item( 0 );
			$sublocales->item( $i )->parentNode->insertBefore( $sublocales->item( $i ), $firstSibling );
		}

		// Move fallbacks to the end of their switch elements
		$fallbacks = $this->xpath->query(
			"//text[not(@systemLanguage)]" . "|" . "//svg:text[not(@systemLanguage)]"
		);
		$count = $fallbacks->length;
		for ( $i = 0; $i < $count; $i++ ) {
			$fallbacks->item( $i )->parentNode->appendChild( $fallbacks->item( $i ) );
		}
	}
}
