<?php

namespace BlueSpice\Bookshelf;

use BsCore;
use BsPARAMTYPE;
use BsValidator;
use Config;
use Exception;
use FormatJson;
use Html;
use Linker;
use MediaWiki\MediaWikiServices;
use PageHierarchyProvider;
use Parser;
use Title;
use ViewTagError;
use ViewTagErrorList;

/**
 * Temp class, until all tags are implemented using TagRegistry
 */
class TagProcessor {
	/** @var Config */
	private $config;

	/**
	 * TagProcessor constructor.
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * @return Config
	 */
	protected function getConfig() {
		return $this->config;
	}

	/**
	 * @param string $sInput Content of $lt;bs:collectiontoc /&gt; from MediaWiki Framework
	 * @param array $aAttributes Attributes of &lt;bs:collectiontoc /&gt; from MediaWiki Framework
	 * @param Parser $oParser Parser object from MediaWiki Framework
	 * @return string
	 */
	public function onBookshelfTag( $sInput, $aAttributes, $oParser ) {
		$sSourceArticleName =
			BsCore::sanitizeArrayEntry( $aAttributes, 'src', '', BsPARAMTYPE::STRING );
		$sSourceArticleName =
			BsCore::sanitizeArrayEntry( $aAttributes, 'book', $sSourceArticleName,
				BsPARAMTYPE::STRING );
		$iTreePanelWidth =
			BsCore::sanitizeArrayEntry( $aAttributes, 'width', 300, BsPARAMTYPE::INT );
		$iTreePanelHeight =
			BsCore::sanitizeArrayEntry( $aAttributes, 'height', 400, BsPARAMTYPE::INT );
		$sTreePanelFloat =
			BsCore::sanitizeArrayEntry( $aAttributes, 'float', '', BsPARAMTYPE::STRING );
		$sTreePanelStyle =
			BsCore::sanitizeArrayEntry( $aAttributes, 'style', '', BsPARAMTYPE::STRING );

		$oErrorListView = new ViewTagErrorList( $this );
		if ( !empty( $sTreePanelStyle ) ) {
			$sTreePanelStyle = ' style="' . $sTreePanelStyle . '"';
		} else {
			if ( $sTreePanelFloat == 'right' || $sTreePanelFloat == 'left' ) {
				$sMarginSide = ( $sTreePanelFloat == 'right' ) ? 'left' : 'right';
				$sTreePanelStyle = ' style="margin-' . $sMarginSide . ': 10px; float: '
					. $sTreePanelFloat . '"';
			} elseif ( !empty( $sTreePanelFloat ) ) {
				$oErrorListView->addItem(
					new ViewTagError( 'float: '
						. wfMessage( 'bs-bookshelf-tagerror-attribute-float-not-valid' )->text()
					)
				);
			}
		}

		$oTreePanelWidthValidatorResponse =
			BsValidator::isValid( 'PositiveInteger', $iTreePanelWidth, [ 'fullResponse' => true ] );
		if ( $oTreePanelWidthValidatorResponse->getErrorCode() ) {
			$oErrorListView->addItem(
				new ViewTagError( 'width: ' .
					wfMessage( 'bs-bookshelf-positive-integer-validation-not-approved' )
						->text()
				)
			);
		}

		$oTreePanelHeightValidatorResponse =
			BsValidator::isValid( 'PositiveInteger', $iTreePanelHeight,
				[ 'fullResponse' => true ] );
		if ( $oTreePanelHeightValidatorResponse->getErrorCode() ) {
			$oErrorListView->addItem(
				new ViewTagError( 'height: '
					. wfMessage( 'bs-bookshelf-positive-integer-validation-not-approved' )
						->text()
				)
			);
		}

		if ( empty( $sSourceArticleName ) ) {
			$oErrorListView->addItem(
				new ViewTagError( wfMessage( 'bs-bookshelf-tagerror-no-attribute-given' )->text() )
			);
		}

		if ( $oErrorListView->hasItems() ) {
			return $oErrorListView->execute();
		}

		$oCurrentTitle = $oParser->getTitle();
		$sTitle = $oCurrentTitle->getPrefixedText();
		$sNumber = '';
		$bHasChildren = false;
		$sDisplayTitle = $sTitle;

		try {
			$oPHP = PageHierarchyProvider::getInstanceFor(
				$sSourceArticleName,
				[ 'follow-redirects' => true ]
			);
			$oJSTreeJSON = $oPHP->getExtendedTOCJSON();
			$oEntry = $oPHP->getEntryFor( $sTitle );
			if ( $oEntry !== null ) {
				$sNumber = $oEntry->articleNumber;

				if ( isset( $oEntry->articleDisplayTitle ) ) {
					$sDisplayTitle = $oEntry->articleDisplayTitle;
				}
				// Fallback in case of no display title but subpage
				if ( str_replace( '_', ' ', $sDisplayTitle ) === $sTitle && $oCurrentTitle->isSubpage() ) {
					$sDisplayTitle = basename( $oCurrentTitle->getText() );
				}
				$bHasChildren = isset( $oEntry->children ) && !empty( $oEntry->children );
			}
		} catch ( Exception $e ) {
			$oErrorListView->addItem(
				new ViewTagError(
					wfMessage(
						'bs-bookshelf-tagerror-article-title-not-valid',
						$sSourceArticleName
					)->plain()
				)
			);
			return $oErrorListView->execute();
		}

		$aAdditionalAttribs = [];
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSBookshelfTagBeforeRender',
			[
				&$sSourceArticleName,
				$oJSTreeJSON,
				&$sNumber,
				&$aAdditionalAttribs
			]
		);

		$oParserOut = $oParser->getOutput();

		$oParserOut->setProperty( 'bs-bookshelf-sourcearticle', $sSourceArticleName );
		$oParserOut->setProperty( 'bs-bookshelf-number', $sNumber );
		$oParserOut->setProperty( 'bs-bookshelf-display-title', $sDisplayTitle );

		if ( $this->getConfig()->get( 'BookshelfTitleDisplayText' ) ) {
			$sTitleText = $sDisplayTitle;
			if ( $sNumber ) {
				$sTitleText = '<span class="bs-chapter-number">' . $sNumber . '. </span>' . $sTitleText;
			}
			if ( $sTitleText !== $sTitle ) {
				$oParserOut->setTitleText( $sTitleText );
			}
		}

		// This seems to better place than "BeforePageDisplay" hook
		$oParserOut->addModules( 'ext.bluespice.bookshelf' );
		$oParserOut->addModuleStyles( 'ext.bluespice.bookshelf.styles' );

		$aAttribs = [
			'class' => 'bs-bookshelf-toc',
			'data-bs-src' => $sSourceArticleName,
			'data-bs-has-children' => $bHasChildren,
			'data-bs-tree' => FormatJson::encode( $oJSTreeJSON )
		];

		if ( !empty( $sNumber ) ) {
			$aAttribs['data-bs-number'] = $sNumber;
		}

		$aAttribs = array_merge( $aAttribs, $aAdditionalAttribs );

		return Html::element( 'div', $aAttribs );
	}

	/**
	 * @param string $sContent Content of $lt;booklist /&gt; from MediaWiki Framework
	 * @param array $aAttributes Attributes of &lt;booklist /&gt; from MediaWiki Framework
	 * @param Parser $oParser Parser object from MediaWiki Framework
	 * @return string
	 */
	public function onBookshelfListTag( $sContent, $aAttributes, $oParser ) {
		$oParser->disableCache();
		if ( empty( $aAttributes['filter'] ) ) {
			return 'No filter set.';
		}

		$aFilters = explode( '|', trim( $aAttributes['filter'] ) );
		$aParsedFilters = [];
		foreach ( $aFilters as $sKeyValuePair ) {
			$aParts = explode( ':', trim( $sKeyValuePair ), 2 );
			if ( count( $aParts ) < 2 ) { continue;
			}
			$sKey   = trim( $aParts[0] );
			$sValue = trim( $aParts[1] );
			$aParsedFilters[$sKey] = $sValue;
		}

		// TODO RBV (19.12.11 16:32): error message if invalid filter
		$aBooks = [];
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'page',
			[ 'page_id', 'page_title' ],
			[ 'page_namespace' => NS_BOOK ],
			__METHOD__,
			[ 'ORDER BY' => 'page_id' ]
		);

		foreach ( $res as $row ) {
			$oSourceTitle = Title::newFromID( $row->page_id );
			if ( $oSourceTitle === null ) { continue;
			}

			$oPHProvider  = PageHierarchyProvider::getInstanceFor(
				$oSourceTitle->getPrefixedText()
			);
			$aBookMeta    = $oPHProvider->getBookMeta();
			if ( empty( $aBookMeta ) ) {
				// No tag found?
				continue;
			}

			$aMeta = $aBookMeta;

			$bMatch = false;
			foreach ( $aParsedFilters as $sKey => $sValue ) {
				if ( empty( $aMeta[$sKey] ) ) { continue;
				}
				if ( strpos( $aMeta[$sKey], $sValue ) !== false ) {
					$bMatch = true;
				} else {
					$bMatch = false;
				}
			}
			if ( !$bMatch ) {
				// Not what we are looking for
				continue;
			}

			$aBooks[] = [
				'link' => Linker::link( $oSourceTitle ),
				'meta' => $aMeta
			];
		}

		// TODO RBV (20.12.11 10:30): Display meta in tooltip...
		// TODO: allow PDF links to be injected
		$sOut = '<ul>';
		foreach ( $aBooks as $aBook ) {
			$sOut .= '<li>' . $aBook['link'] . '</li>';
		}
		$sOut .= '</ul>';

		return $sOut;
	}

	/**
	 * Renders a generic node tag that gets evaluated by ... future code or clientside extensions
	 * @param string $sInput
	 * @param array $aAttributes
	 * @param Parser $oParser
	 * @return string The HTML to be send to the client
	 */
	public function onBookshelfNodeTag( $sInput, $aAttributes, $oParser ) {
		// Taken directly from Html::element implementation. This allows us to
		// use Html::rawElement below and yet give a hook handler the chance to
		// add childs to the content
		$sNodeText = strtr( $aAttributes['text'], [
			'&' => '&amp;',
			'<' => '&lt;'
		] );

		$aAttribs = [
			'class' => 'bs-bookshelf-node'
		];

		foreach ( $aAttributes as $sAttributeName => $sAttributeValue ) {
			$aAttribs['data-bs-node-' . $sAttributeName] = $sAttributeValue;
		}

		$sElement = 'span';
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSBookshelfNodeTag',
			[ strtolower( $aAttributes['type'] ), &$sNodeText, &$aAttribs, &$sElement, $oParser ]
		);

		$sOutput = Html::rawElement( $sElement, $aAttribs, $sNodeText );

		return $sOutput;
	}
}
