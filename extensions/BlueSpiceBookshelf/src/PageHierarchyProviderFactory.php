<?php

namespace BlueSpice\Bookshelf;

use BsPageContentProvider;
use BsTagFinder;
use DynamicPageHierarchyProvider;
use InvalidArgumentException;
use MWException;
use PageHierarchyProvider;
use Title;

class PageHierarchyProviderFactory {
	/** @var array */
	protected static $prInstances = [];

	/**
	 * N-glton for reusing an instance for a given source article
	 * @param string $sSourceArticleTitle The title of the article containing the hierarchical list
	 * as string. I.e. 'MyNS:Some Hierarcy Article'.
	 * @param array $aParams The parameters used for processing.
	 * @return PageHierarchyProvider Instance of the PageHierarchyProvider for the requested
	 * SourceArticle
	 */
	public function getInstanceFor( $sSourceArticleTitle, $aParams = [] ) {
		$aParams = [ 'indent-char' => '*' ] + $aParams;
		$sParamHash = md5( $aParams['indent-char'] );
		$sInstanceKey = md5( $sSourceArticleTitle . $sParamHash );
		if ( !isset( self::$prInstances[ $sInstanceKey ] )
			|| self::$prInstances[ $sInstanceKey ] == null ) {

			$class = $this->getClassForParams( $aParams );
			self::$prInstances[ $sInstanceKey ] =
				new $class( $sSourceArticleTitle, $aParams );
		}
		return self::$prInstances[ $sInstanceKey ];
	}

	/**
	 * N-glton for reusing an instance for a given article title.
	 * Looks if article belongs to a source article.
	 * @param string $sArticleTitle The title of the article. I.e. 'MyNS:Some Article'.
	 * @param array $aParams The parameters used for processing.
	 * @return PageHierarchyProvider Instance of the PageHierarchyProvider if source article exists,
	 * null otherwise.
	 */
	public function getInstanceForArticle( $sArticleTitle, $aParams = [] ) {
		$aParams = [ 'indent-char' => '*' ] + $aParams;

		// Checks, if article belongs to an own toc.
		// If not, we have got to analyze the Articles content and look for the sourcearticle
		$oPageContentProvider = new BsPageContentProvider();
		$oTitle = Title::newFromText( $sArticleTitle );
		$sContent = $oPageContentProvider->getWikiTextContentFor( $oTitle, $aParams );

		// TODO: Make tag recognition more flexible.
		// I. e. BsTagFinder for finding several tags <book />, etc.
		$aBookshelfTags = BsTagFinder::find(
			$sContent,
			[ 'hierarchicaltoc', 'bookshelf', 'htoc', 'bs:bookshelf' ]
		);

		$sHTOCTitle = $this->findSuitableSourceArticleReference( $aBookshelfTags );
		if ( empty( $sHTOCTitle ) ) {
			// nothing to do if no tag is found
			throw new InvalidArgumentException(
				'Provided Article (' . $sArticleTitle . ') does not contain reference to sourcearticle!'
			);
		} else {
			$oHTOC = $this->getInstanceFor( $sHTOCTitle, $aParams );
		}

		return $oHTOC;
	}

	/**
	 *
	 * @param array $aTags
	 * @return string The prefixed text of the source title or an empty string if nothing suitable was
	 * found
	 */
	public function findSuitableSourceArticleReference( $aTags ) {
		foreach ( $aTags as $aTag ) {
			if ( empty( $aTag ) ) { continue;
			}
			$aAttributes = $aTag['attributes'];
			$sSourceArticleTitle = '';
			if ( isset( $aAttributes['sourcepagetitle'] ) ) {
				$sSourceArticleTitle = $aAttributes['sourcepagetitle'];
			}
			if ( isset( $aAttributes['book'] ) ) {
				$sSourceArticleTitle = $aAttributes['book'];
			}
			if ( isset( $aAttributes['src'] ) ) {
				$sSourceArticleTitle = $aAttributes['src'];
			}
			if ( !empty( $sSourceArticleTitle ) ) {
				return $sSourceArticleTitle;
			}
		}
		return '';
	}

	/**
	 * Get book type for Title
	 *
	 * @param Title $title
	 * @return string
	 * @throws MWException
	 */
	public function getBookTypeFromTitle( Title $title ) {
		if ( $title->getNamespace() === NS_USER ) {
			return 'user_book';
		}
		if ( $title->getNamespace() === NS_BOOK ) {
			return 'ns_book';
		}

		throw new MWException( 'Book type cannot be determined for given title' );
	}

	private function getClassForParams( array $params ) {
		if ( isset( $params['book_type'] ) && $params['book_type'] === 'local_storage' ) {
			return DynamicPageHierarchyProvider::class;
		}

		return PageHierarchyProvider::class;
	}
}
