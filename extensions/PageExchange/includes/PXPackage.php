<?php
/**
 * Abstract parent class for package classes.
 *
 * @author Yaron Koren
 * @ingroup PX
 */

use MediaWiki\MediaWikiServices;

abstract class PXPackage {

	protected $mName;
	protected $mVersion;
	protected $mGlobalID;
	protected $mDescription;
	protected $mPages = [];
	protected $mPagesString;
	protected $mPagesStatus;
	protected $mPublisher;
	protected $mPublisherURL;
	protected $mAuthor;
	protected $mLanguage;
	protected $mURL;
	protected $mRequiredExtensions = [];
	protected $mRequiredPackages = [];
	protected $mUser;

	public function populateWithData( $fileData, $packageData ) {
		$pagesData = self::getPackageField( 'pages', $fileData, $packageData, false );
		if ( $pagesData !== null ) {
			foreach( $pagesData as $pageData ) {
				$this->mPages[] = PXPage::newFromData( $pageData );
			}
		}
		$this->processPages();
		$this->mVersion = self::getPackageField( 'version', null, $packageData );
		$this->mGlobalID = self::getPackageField( 'globalID', null, $packageData );
		$this->mDescription = self::getPackageField( 'description', null, $packageData, true, true );
		$this->mPublisher = self::getPackageField( 'publisher', $fileData, $packageData );
		$this->mPublisherURL = self::getPackageField( 'publisherURL', $fileData, $packageData );
		if ( substr( $this->mPublisherURL, 0, 4 ) !== 'http' ) {
			$this->mPublisherURL = null;
		}
		$this->mAuthor = self::getPackageField( 'author', $fileData, $packageData );
		$this->mLanguage = self::getPackageField( 'language', $fileData, $packageData );
		$this->mURL = self::getPackageField( 'url', $fileData, $packageData );
		if ( substr( $this->mURL, 0, 4 ) !== 'http' ) {
			$this->mURL = null;
		}
		$this->mRequiredExtensions = self::getPackageField( 'requiredExtensions', $fileData, $packageData );
		$this->mRequiredPackages = self::getPackageField( 'requiredPackages', $fileData, $packageData );
	}

	public static function getSpecialPage( $pageName ) {
		if ( class_exists( 'MediaWiki\Special\SpecialPageFactory' ) ) {
			// MW 1.32+
			return MediaWikiServices::getInstance()
				->getSpecialPageFactory()
				->getPage( $pageName );
		} else {
			return SpecialPageFactory::getPage( $pageName );
		}
	}

	public static function getPackageField( $fieldName, $fileData, $packageData, $escapeHTML = true, $isWikitext = false ) {
		if ( property_exists( $packageData, $fieldName ) ) {
			$value = $packageData->$fieldName;
		} elseif ( $fileData !== null && property_exists( $fileData, $fieldName ) ) {
			$value = $fileData->$fieldName;
		} else {
			return null;
		}
		if ( $isWikitext ) {
			$mwServices = MediaWikiServices::getInstance();
			$parser = $mwServices->getParser();
			$packagesTitle = self::getSpecialPage( 'Packages' )->getPageTitle();
			return $parser->parse( $value, $packagesTitle, new ParserOptions(), false )->getText();
		}
		if ( !$escapeHTML ) {
			return $value;
		}
		if ( is_array( $value ) ) {
			return array_map( 'htmlentities', $value );
		}
		return htmlentities( $value );
	}

	public function getName() {
		return $this->mName;
	}

	public function getGlobalID() {
		return $this->mGlobalID;
	}

	public function getCardBGColor() {
		return [ 255, 255, 255 ];
	}

	public function getCardHeaderBGColor() {
		return '#c8ccd1';
	}

	public function getCardBodyHTML() {
		return '';
	}

	public function displayCard() {
		$bgColor = $this->getCardBGColor();
		$bgHex = $this->colorArrayToHex( $bgColor );
		$headerBGColor = $this->darkenColor( $bgColor );
		$headerBGHex = $this->colorArrayToHex( $headerBGColor );
		//$headerBGHex = $this->getCardHeaderBGColor();
		$borderColor = $this->darkenColor( $headerBGColor );
		$borderHex = $this->colorArrayToHex( $borderColor );
		$packageHTML = <<<END
<div class="pageExchangeCardWrapper">
<div class="pageExchangeCard" style="background: $bgHex; border: 1px $borderHex solid;">
<div class="pageExchangeCardHeader" style="background: $headerBGHex; border: 1px $borderHex solid;">
{$this->mName}
</div>

END;
		$packageHTML .= $this->getCardBodyHTML();
		$packageHTML .= "</div>\n</div>\n";

		return $packageHTML;
	}

	protected function colorArrayToHex( $colors ) {
		return sprintf("#%02x%02x%02x", $colors[0], $colors[1], $colors[2] );
	}

	protected function darkenColor( $colors ) {
		$redDifference = 256 - $colors[0];
		$greenDifference = 256 - $colors[1];
		$blueDifference = 256 - $colors[2];
		$differenceSum = $redDifference + $greenDifference + $blueDifference;
		return [
			round( $colors[0] - 120 * ( $redDifference / $differenceSum ) ),
			round( $colors[1] - 120 * ( $greenDifference / $differenceSum ) ),
			round( $colors[2] - 120 * ( $blueDifference / $differenceSum ) ),
		];
	}

	public function displayAttribute( $attrMsg, $value, $hasError = false ) {
		if ( $value == '' ) {
			return '';
		}
		$text = '<p>';
		if ( $hasError ) {
			$text .= new OOUI\IconWidget( [
				'icon' => 'error',
				'flags' => 'warning',
				'title' => 'Error'
			] ) . ' ';
		}
		if ( $attrMsg != '' ) {
			$text .= '<strong>' . wfMessage( $attrMsg )->parse() . '</strong> ';
		}
		if ( is_array( $value ) ) {
			$text .= implode( ', ', $value );
		} else {
			$text .= $value;
		}
		$text .= "</p>\n";
		return $text;
	}

	public function displayDescription() {
		if ( $this->mDescription == null ) {
			return '<p><em>(No description)</em></p>';
		}

		return $this->mDescription;
	}

	public function displayWebsite( $showURL = false ) {
		if ( $this->mURL == null ) {
			return '';
		}
		$linkText = $showURL ? $this->mURL : 'Link';
		$link = Html::element( 'a', [ 'href' => $this->mURL ], $linkText );
		return $this->displayAttribute( 'pageexchange-package-website', $link );
	}

	public function displayInfoMessage( $msg ) {
		$text =<<<END
<table class="pageExchangeInfoMessage">
<tr>
<td>

END;
		$text .= new OOUI\IconWidget( [
			'icon' => 'notice',
			//'title' => 'Notice'
		] );

		$text .=<<<END
</td>
<td>
<div>$msg</div>
</td>
</tr>
</table>

END;

		return $text;
	}

	public function displayWarningMessage( $msg ) {
		$text =<<<END
<table class="pageExchangeWarningMessage">
<tr>
<td>

END;
		$text .= new OOUI\IconWidget( [
			'icon' => 'notice', //'warning',
			//'title' => 'Notice'
		] );

		$text .=<<<END
</td>
<td>
<div class="error">$msg</div>
</td>
</tr>
</table>

END;

		return $text;
	}

	abstract public function processPages();

	abstract public function getFullHTML();

	public function getPackageLink( $linkText, $query ) {
		$packagesTitle = self::getSpecialPage( 'Packages' )->getPageTitle();
		$packageURL = $packagesTitle->getLocalURL( $query );
		return Html::element( 'a', [ 'href' => $packageURL ], $linkText );
	}

	public function logAction( $actionName, User $user ) {
		$log = new LogPage( 'pageexchange', false );

		$packagesTitle = self::getSpecialPage( 'Packages' )->getPageTitle();
		$logParams = [
			$this->mName,
			$this->mPublisher
		];

		// Every log entry requires an associated title; these
		// actions don't involve an actual page, so we just use
		// Special:Packages as the title.
		$log->addEntry( $actionName, $packagesTitle, '', $logParams, $user );
	}

}
