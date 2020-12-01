<?php

use BlueSpice\Bookshelf\ILineProcessor;
use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;

/**
 * Provide book hierarchy for a page
 */
class PageHierarchyProvider {
	/** @var string */
	protected $sSourceArticleTitle = '';
	/** @var mixed|string */
	protected $sIndentChar = '*';
	/** @var array|null */
	protected $aSimpleTOC = null;
	/** @var array|null */
	protected $aExtendedTOC = null;
	/** @var Title|null */
	protected $oSourceArticleTitle = null;
	/** @var false|mixed|string */
	protected $sSourceContent = '';
	/** @var BagOStuff|null */
	protected $cache = null;

	/**
	 * @param string $sSourceArticleTitle
	 * @param array $aParams
	 */
	public function __construct( $sSourceArticleTitle, $aParams ) {
		$this->sSourceArticleTitle  = $sSourceArticleTitle;
		$this->sIndentChar          = $aParams['indent-char'];

		$this->oSourceArticleTitle = Title::newFromText( $sSourceArticleTitle );

		if ( $this->oSourceArticleTitle == null ||
			$this->oSourceArticleTitle->exists() == false ) {
			throw new InvalidArgumentException(
				'Provided SourceArticleTitle (' . $sSourceArticleTitle . ') is not valid or Article '
				. 'does not exist!'
			);
		}

		$this->cache = wfGetMainCache();
		$cacheKey = $this->getCacheKey(
			$this->oSourceArticleTitle,
			__METHOD__,
			$aParams
		);

		$this->sSourceContent = $this->cache->getWithSetCallback(
			$cacheKey,
			3600,
			function () use( $aParams ) {
				$oPCP = new BsPageContentProvider();
				$data = $oPCP->getWikiTextContentFor( $this->oSourceArticleTitle, $aParams );
				return $data;
			}
		);

		// Initialize SimpleTOCArray
		$this->createSimpleTOCArrayFromContent();
	}

	/**
	 * N-glton for reusing an instance for a given source article
	 * @param string $sSourceArticleTitle The title of the article containing the hierarchical list
	 * as string. I.e. 'MyNS:Some Hierarcy Article'.
	 * @param array $aParams The parameters used for processing.
	 * @return PageHierarchyProvider Instance of the PageHierarchyProvider for the requested
	 * SourceArticle
	 */
	public static function getInstanceFor( $sSourceArticleTitle, $aParams = [] ) {
		$phpf = MediaWikiServices::getInstance()->getService(
			'BSBookshelfPageHierarchyProviderFactory'
		);
		return $phpf->getInstanceFor( $sSourceArticleTitle, $aParams );
	}

	/**
	 * N-glton for reusing an instance for a given article title.
	 * Looks if article belongs to a source article.
	 * @param string $sArticleTitle The title of the article. I.e. 'MyNS:Some Article'.
	 * @param array $aParams The parameters used for processing.
	 * @return PageHierarchyProvider Instance of the PageHierarchyProvider if source article exists,
	 * null otherwise.
	 */
	public static function getInstanceForArticle( $sArticleTitle, $aParams = [] ) {
		$phpf = MediaWikiServices::getInstance()->getService(
			'BSBookshelfPageHierarchyProviderFactory'
		);
		return $phpf->getInstanceForArticle( $sArticleTitle, $aParams );
	}

	/**
	 * TODO RBV (09.09.11 15:10): locate elsewhere
	 * @return array
	 */
	public function getBookMeta() {
		$content = '' . $this->sSourceContent;
		$aBookMeta = BsTagFinder::find( $content, [ 'bookmeta', 'bs:bookmeta' ] );

		// We only use the first (!) occurence. There shouldn't be more.
		if ( isset( $aBookMeta[0] ) && isset( $aBookMeta[0]['attributes'] ) ) {
			$aBookMeta = $aBookMeta[0]['attributes'];
		} else {
			$aBookMeta = [];
		}

		return $aBookMeta;
	}

	/**
	 * Parses a Wiki List Syntax into a numerated array.
	 * Thanks to Sebastian Ulbricht for parsing logic.
	 * @return array Two dimesional array. [0] => array(1, 3, 2, ...),
	 * [1] => [[Some Wiki Link]]. Or null if an invalid $sourceString was committed.
	 */
	public function getSimpleTOCArray() {
		return $this->aSimpleTOC;
	}

	/**
	 *
	 * @return array
	 */
	public function getExtendedTOCArray() {
		$cacheKey = $this->getCacheKey(
			$this->oSourceArticleTitle,
			__METHOD__
		);
		$extendedToc = $this->cache->get( $cacheKey );

		if ( $extendedToc === false ) {
			$this->ensureExtendedTOCArray();
			$this->cache->set( $cacheKey, $this->aExtendedTOC );
		} else {
			$this->aExtendedTOC = $extendedToc;
		}

		return $this->aExtendedTOC;
	}

	/**
	 * Creates a JSON string representation of the hierarchy
	 * @param array $aParams
	 * @return stdClass The JSON object.
	 */
	public function getExtendedTOCJSON( $aParams = [] ) {
		$cacheKey = $this->getCacheKey(
			$this->oSourceArticleTitle,
			__METHOD__,
			$aParams
		);

		$result = $this->cache->get( $cacheKey );

		if ( $result === false ) {
			$aParams = array_merge(
				[
					'suppress-number-in-text' => false,
				],
				$aParams
			);

			$this->ensureExtendedTOCArray();
			$aTOC = $this->aExtendedTOC;

			$result = $this->getBookRootDataJSON();
			$result['children'] = $this->getChildrenForJSON( $aTOC, $aParams );
			// Objectify
			$result = FormatJson::decode( FormatJson::encode( $result ) );
			$this->cache->set( $cacheKey, $result );
		}

		return $result;
	}

	/**
	 * @param array $toc
	 * @param array $params
	 * @return array
	 */
	protected function getChildrenForJSON( $toc, $params ) {
		$sorted = [];
		foreach ( array_reverse( $toc ) as $child ) {
			if ( !isset( $child['number'] ) ) {
				continue;
			}
			$sorted[$child['number']] = $child;
		}

		ksort( $sorted );

		return $this->addChildrenRecursively( $sorted, 1, false, $params );
	}

	/**
	 * @param array $source
	 * @param int $level
	 * @param false $parentNumber
	 * @param array $params
	 * @return array
	 */
	protected function addChildrenRecursively( $source, $level, $parentNumber = false, $params = [] ) {
		$result = [];
		foreach ( $source as $number => $child ) {
			$number = (string)$number;
			$numberBits = explode( '.', $number );
			if ( count( $numberBits ) !== $level ) {
				continue;
			}
			if ( $parentNumber && strpos( $number, $parentNumber ) !== 0 ) {
				continue;
			}
			$subChildren = $this->addChildrenRecursively( $source, $level + 1, $number, $params );
			if ( $subChildren ) {
				$child['children'] = $subChildren;
			}
			$result[] = $this->formatChildForJSON( $child, $params );
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws ConfigException
	 */
	protected function getBookRootDataJSON() {
		$config = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );
		// TODO: inject from outside!
		$bookDisplayTitle = $config->get( 'BookshelfSupressBookNS' )
			? $this->oSourceArticleTitle->getText()
			: $this->oSourceArticleTitle->getPrefixedText();

		return [
			'text' => $bookDisplayTitle,
			'articleTitle' => $this->oSourceArticleTitle->getPrefixedText(),
			'articleDisplayTitle' => $bookDisplayTitle,
			'articleId' => $this->oSourceArticleTitle->getArticleID(),
			'bookshelf' => [
				'type' => 'wikipage',
				'page_id' => $this->oSourceArticleTitle->getArticleID(),
				'page_namespace' => $this->oSourceArticleTitle->getNamespace(),
				'page_title' => $this->oSourceArticleTitle->getText(),
			]
		];
	}

	/**
	 * @param array $child
	 * @param array $params
	 * @return array
	 */
	protected function formatChildForJSON( $child, $params ) {
		$text = $child['display-title'];
		if ( !$params['suppress-number-in-text'] ) {
			$text = $child['number'] . '. ' . $text;
		}
		return [
			'text' => $text,
			'id' => $child['number'],
			'articleNumber' => $child['number'],
			'articleType' => $child['type'],
			'articleTitle' => $child['title'],
			'articleDisplayTitle' => $child['display-title'],
			'articleId' => $child['article-id'],
			'articleIsRedirect' => $child['is-redirect'],
			'bookshelf' => $child['bookshelf'],
			'children' => isset( $child['children'] ) ? $child['children'] : []
		];
	}

	/**
	 *
	 * @param string $sArticleTitle
	 * @param array $aParams
	 * @return array
	 */
	public function getEntryFor( $sArticleTitle, $aParams = [] ) {
		$oJSON = $this->getExtendedTOCJSON( $aParams );
		return $this->findEntry( $sArticleTitle, $oJSON->children );
	}

	/**
	 *
	 * @param string $sArticleTitle
	 * @param stdClass[] $aEntries
	 * @return stdClass
	 */
	protected function findEntry( $sArticleTitle, $aEntries ) {
		$sNormSearchTitle = str_replace( '_', ' ', $sArticleTitle );

		foreach ( $aEntries as $oEntry ) {
			$sNormTitle = str_replace( '_', ' ', $oEntry->articleTitle );
			if ( $sNormTitle === $sNormSearchTitle ) {
				return $oEntry;
			}
			if ( isset( $oEntry->children ) && !empty( $oEntry->children ) ) {
				$oEntry = $this->findEntry( $sArticleTitle, $oEntry->children );
				if ( $oEntry !== null ) {
					return $oEntry;
				}
			}
		}

		return null;
	}

	/**
	 * Looks for the number in TOC to which the article belongs to. If the article does not belong
	 * to the current book, it analyzes the articels content, looks for the sourcearticle and tries
	 * to find out the number there.
	 *
	 * @param string $sArticleTitle The title of the article.
	 * @param bool $recurseFlag Use $recurseFlag == true if you call it recursively from inside the
	 * function.
	 * @return string The content number in TOC or an empty string if nothing was found.
	 */
	public function getNumberFor( $sArticleTitle, $recurseFlag = false ) {
		$cacheKey = $this->getCacheKey(
			\Title::newFromText( $sArticleTitle ),
			__METHOD__
		);
		$number = $this->cache->get( $cacheKey );

		if ( $number === false ) {

			$oSearchTitle = Title::newFromText( $sArticleTitle );
			// Is this Article in the current array?
			$this->ensureExtendedTOCArray();
			foreach ( $this->aExtendedTOC as $entry ) {
				$oCurrentTitle = Title::newFromText( $entry['title'] );
				if ( $oCurrentTitle instanceof Title
					&& $oCurrentTitle->equals( $oSearchTitle ) ) {
					return $entry['number'];
				}
				if ( $sArticleTitle === $entry['title'] ) {
					return $entry['number'];
				}
			}

			// this prevents from infinite loop (this happens, when nothing is found)
			if ( $recurseFlag == true ) {
				return '';
			}

			// If not, we have got to analyze the Articles content and look for the sourcearticle
			$oPageContentProvider = new BsPageContentProvider();
			$oTitle = Title::newFromText( $sArticleTitle );
			$sContent = $oPageContentProvider->getWikiTextContentFor( $oTitle );

			$aBookshelfTags = BsTagFinder::find(
				$sContent,
				[ 'hierarchicaltoc', 'bookshelf', 'htoc', 'bs:bookshelf' ]
			);
			$phpf = MediaWikiServices::getInstance()->getService(
				'BSBookshelfPageHierarchyProviderFactory'
			);
			$sHTOCTitle = $phpf->findSuitableSourceArticleReference( $aBookshelfTags );
			if ( empty( $sHTOCTitle ) ) {
				return '';
			}
			$oHTOC = $phpf->getInstanceFor( $sHTOCTitle );

			$number = $oHTOC->getNumberFor( $sArticleTitle, 1 );
			$this->cache->set( $cacheKey, $number );
		}

		return $number;
	}

	/**
	 * Find out the extendedTOCArray with all the parents to article title.
	 * @param string $sArticleTitle The title of the page we are looking for
	 * ?do we need? param boolean $recurseFlag Use $recurseFlag == true if you call it recursively
	 * from inside the function.
	 * @param array $aParams
	 * @return array Returns an extendedTOCArray with sourcepagetitle as first array element.
	 *  array (
	 *    'sourcetitle' => 'Inhaltsverzeichnis',
	 *     0 => array (
	 *       'number' => '1',
	 *       'type' => 'wikilink-with-title',
	 *       'article-id' => '1'
	 *       'namespace-text' => '',
	 *       'namespace-id' => 0,
	 *       'revision-id' => 2957,
	 *       'title' => 'Hauptseite',
	 */
	public function getAncestorsFor( $sArticleTitle, $aParams = [] ) {
		$sNumber = $this->getNumberFor( $sArticleTitle );

		// Checks, if article belongs to an own toc.
		// If not, we have got to analyze the Articles content and look for the sourcearticle
		$oPCP = new BsPageContentProvider();
		$oTitle = Title::newFromText( $sArticleTitle );
		$sContent = $oPCP->getWikiTextContentFor( $oTitle, $aParams );

		$aBookshelfTags = BsTagFinder::find(
			$sContent,
			[ 'hierarchicaltoc', 'bookshelf', 'htoc' ]
		);
		$phpf = MediaWikiServices::getInstance()->getService(
			'BSBookshelfPageHierarchyProviderFactory'
		);
		$sHTOCTitle = $phpf->findSuitableSourceArticleReference( $aBookshelfTags );
		if ( $sContent != $sHTOCTitle && $sHTOCTitle != $this->sSourceArticleTitle ) {
			// getting a new toc array instance
			$sTitle = $sHTOCTitle;
			$oHTOC = $phpf->getInstanceFor( $sHTOCTitle );
			$arTOC = $oHTOC->getExtendedTOCArray();
		} else {
			$sTitle = $this->sSourceArticleTitle;
			$arTOC = $this->getExtendedTOCArray();
		}

		$aParents = null;
		$aParents['sourcearticletitle'] = $sTitle;
		$aParents['ancestors'] = [];
		if ( $sNumber == null ) {
			return $aParents;
		}
		// loop through the array and search for given numbers
		// (i.e. 1.1.1, 1.1, 1)
		foreach ( $arTOC as $iIndex => $aEntry ) {
			$sPattern = $sNumber;
			while ( strpos( $sPattern, '.' ) > 0 ) {
				$sPattern = substr( $sPattern, 0, strrpos( $sPattern, '.' ) );
				// find the array with number
				if ( $aEntry['number'] == $sPattern ) {
					$aParents['ancestors'][$iIndex] = $aEntry;
				}
			}
		}

		return $aParents;
	}

	/**
	 * Makes a string of Wikitext links as list and saves it for the set $mSSourceArticleTitle.
	 *
	 * @param array $arHierarchyArray The complete array.
	 * array (
	 *   'sourcetitle' => 'Inhaltsverzeichnis',
	 *   0 => array (
	 *     'number' => '1',
	 *     'type' => 'wikilink-with-title',
	 *     'article-id' => '1',
	 *     'namespace-text' => '',
	 *     'namespace-id' => 0,
	 *     'revision-id' => 2957,
	 *     'title' => 'Hauptseite',
	 *     ...
	 * @param string $sSummary i.e.'Edited by test'.
	 * @param array $arBookMeta
	 *
	 * @return string Returns the string list.
	 */
	public function saveHierarchy( $arHierarchyArray, $sSummary, $arBookMeta = [] ) {
		// the bookmeta info
		$sBookMeta = "<bs:bookmeta \n";
		foreach ( $arBookMeta as $key => $value ) {
			$sBookMeta .= "\t$key=" . "\"" . $value . "\" \n";
		}
		$sBookMeta .= "/>\n";

		// $sLinkList = null;
		$sLinkList = $sBookMeta;
		$util = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' );

		foreach ( $arHierarchyArray as $key => $value ) {
			// making the number to stars 3.2.1 -> ***; 1.4 -> **
			$sStars = str_replace( '.', '',
				preg_replace( '#[0-9]{1,65535}#s', '*', $value['number'] )
			);

			$sArticleTitle         = $value['title'];
			$sArticleDisplayTitle  = $value['display-title'];

			$oArticleTitle = Title::newFromText( $sArticleTitle );
			if ( $oArticleTitle == null ) {
				$sLink = $sArticleTitle;
			} else {
				$linkHelper = $util->getWikiTextLinksHelper( '' )
					->getInternalLinksHelper()->addTargets( [
					$sArticleDisplayTitle => $oArticleTitle
				] );
				$sLink = $linkHelper->getWikitext();

				// Make sure that changes to the 'display-title' are shown on
				// next page load. This should already be done by
				// 'BsCore::addTemplateLinkDependencyByText'
				// in 'Bookshelf:onBookshelfTag' but there it doesn't work :(
				WikiPage::factory( $oArticleTitle )->doPurge();
			}

			$sLinkList .= $sStars . ' ' . $sLink . "\n";
		}

		$oArticle = new Article( $this->oSourceArticleTitle );
		$oStatus = $oArticle->doEdit( $sLinkList, $sSummary, EDIT_DEFER_UPDATES );

		return $oStatus;
	}

	protected function createSimpleTOCArrayFromContent() {
		$this->aSimpleTOC = [];

		/* Thanks to Sebastian Ulbricht! */
		$iLevel = 0;
		$aNumber = [];
		$aLines = explode( "\n", $this->sSourceContent );

		foreach ( $aLines as $sText ) {
			// Is line empty or does not start with a valid indent character?
			if ( empty( $sText ) || $sText[0] != $this->sIndentChar ) {
				continue;
			}

			$iDepth = 0;
			$bIsIndentCharacter = true;
			// Count indent characters '*', ':', or '#' into $intDepth and cut them off
			do {
				if ( isset( $sText[ $iDepth ] ) && $sText[ $iDepth ] == $this->sIndentChar ) {
					$iDepth++;
				} else { $bIsIndentCharacter = false;
				}
			}
			while ( $bIsIndentCharacter );
			$sText = substr( $sText, $iDepth );

			// Skip line processing if empty
			$sText = trim( $sText );
			if ( empty( $sText ) ) { continue;
			}

			if ( $iDepth > $iLevel ) {
				while ( $iDepth > $iLevel ) {
					array_push( $aNumber, 1 );
					$iLevel++;
				}
			} elseif ( $iDepth < $iLevel ) {
				while ( $iDepth < $iLevel ) {
					array_pop( $aNumber );
					$iLevel--;
					$aNumber[ $iLevel - 1 ]++;
				}
			} else {
				$aNumber[ $iLevel - 1 ]++;
			}
			$this->aSimpleTOC[] = [
				'number-array' => $aNumber,
				'text'         => trim( $sText )
			];
		}
	}

	protected function createExtendedTOCArrayFromSimpleTOCArray() {
		$this->aExtendedTOC = [];
		$this->initLineProcessors();

		foreach ( $this->aSimpleTOC as $aListEntry ) {
			$aProcessedEntry = $this->processLine( $aListEntry[ 'text' ] );
			$aProcessedEntry['number'] = implode( '.', $aListEntry['number-array'] );
			$this->aExtendedTOC[] = $aProcessedEntry;
		}
	}

	private function ensureExtendedTOCArray() {
		if ( $this->aExtendedTOC === null ) {
			$this->createExtendedTOCArrayFromSimpleTOCArray();
		}
	}

	/**
	 *
	 * @param \Title $title
	 * @param string $method
	 * @param array $aParams
	 * @return string
	 */
	protected function getCacheKey( $title, $method, $aParams = [] ) {
		$templates = $this->oSourceArticleTitle->getTemplateLinksFrom();

		$templateRevisionIds = [];
		foreach ( $templates as $template ) {
			$templateRevisionIds[] = $template->getLatestRevID();
		}

		return $this->cache->makeKey(
			$title->getPrefixedDBkey(),
			$method,
			md5( serialize( $aParams ) ),
			md5( serialize( [
				$this->oSourceArticleTitle->getLatestRevID(),
				$templateRevisionIds
			] ) )
		);
	}

	/**
	 * @var ILineProcessor[]
	 */
	protected $lineProcessors = [];

	protected function initLineProcessors() {
		$lineParserRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceBookshelfLineProcessors'
		);
		foreach ( $lineParserRegistry->getAllValues() as $lineParserFactory ) {
			if ( !is_callable( $lineParserFactory ) ) {
				continue;
			}
			$this->lineProcessors[] = call_user_func_array( $lineParserFactory, [] );
		}
	}

	/**
	 *
	 * @param string $line
	 * @return array
	 */
	protected function processLine( $line ) {
		foreach ( $this->lineProcessors as $lineProcessor ) {
			if ( $lineProcessor->applies( $line ) ) {
				$result = $lineProcessor->process( $line );
				if ( $lineProcessor->isFinal() ) {
					break;
				}
			}
		}
		return $result->toArray();
	}
}
