<?php

use MediaWiki\MediaWikiServices;

class DynamicPageHierarchyProvider extends PageHierarchyProvider {

	/**
	 * @param string $title
	 * @param array $params
	 * @throws MWException
	 */
	public function __construct( $title, $params ) {
		$this->sSourceArticleTitle  = $title;
		$this->sIndentChar          = $params['indent-char'];

		$this->cache = wfGetMainCache();
		if ( !isset( $params['content'] ) ) {
			throw new MWException( 'Parameter \"content\" is required"' );
		}
		$this->sSourceContent = $params['content'];

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
		$aParams['book_type'] = 'local_storage';
		$phpf = MediaWikiServices::getInstance()->getService(
			'BSBookshelfPageHierarchyProviderFactory'
		);
		return $phpf->getInstanceFor( $sSourceArticleTitle, $aParams );
	}

	/**
	 * @return array
	 */
	protected function getBookRootDataJSON() {
		return [
			'text' => $this->sSourceArticleTitle,
			'articleTitle' => $this->sSourceArticleTitle,
			'articleDisplayTitle' => $this->sSourceArticleTitle,
			'articleId' => 0,
			'bookshelf' => [
				'type' => 'text',
				'nomenu' => true,
			],
		];
	}

	/**
	 *
	 * @inheritDoc
	 */
	protected function getCacheKey( $title, $method, $aParams = [] ) {
		return $this->cache->makeKey(
			$this->sSourceArticleTitle,
			$method,
			md5( serialize( $aParams ) )
		);
	}
}
