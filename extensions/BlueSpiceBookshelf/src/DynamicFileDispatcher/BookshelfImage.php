<?php

namespace BlueSpice\Bookshelf\DynamicFileDispatcher;

use BlueSpice\DynamicFileDispatcher\ArticlePreviewImage;
use BlueSpice\DynamicFileDispatcher\Params;
use MediaWiki\MediaWikiServices;

class BookshelfImage extends ArticlePreviewImage {

	/**
	 * TODO: remove when in base class
	 */
	const MODULE_NAME = 'articlepreviewimage';

	/*
	 * @var \PageHierarchyProvider
	 */
	protected $pageHierarchyProvider = null;

	/**
	 *
	 * @return StaticCoverImage|ImageExternal
	 */
	public function getFile() {
		$title = \Title::newFromText( $this->params[static::TITLETEXT] );
		$this->pageHierarchyProvider = \PageHierarchyProvider::getInstanceFor(
			$title->getPrefixedText()
		);
		$meta = $this->pageHierarchyProvider->getBookMeta();

		$coverpage = '';
		if ( isset( $meta['bookshelfimage'] ) ) {
			$coverpage = $meta['bookshelfimage'];
		}

		if ( empty( $coverpage ) ) {
			return new ImageExternal(
				$this,
				$this->buildFallbackURL(),
				$this->getContext()->getUser()
			);
		}

		$parsedUrl = wfParseUrl( $coverpage );
		if ( $parsedUrl !== false ) {
			return new ImageExternal(
				$this,
				$coverpage,
				$this->getContext()->getUser()
			);
		}

		$file = \RepoGroup::singleton()->findFile( $coverpage );
		if ( $file instanceof \File ) {
			// TODO: Add "transformable" RepoFile to BSF
			return new ImageExternal(
				$this,
				$file->createThumb(
					$this->params[static::WIDTH],
					$this->params[static::HEIGHT]
				),
				$this->getContext()->getUser()
			);
		}

		return new StaticCoverImage( $this );
	}

	/**
	 *
	 * @return string
	 */
	protected function buildFallbackURL() {
		$dfdUrlBuilder = MediaWikiServices::getInstance()
			->getService( 'BSDynamicFileDispatcherUrlBuilder' );

		$extendedToc = $this->pageHierarchyProvider->getExtendedTOCArray();
		$firstPage = $extendedToc[0];
		$title = \Title::newFromText( $firstPage['title'] );

		return $dfdUrlBuilder->build( new Params( [
			Params::MODULE => static::MODULE_NAME,
			static::TITLETEXT => $title->getPrefixedText(),
			static::HEIGHT => $this->params[static::HEIGHT],
			static::WIDTH => $this->params[static::WIDTH],
		] ) );
	}

}
