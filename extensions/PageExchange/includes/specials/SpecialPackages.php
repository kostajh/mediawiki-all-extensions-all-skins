<?php
/**
 * Class for the page Special:Packages
 *
 * @author Yaron Koren
 */

class SpecialPackages extends SpecialPage {

	private $mInstalledExtensions = [];
	private $mInstalledPackages = [];
	private $mMatchingPackages = [];
	private $mNonMatchingPackages = [];
	private $mUnusablePackages = [];

	public function __construct() {
		parent::__construct( 'Packages', 'pageexchange' );
	}

	public function execute( $query ) {
		$this->checkPermissions();

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		$this->setHeaders();
		$out->enableOOUI();
		$out->addModules( [ 'ext.pageexchange' ] );
		$out->addModuleStyles( [ 'oojs-ui.styles.icons-alerts' ] );

		// Extensions loaded via wfLoadExtension().
		$registeredExtensions = ExtensionRegistry::getInstance()->getAllThings();
		foreach ( $registeredExtensions as $extName => $extData ) {
			// Make the names "space-insensitive".
			$extensionName = str_replace( ' ', '', $extName );
			$this->mInstalledExtensions[] = $extensionName;
		}

		// For MW 1.35+, this only gets extensions loaded the old way, via
		// include_once() or require_once().
		$extensionCredits = $this->getConfig()->get( 'ExtensionCredits' );
		foreach ( $extensionCredits as $group => $exts ) {
			foreach ( $exts as $ext ) {
				// Make the names "space-insensitive".
				$extensionName = str_replace( ' ', '', $ext['name'] );
				$this->mInstalledExtensions[] = $extensionName;
			}
		}

		$packageName = $request->getVal( 'name' );
		$fileNum = $request->getVal( 'fileNum' );

		if ( $packageName !== null && $fileNum !== null ) {
			$out->setPageTitle( $this->msg( 'packages' )->parse() . ': ' . $packageName );
			$this->addBreadcrumb();
			$package = $this->getRemotePackage( $fileNum, $packageName );
			if ( $package == null ) {
				$out->addHTML( '<span class="error">Error: No package was found named "' . $packageName . '".' );
				return;
			} elseif ( $request->getCheck( 'install' ) ) {
				$package->install( $user );
				$text = $this->displaySuccessMessage( $this->msg( 'pageexchange-packageinstalled' )->parse() );
			} else {
				$text = $package->getFullHTML();
			}
		} elseif ( $packageName !== null ) {
			$package = null;
			$this->loadInstalledPackages();
			$this->loadAllFiles();
			foreach ( $this->mInstalledPackages as $installedPackage ) {
				if ( $installedPackage->getName() == $packageName ) {
					$this->addBreadcrumb();
					$package = $installedPackage;
					$out->setPageTitle( $this->msg( 'packages' )->parse() . ': ' . $packageName );
					break;
				}
			}
			if ( $package == null ) {
				$out->addHTML( '<span class="error">Error: No package has been installed named "' . $packageName . '".' );
				return;
			}
			if ( $request->getCheck( 'update' ) ) {
				$package->update( $user );
				$text = $this->displaySuccessMessage( $this->msg( 'pageexchange-packageupdated' )->parse() );
			} elseif ( $request->getCheck( 'uninstall' ) ) {
				$deleteAll = $request->getCheck('deleteAll');
				$package->uninstall( $user, $deleteAll );
				$text = $this->displaySuccessMessage( $this->msg( 'pageexchange-packageuninstalled' )->parse() );
			} else {
				$text = $package->getFullHTML();
			}
		} else {
			$this->loadInstalledPackages();
			$this->loadAllFiles();
			$text = $this->displayAll();
		}
		$out->addHTML( $text );
	}

	private function loadInstalledPackages() {
		$dbr = wfGetDb( DB_REPLICA );
		$res = $dbr->select(
			'px_packages',
			[ 'pxp_id', 'pxp_name', 'pxp_package_data' ]
		);
		while ( $row = $res->fetchRow() ) {
			$this->mInstalledPackages[] = PXInstalledPackage::newFromDB( $row, $this->getUser() );
		}
	}

	private function loadAllFiles() {
		$installedPackageIDs = [];
		foreach ( $this->mInstalledPackages as $installedPackage ) {
			$installedPackageIDs[] = $installedPackage->getGlobalID();
		}

		$packageFiles = $this->getConfig()->get( 'PageExchangePackageFiles' );
		foreach ( $packageFiles as $i => $url ) {
			try {
				$pxFile = PXPackageFile::init( $url, $i + 1, $this->mInstalledExtensions, $installedPackageIDs );
			} catch ( MWException $e ) {
				$this->getOutput()->addHtml( Html::element( 'div', [ 'class' => 'error' ], $e->getMessage() ) );
				continue;
			}
			$packages = $pxFile->getAllPackages( $this->getUser() );
			foreach ( $packages as $remotePackage ) {
				$this->loadRemotePackage( $remotePackage );
			}
		}
	}

	private function loadRemotePackage( $remotePackage ) {
		// Check if it matches an installed package.
		foreach ( $this->mInstalledPackages as &$installedPackage ) {
			if (
				$installedPackage->getGlobalID() !== null &&
				$installedPackage->getGlobalID() == $remotePackage->getGlobalID()
			) {
				$installedPackage->setAssociatedRemotePackage( $remotePackage );
				return;
			}
		}

		if ( $remotePackage->isUninstallable() ) {
			$this->mUnusablePackages[] = $remotePackage;
		} elseif ( $remotePackage->isMatch() ) {
			$this->mMatchingPackages[] = $remotePackage;
		} else {
			$this->mNonMatchingPackages[] = $remotePackage;
		}
	}

	private function getRemotePackage( $fileNum, $packageName ) {
		$packageFiles = $this->getConfig()->get( 'PageExchangePackageFiles' );
		if ( count( $packageFiles ) < $fileNum ) {
			return null;
		}

		$fileURL = $packageFiles[$fileNum - 1];

		$dbr = wfGetDb( DB_REPLICA );
		$res = $dbr->select(
			'px_packages',
			'pxp_global_id'
		);
		$installedPackageIDs = [];
		while ( $row = $res->fetchRow() ) {
			$installedPackageIDs[] = $row[0];
		}

		$pxFile = PXPackageFile::init( $fileURL, $fileNum, $this->mInstalledExtensions, $installedPackageIDs );

		return $pxFile->getPackage( $packageName, $this->getUser() );
	}

	private function displayAll() {
		$text = '';
		$installedPackagesText = '';
		foreach ( $this->mInstalledPackages as $package ) {
			$installedPackagesText .= $package->displayCard();
		}

		if ( $installedPackagesText != '' ) {
			$text .= Html::element( 'h2', null, wfMessage( 'pageexchange-installed' )->parse() ) . "\n";
			$text .= <<<END
$installedPackagesText
<br style="clear: both;" />

END;
		}

		$matchingPackagesText = '';
		foreach ( $this->mMatchingPackages as $package ) {
			$matchingPackagesText .= $package->displayCard();
		}
		if ( $matchingPackagesText != '' ) {
			$text .= Html::element( 'h2', null, wfMessage( 'pageexchange-available' )->parse() ) . "\n";
			$text .= <<<END
<p>Page names in <em>italics</em> already exist on this wiki.</p>
$matchingPackagesText
<br style="clear: both;" />

END;
		}

		$nonMatchingPackagesText = '';
		foreach ( $this->mNonMatchingPackages as $package ) {
			$nonMatchingPackagesText .= $package->displayCard();
		}
		if ( $nonMatchingPackagesText != '' ) {
			$text .= <<<END
<h2>Non-matching</h2>
<p>Page names in <em>italics</em> already exist on this wiki.</p>
$nonMatchingPackagesText
<br style="clear: both;" />

END;
		}

		$unusablePackagesText = '';
		foreach ( $this->mUnusablePackages as $package ) {
			$unusablePackagesText .= $package->displayCard();
		}
		if ( $unusablePackagesText !== '' ) {
			$text .= <<<END
<h2>Unusable</h2>
$unusablePackagesText
<br style="clear: both;" />

END;
		}

		return $text;
	}

	private function addBreadcrumb() {
		$linkRenderer = $this->getLinkRenderer();
		$packagesPage = $this;
		$mainPageLink =
			$linkRenderer->makeLink( $this->getPageTitle(),
				htmlspecialchars( $this->getDescription() ) );
		$this->getOutput()->setSubtitle( '< ' . $mainPageLink );
	}

	private function displaySuccessMessage( $msg ) {
		$text = Html::element( 'p', null, $msg ) . "\n";
		$linkRenderer = $this->getLinkRenderer();
		$mainPageLink = $linkRenderer->makeLink(
			$this->getPageTitle(),
			$this->msg( 'returnto' )->rawParams( $this->getDescription() )->parse()
		);
		$text .= Html::rawElement( 'p', null, $mainPageLink );
		return $text;
	}
}
