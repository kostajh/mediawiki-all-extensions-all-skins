<?php
/**
 * File for the SVGMessageGroup class used in SVG translation
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland, Harry Burt
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

/**
 * Group for messages that are stored in subpages of the File namespace.
 *
 * @ingroup MessageGroups
 */
class SVGMessageGroup extends WikiMessageGroup {
	protected $source = null;
	protected $sourceLanguage = null;
	protected $onWikiTranslations = null;

	/**
	 * @param string $filename Name of the file to be translated (no namespace)
	 * @throws MWException if file not found
	 */
	public function __construct( $filename ) {
		global $wgLang, $wgContLang;

		$title = Title::newFromText( $filename, NS_FILE );

		if ( $title === null || !$title->exists() ) {
			throw new MWException( 'File not found' );
		}

		// Pick up normalisation
		$filename = $title->getText();
		$this->setNamespace( NS_FILE );
		$this->setLabel( $filename );
		$this->setId( $filename );
		$prefixedFilename = $wgContLang->getNsText( NS_FILE ) . ':' . $filename;

		// Parental constructor. Sets $this->source.
		parent::__construct( $filename, $filename );

		$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
		if ( !$file || !$file->exists() ) {
			throw new MWException( 'File not found' );
		}

		$rev = '';
		if ( $title->exists() ) {
			// If the *page* associated with the file exists, grab its content
			$rev = $this->getWikitextForTransclusion( $title );
			$revsections = explode( "\n==", $rev );
			foreach ( $revsections as $revsection ) {
				// Attempt to trim the file description page down to only the most relevant content
				if ( strpos( $revsection, '{{Information' ) !== false ) {
					$rev = trim( preg_replace( "/==+[^=]+==+/", "", $revsection ) );
				}
			}
		}

		if ( trim( $rev ) === '' ) {
			$rev = wfMessage( 'translate-svg-nodesc' )->plain();
		}

		$desc = "[[$prefixedFilename|thumb|" . $wgLang->alignEnd() . "|upright|275x275px]]" . "\n" .
			Html::rawElement( 'div', [ 'style' => 'overflow:auto; padding:2px;' ], $rev );
		$this->setDescription( $desc );
	}

	/**
	 * Return a full URL to the file page of the SVG
	 * @return string
	 */
	public function getUrl() {
		return Title::makeTitleSafe( $this->getNamespace(), $this->source )->getFullURL();
	}

	/**
	 * Fetch definitions from database.
	 * @return \array Array of messages keys with definitions.
	 */
	public function getDefinitions() {
		$definitions = [];
		$subpages = Title::makeTitleSafe( $this->getNamespace(), $this->source )->getSubpages();
		foreach ( $subpages as $subpage ) {
			/** @var Title $subpage */
			if ( $this->isSourceLanguage( $subpage->getSubpageText() ) ) {
				$definition = $this->getWikitextForTransclusion( $subpage );
				$definition = TranslateSvgUtils::stripPropertyString( $definition );

				// Is there really not an easier way to get the parent page than:
				$messageparent = str_replace( '/' . $subpage->getSubpageText(), '', $subpage->getText() );
				$definitions[$messageparent] = $definition;
			}
		}
		return $definitions;
	}

	/**
	 * Returns the $code-language translation of a message specified by $key
	 *
	 * @param string $key Key of the message.
	 * @param string $code Language code.
	 * @return string|null The translation or null if it doesn't exist.
	 */
	public function getMessage( $key, $code ) {
		$title = Title::makeTitleSafe( $this->getNamespace(), "$key/$code" );
		if ( !$title->exists() ) {
			return null;
		}
		$definition = $this->getWikitextForTransclusion( $title );
		$definition = TranslateSvgUtils::stripPropertyString( $definition );
		return $definition;
	}

	/**
	 * Returns the associated properties of the message specified by $key
	 *
	 * @param string $key Key of the message.
	 * @param string $code Language code.
	 * @return string|null The translation or null if it doesn't exist.
	 */
	public function getProperties( $key, $code ) {
		$title = Title::makeTitleSafe( $this->getNamespace(), "$key/$code" );
		if ( !$title->exists() ) {
			return '';
		}
		$translation = $this->getWikitextForTransclusion( $title );
		$properties = TranslateSvgUtils::extractPropertyString( $translation );

		return $properties;
	}

	/**
	 * Import translations from the file, onto the wiki
	 *
	 * @return bool True on success, false on failure
	 */
	public function importTranslations() {
		global $wgTranslateSvgBotName;
		$bot = User::newFromName( $wgTranslateSvgBotName, false );

		$svg = SVGFile::newFromMessageGroup( $this );
		if ( $svg === null ) {
			return false;
		}

		$translations = $svg->getInFileTranslations();
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		foreach ( $translations as $key => $outerArray ) {
			foreach ( $outerArray as $language => $innerArray ) {
				if ( $language === 'fallback' ) {
					$language = $this->getSourceLanguage();
				}
				$translation = TranslateSvgUtils::arrayToTranslation( $innerArray );
				$fullKey = $this->source . '/' . $key . '/' . $language;
				$title = Title::makeTitleSafe( $this->getNamespace(), $fullKey );
				if ( $title === null || $title->exists() ) {
					// @todo: consider whether an update of the page is in order
					continue;
				}
				if ( !$pm->userCan( 'create', $bot, $title ) ) {
					// Needs to be created, can't be, so fail
					return false;
				}
				$wikiPage = new WikiPage( $title );
				$summary = wfMessage( 'translate-svg-autocreate' )->inContentLanguage()->text();
				$content = ContentHandler::makeContent( $translation, $title );
				$status = $wikiPage->doEditContent( $content, $summary, 0, false, $bot );
				if ( !$status->isOK() ) {
					// Needs to be created, couldn't, so fail
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Returns the source language code of this message group by using
	 * the TranslateMetadata framework, or 'en' (English) if none set.
	 * Overrides parent method
	 *
	 * @return string Language code
	 */
	public function getSourceLanguage() {
		if ( !isset( $this->sourceLanguage ) ) {
			$databaseValue = TranslateMetadata::get( $this->source, 'sourcelang' );
			$this->sourceLanguage = ( $databaseValue !== false ) ? $databaseValue : 'en';
		}
		return $this->sourceLanguage;
	}

	/**
	 * Sets the source language code of this message group by using
	 * the TranslateMetadata framework.
	 *
	 * @param string $srcLang The source language code
	 * @return null
	 */
	public function setSourceLanguage( $srcLang ) {
		$this->sourceLanguage = $srcLang;
		TranslateMetadata::set( $this->source, 'sourcelang', $srcLang );
	}

	/**
	 * Returns a list of languages the file has been translated into *on wiki*
	 * i.e. some of those may not have been saved back to the file yet.
	 * @return array
	 */
	public function getOnWikiLanguages() {
		$stats = MessageGroupStats::forGroup( $this->getId() );
		$languages = [];
		foreach ( $stats as $language => $data ) {
			$translatedCount = $data[MessageGroupStats::TRANSLATED];
			$fuzzyCount = $data[MessageGroupStats::FUZZY];
			if ( $translatedCount > 0 || $fuzzyCount > 0 ) {
				$languages[] = $language;
			}
		}
		return $languages;
	}

	/**
	 * Extract translations from on wiki
	 *
	 * @param bool $forceUpdate Force the regeneration the list (default: false)
	 * @return array Array of translations (indexed by ID, then langcode, then property)
	 */
	public function getOnWikiTranslations( $forceUpdate = false ) {
		if ( $this->onWikiTranslations !== null && !$forceUpdate ) {
			return $this->onWikiTranslations;
		}

		$onWikiTranslations = [];
		$languages = $this->getOnWikiLanguages();

		// Translations generated onwiki
		foreach ( $languages as $language ) {
			$collection = $this->initCollection( $language );
			$collection->loadTranslations();
			$mangler = $this->getMangler();
			foreach ( $collection as $item ) {
				/** @var TMessage $item */
				$key = explode( '/', $mangler->unmangle( $item->key() ) );
				$key = array_pop( $key );
				$translation = str_replace( TRANSLATE_FUZZY, '', $item->translation() );

				if ( $translation === '' ) {
					// No translation provided
					continue;
				}

				$language = ( $this->getSourceLanguage() == $language ) ? 'fallback' : $language;
				$item = TranslateSvgUtils::translationToArray( $translation );
				if ( !isset( $onWikiTranslations[$key] ) ) {
					$onWikiTranslations[$key] = [];
				}
				$onWikiTranslations[$key][$language] = $item;
			}
		}

		$this->onWikiTranslations = $onWikiTranslations;
		return $this->onWikiTranslations;
	}

	public function register( $useJobQueue = true ) {
		$articleId = Title::newFromText( $this->getLabel(), NS_FILE )->getArticleId();

		$dbw = wfGetDB( DB_MASTER );
		$row = [ 'ts_page_id' => $articleId ];

		$dbw->insert( 'translate_svg', $row, __METHOD__, [ 'IGNORE' ] );

		if ( $dbw->affectedRows() === 0 ) {
			// If $dbw->affectedRows() == 0, it already exists,
			// but no particular reason to error out
			return true;
		}

		MessageGroups::clearCache();
		if ( $useJobQueue ) {
			MessageIndexRebuildJob::newJob()->insertIntoJobQueue();
		} else {
			MessageIndex::singleton()->rebuild();
		}
		return true;
	}

	/**
	 * @param LinkTarget $title
	 * @return string|false
	 */
	private function getWikitextForTransclusion( $title ) {
		return MediaWikiServices::getInstance()->getRevisionLookup()
			->getRevisionByTitle( $title )
			->getContent( SlotRecord::MAIN )
			->getWikitextForTransclusion();
	}
}
