<?php

class MintyDocsCopy extends MintyDocsPublish {

	private $mParentTitle;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct( 'MintyDocsCopy' );

		self::$mNoActionNeededMessage = "None of the pages in this manual need to be copied.";
		self::$mEditSummaryMsg = "mintydocs-copy-editsummary";
		self::$mSuccessMsg = "mintydocs-copy-success";
		self::$mSinglePageMsg = "mintydocs-copy-singlepage";
		self::$mButtonMsg = "mintydocs-copy-button";
	}

	function execute( $query ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$req = $this->getRequest();

		// Check permissions.
		if ( !$this->getUser()->isAllowed( 'mintydocs-administer' ) ) {
			$this->displayRestrictionError();
			return;
		}

		$setVersion = $req->getCheck( 'mdSetVersion' );
		$publish = $req->getCheck( 'mdPublish' );
		if ( $setVersion || $publish ) {
			// Guard against cross-site request forgeries (CSRF).
			$validToken = $this->getUser()->matchEditToken( $req->getVal( 'csrf' ), $this->getName() );
			if ( !$validToken ) {
				$text = "This appears to be a cross-site request forgery; canceling.";
				$out->addHTML( $text );
				return;
			}
		}

		try {
			$title = $this->getTitleFromQuery( $query );
		} catch ( Exception $e ) {
			$out->addHTML( $e->getMessage() );
			return;
		}
		$this->mParentTitle = $title;

		$this->targetVersion = $req->getVal( 'target_version' );

		if ( $publish ) {
			$this->publishAll();
			return;
		}

		if ( $setVersion ) {
			$this->displayMainForm( $title );
			return;
		}

		$this->displayVersionSelector( $title );
	}

	function displayVersionSelector( $title ) {
		$out = $this->getOutput();

		$mdPage = MintyDocsUtils::pageFactory( $title );
		list( $productStr, $versionStr ) = $mdPage->getProductAndVersionStrings();
		$productPage = Title::newFromText( $productStr );
		$product = new MintyDocsProduct( $productPage );
		$versions = $product->getChildrenPages();
		$versionStrings = [];
		foreach ( $versions as $versionTitle ) {
			list( $curProductStr, $curVersionStr ) = explode( '/', $versionTitle->getText() );
			if ( $curVersionStr !== $versionStr ) {
				$versionStrings[] = $curVersionStr;
			}
		}

		$optionsHtml = '';
		foreach ( $versionStrings as $curVersionStr ) {
			$optionsHtml .= Html::element(
				'option', [
					'value' => $curVersionStr
				], $curVersionStr
			) . "\n";
		}

		$text = Html::hidden( 'csrf', $this->getUser()->getEditToken( $this->getName() ) ) . "\n";
		$text .= "Select version to copy pages to: ";
		$text .= Html::rawElement( 'select', [ 'name' => 'target_version' ], $optionsHtml ) . "\n";
		$text .= '<p>' . Html::input( 'mdSetVersion', $this->msg( 'apisandbox-continue' )->text(), 'submit' ) . "</p>\n";
		$text = Html::rawElement( 'form', [ 'method' => 'post' ], $text );

		$out->addHtml( $text );
	}

	function displayPageParents( $mdPage ) {
		$targetTitle = $this->generateTargetTitle( $mdPage->getTitle()->getText() );
		$text = '<p>These will be copied to the location ' . Linker::link( $targetTitle ) . ".</p>\n";
		$text .= Html::hidden( 'target_version', $this->targetVersion );
		return $text;
	}

	function generateSourceTitle( $sourcePageName ) {
		return Title::newFromText( $sourcePageName, $this->mParentTitle->getNamespace() );
	}

	function generateTargetTitle( $targetPageName ) {
		$pageElements = explode( '/', $targetPageName, 3 );
		if ( count( $pageElements ) == 3 ) {
			list( $product, $version, $manualAndTopic ) = $pageElements;
			$targetPageName = "$product/" . $this->targetVersion . "/$manualAndTopic";
		} else {
			// Probably 2 - product and version. We just need the product.
			$product = $pageElements[0];
			$targetPageName = "$product/" . $this->targetVersion;
		}
		return Title::newFromText( $targetPageName, $this->mParentTitle->getNamespace() );
	}

	function generateParentTargetTitle( $fromParentTitle ) {
		$fromParentPageName = $fromParentTitle->getFullText();
		return $this->generateTargetTitle( $fromParentPageName );
	}

	function overwritingIsAllowed() {
		return true;
	}

	function validateTitle( $title ) {
		$mdPage = MintyDocsUtils::pageFactory( $title );
		if ( !$mdPage instanceof MintyDocsManual ) {
			throw new MWException( 'Page must be a MintyDocs manual.' );
		}
	}

}
