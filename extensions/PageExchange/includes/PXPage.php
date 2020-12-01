<?php

/**
 * Class for a single page (and, possibly, an associated file) within a
 * package.
 *
 * @author Yaron Koren
 * @ingroup PX
 */

class PXPage {

	private $mName;
	private $mNamespace;
	private $mNamespaceConstant;
	private $mURL;
	private $mFileURL;
	private $mLocalTitle;
	private $mLocalTitleExists;
	private $mLink;
	private $mLocalLink;

	public static function newFromData( $packagePageData ) {
		$page = new PXPage();
		$page->mName = $packagePageData->name;
		if ( property_exists( $packagePageData, 'namespace' ) ) {
			$page->mNamespaceConstant = $packagePageData->namespace;
		} else {
			// NS_MAIN is the default.
			$page->mNamespaceConstant = 'NS_MAIN';
		}
		if ( defined( $page->mNamespaceConstant ) ) {
			$page->mNamespace = constant( $page->mNamespaceConstant );
			$page->mLocalTitle = Title::makeTitleSafe( $page->mNamespace, $page->mName );
			if ( $page->mLocalTitle == null ) {
				return null;
			}
			$page->mLocalLink = Linker::link( $page->mLocalTitle );
			$page->mLocalTitleExists = $page->mLocalTitle->exists();
			$pageFullName = $page->mLocalTitle->getFullText();
			if ( $page->mNamespace == NS_FILE ) {
				$page->mFileURL = $packagePageData->fileURL;
				if ( substr( $page->mFileURL, 0, 4 ) !== 'http' ) {
					return null;
				}
			}
		} else {
			// If the specified namespace is not defined on this
			// wiki, we'll just do the best we can.
			$page->mNamespace = $page->mNamespaceConstant;
			$page->mLocalTitleExists = false;
			$pageFullName = $page->mNamespace . ':' . $page->mName;
			$page->mLocalLink = $pageFullName;
		}
		$page->mURL = $packagePageData->url;
		if ( substr( $page->mURL, 0, 4 ) !== 'http' ) {
			return null;
		}
		if ( $page->mNamespace == NS_FILE ) {
			$page->mLink = Html::element( 'a', [ 'href' => $page->mFileURL ], $pageFullName ) . ' (' .
				Html::element( 'a', [ 'href' => $page->mURL ], 'text contents' ) . ')';
		} else {
			$page->mLink = Html::element( 'a', [ 'href' => $page->mURL ], $pageFullName );
		}

		return $page;
	}

	public function getName() {
		return $this->mName;
	}

	public function getNamespace() {
		return $this->mNamespace;
	}

	public function getLocalTitle() {
		return $this->mLocalTitle;
	}

	public function localTitleExists() {
		return $this->mLocalTitleExists;
	}

	public function getLink() {
		return $this->mLink;
	}

	public function getLocalLink() {
		return $this->mLocalLink;
	}

	public function getURL() {
		return $this->mURL;
	}

	public function setURL( $url ) {
		$this->mURL = $url;
	}

	public function getFileURL() {
		return $this->mFileURL;
	}

	public function getContentType() {
		if ( $this->mNamespace !== NS_MEDIAWIKI ) {
			return null;
		}
		if ( substr( $this->mName, -3 ) == '.js' ) {
			return 'JavaScript';
		}
		if ( substr( $this->mName, -4 ) == '.css' ) {
			return 'CSS';
		}
		return null;
	}

	public function getRemoteContents() {
		return PXPackageFile::getWebPageContents( $this->mURL );
	}

	public function getLocalContents() {
		if ( !$this->mLocalTitleExists ) {
			return null;
		}

		$wikiPage = new WikiPage( $this->mLocalTitle );
		$content = $wikiPage->getContent();
		if ( $content !== null ) {
			return $content->getNativeData();
		} else {
			return null;
		}

	}

	/**
	 * Create or update a wiki page, and its associated file, if there is
	 * one.
	 */
	public function createOrUpdateWikiPage( $user, $packageName, $isInstall ) {
		// Most of the work - including creating the associated file,
		// if there is one - is actually done by a job.
		$editSummaryMsg = $isInstall ? 'pageexchange-installpackage' : 'pageexchange-updatepackage';
		$editSummary = wfMessage( $editSummaryMsg )->rawParams( $packageName )->inContentLanguage()->parse();

		$jobs = [];
		$params = [
			'page_url' => $this->mURL,
			'user_id' => $user->getID(),
			'edit_summary' => $editSummary,
			'content_type' => $this->getContentType()
		];
		if ( $this->mNamespace == NS_FILE ) {
			$params['file_url'] = $this->mFileURL;
		}
		$jobs[] = new PXCreatePageJob( $this->mLocalTitle, $params );
		JobQueueGroup::singleton()->push( $jobs );
	}

	/**
	 * Delete a wiki page, and its associated file, if there is one.
	 */
	public function deleteWikiPage( $user, $packageName, $isUninstall ) {
		$wikiPage = new WikiPage( $this->mLocalTitle );
		$editSummaryMsg = $isUninstall ? 'pageexchange-uninstallpackage' : 'pageexchange-updatepackage';
		$editSummary = wfMessage( $editSummaryMsg )->rawParams( $packageName )->inContentLanguage()->parse();
		$wikiPage->doDeleteArticleReal( $editSummary );
		if ( $this->mNamespace == NS_FILE ) {
			$file = wfLocalFile( $this->mLocalTitle );
			$file->delete( $editSummary );
		}
	}

	public function getPageData() {
		$pageData = [
			'name' => $this->mName,
			'namespace' => $this->mNamespaceConstant,
			'url' => $this->mURL
		];
		if ( $this->mFileURL !== null ) {
			$pageData['fileURL'] = $this->mFileURL;
		}
		return $pageData;
	}
}
