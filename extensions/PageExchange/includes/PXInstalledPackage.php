<?php
/**
 * Class for a package that has been installed.
 *
 * @author Yaron Koren
 * @ingroup PX
 */

class PXInstalledPackage extends PXPackage {

	private $mID;
	private $mAssociatedRemotePackage;
	private $mUnmatchedRemotePages = [];

	public static function newFromDB( $dbRow, $user ) {
		$package = new PXInstalledPackage();

		$packageData = json_decode( $dbRow['pxp_package_data'] );
		$package->populateWithData( $directoryData = null, $packageData );

		$package->mName = $dbRow['pxp_name'];
		$package->mID = $dbRow['pxp_id'];
		$package->mUser = $user;

		return $package;
	}

	public function setAssociatedRemotePackage( $remotePackage ) {
		$this->mAssociatedRemotePackage = $remotePackage;

		foreach ( $remotePackage->mPages as $remotePage ) {
			$foundMatchingLocalPage = false;
			foreach ( $this->mPages as $localPage ) {
				if ( $localPage->getName() == $remotePage->getName() &&
					$localPage->getNamespace() == $remotePage->getNamespace() ) {
					$localPage->setURL( $remotePage->getURL() );
					$foundMatchingLocalPage = true;
					break;
				}
			}
			if ( !$foundMatchingLocalPage ) {
				$this->mUnmatchedRemotePages[] = $remotePage;
			}
		}
	}

	public function getCardBodyHTML() {
		$packageHTML = '';
		$remotePackage = $this->mAssociatedRemotePackage;
		if ( $remotePackage !== null && $remotePackage->mVersion !== $this->mVersion ) {
			$packageHTML .= $this->displayInfoMessage( wfMessage( 'pageexchange-package-morerecent' )->parse() );
		}
		$packageHTML .= $this->displayDescription();
		$packageHTML .= $this->displayWebsite();
		$packageHTML .= $this->displayInstalledAttribute( 'pageexchange-package-publisher', 'mPublisher' );
		$packageHTML .= $this->displayInstalledAttribute( 'pageexchange-package-author', 'mAuthor' );
		$packageHTML .= $this->displayAttribute( 'pageexchange-package-pages', $this->mPagesString );
		$updateMsg = wfMessage( 'pageexchange-package-update' )->parse();
		$uninstallMsg = wfMessage( 'pageexchange-package-uninstall' )->parse();
		$packageLink = $this->getPackageLink(
			"$updateMsg / $uninstallMsg",
			[ 'name' => $this->mName ]
		);
		$packageHTML .= Html::rawElement( 'p', [ 'class' => 'pageExchangeCardBottomLink' ], $packageLink );

		return $packageHTML;
	}

	public function displayInstalledAttribute( $attrMsg, $attrField ) {
		$localValue = $this->$attrField;
		$remotePackage = $this->mAssociatedRemotePackage;
		if ( $remotePackage !== null ) {
			$remoteValue = $remotePackage->$attrField;
		} else {
			$remoteValue = null;
		}
		if ( $localValue == null && $remoteValue == null ) {
			return '';
		}

		$text = '<p>';
		if ( $attrMsg != '' ) {
			$text .= '<strong>' . wfMessage( $attrMsg )->parse() . '</strong> ';
		}
		if ( is_array( $localValue ) ) {
			$text .= implode( ', ', $localValue );
		} else {
			$text .= $localValue;
		}
		if ( $remotePackage !== null && $remoteValue !== $localValue ) {
			$latestValueText = wfMessage( 'pageexchange-latestvalue' )->rawParams( "<em>$remoteValue</em>" )->parse();
			$text .= " <span class=\"error\">$latestValueText</span> ";
		}
		$text .= "</p>\n";
		return $text;
	}

	public function processPages() {
		$pageLinks = [];
		foreach ( $this->mPages as $page ) {
			$pageLink = Linker::link( $page->getLocalTitle(), null, [], [ 'action' => 'raw' ] );
			$pageLinks[] = $pageLink;
		}
		$this->mPagesString = implode( ', ', $pageLinks );
	}

	private function isUpdateable() {
                $userCanEditJS = $this->mUser->isAllowed( 'editinterface' ) && $this->mUser->isAllowed( 'editsitejs' );
                $userCanEditCSS = $this->mUser->isAllowed( 'editinterface' ) && $this->mUser->isAllowed( 'editsitecss' );

		foreach ( $this->mPages as $page ) {
			$contentType = $page->getContentType();
			if ( $contentType == 'JavaScript' && !$userCanEditJS ) {
				return false;
			}
			if ( $contentType == 'CSS' && !$userCanEditCSS ) {
				return false;
			}
		}
		return true;
	}

	public function getFullHTML() {
		$remoteDiffersFromInstalled = false;
		$packageHTML = '';
		$remotePackage = $this->mAssociatedRemotePackage;
		if ( $remotePackage == null ) {
			$packageHTML .= '<div class="errorbox">' .
				'The external definition of this package can no longer be found.' .
				'</div>';
		}
		$packageHTML .= $this->displayDescription();
		$packageHTML .= $this->displayWebsite( true );
		$packageHTML .= $this->displayInstalledAttribute( 'pageexchange-package-author', 'mAuthor' );
		$packageHTML .= $this->displayInstalledAttribute( 'pageexchange-package-publisher', 'mPublisher' );
		$packageHTML .= $this->displayInstalledAttribute( 'pageexchange-package-version', 'mVersion' );

		$pagesString = "<ul>\n";
		foreach ( $this->mPages as $page ) {
			$pagesString .= "<li>" . $page->getLocalLink();
			$remoteContents = $page->getRemoteContents();
			if ( $remotePackage == null ) {
				// No remote version of anything.
			} elseif ( $page->getURL() == null ) {
				$pagesString .= ' - <span class="error">This page no longer exists in the latest version of this package.</span>';
				$remoteDiffersFromInstalled = true;
			} elseif ( $remoteContents == null ) {
				$pagesString .= ' (' . Html::element( 'a', [ 'href' => $page->getURL() ], 'external' ) . ')';
				$pagesString .= ' - <span class="error">No contents found in the external version of this page!</span>';
			} elseif ( ! $page->localTitleExists() ) {
				// Seems impossible that this would happen.
				$remoteDiffersFromInstalled = true;
			} else {
				if ( $page->getNamespace() == NS_FILE ) {
					$pagesString .= ' (' . Html::element( 'a', [ 'href' => $page->getURL() ], 'external text' ) . ', ' .
						Html::element( 'a', [ 'href' => $page->getFileURL() ], 'external file' ) . ')';
				} else {
					$pagesString .= ' (' . Html::element( 'a', [ 'href' => $page->getURL() ], 'external' ) . ')';
				}
				$localContents = $page->getLocalContents();
				if ( $localContents == $remoteContents ) {
					// Do nothing.
				} else {
					$pagesString .= ' - ' . wfMessage( 'pageexchange-pagehaschanged' )->parse();
					$remoteDiffersFromInstalled = true;
				}
			}
			if ( $page->getNamespace() == NS_FILE && $remotePackage !== null ) {
				$pagesString .= ' (It is unknown whether the latest version of the file matches the local copy.)';
			}
		}
		foreach ( $this->mUnmatchedRemotePages as $unmatchedRemotePage ) {
			$pagesString .= "<li>" . $unmatchedRemotePage->getLocalLink() .
				' (' . Html::element( 'a', [ 'href' => $unmatchedRemotePage->getURL() ], 'external' ) .
				') - this page does not exist locally.';
		}

		$pagesString .= "</ul>\n";
		$packageHTML .= $this->displayAttribute( 'pageexchange-package-pages', $pagesString );

		$packageHTML .= $this->displayInstalledAttribute( 'pageexchange-package-language', 'mLanguage' );

		if ( !$remoteDiffersFromInstalled && $remotePackage !== null ) {
			$remoteDiffersFromInstalled =
				count( $this->mUnmatchedRemotePages ) > 0 ||
				$this->mName !== $remotePackage->mName ||
				$this->mPublisher !== $remotePackage->mPublisher ||
				$this->mVersion !== $remotePackage->mVersion;
		}

		if ( !$this->isUpdateable() ) {
			$packageHTML .= $this->displayWarningMessage( 'You cannot update or uninstall this package because it contains JavaScript and/or CSS pages, which you lack the permission to edit.' );
			return $packageHTML;
		}

		// Normally 'useInputTag' is not supposed to be used for
		// OOUI buttons (you're supposed to make them part of a
		// FieldLayout, and then a FormLayout), but this was much
		// easier to do - in part because there is very minimal
		// documentation for creating OOUI elements in PHP.
		$updateButton = new OOUI\ButtonInputWidget( [
			'label' => wfMessage( 'pageexchange-package-update' )->parse(),
			'useInputTag' => true,
			'type' => 'submit',
			'name' => 'update',
			'flags' => 'progressive'
		] );
		$deleteAllCheckbox = new OOUI\CheckboxInputWidget( [
			'name' => 'deleteAll',
			'selected' => true
		] );
		$deleteAllMsg = wfMessage( 'pageexchange-package-deleteallpages' )->parse();
		$uninstallButton = new OOUI\ButtonInputWidget( [
			'label' => wfMessage( 'pageexchange-package-uninstall' )->parse(),
			'useInputTag' => true,
			'type' => 'submit',
			'name' => 'uninstall',
			'flags' => 'destructive'
		] );
		$uninstallFormSection =<<<END
<p>
<label>
$deleteAllCheckbox
$deleteAllMsg
</label>
<p>
$uninstallButton
</p>

END;

		$packageHTML .=<<<END
<form method="post">
<table style="border-collapse: collapse; margin-top: 20px; border-top: 1px solid #ccc;">
<tr>

END;

		if ( $remotePackage == null || !$remoteDiffersFromInstalled ) {
			// Don't include the update button if the remote
			// package can't be found, or if it's identical to the
			// installed package.
			$packageHTML .=<<<END
<td style="padding: 15px;">
$uninstallFormSection
</td>

END;
		} else {
			$packageHTML .=<<<END
<td style="border-right: 1px solid #ccc; padding: 25px;">
$updateButton
</td>
<td style="border-left: 1px solid #ccc; padding: 25px;">
$uninstallFormSection
</td>

END;
		}

		$packageHTML .=<<<END
</tr>
</table>
</form>

END;

		return $packageHTML;
	}

	public function update( $user ) {
		$dbw = wfGetDB( DB_MASTER );

		$updateValues = [
			'pxp_name' => $this->mAssociatedRemotePackage->mName
		];
		$dbw->update( 'px_packages', $updateValues, [ 'pxp_id' => $this->mID ] );

		$deletedPages = [];

		foreach ( $this->mPages as $localPage ) {
			$localName = $localPage->getName();
			$localNamespace = $localPage->getNamespace();
			$foundMatch = false;
			foreach ( $this->mAssociatedRemotePackage->mPages as $remotePage ) {
				if ( $remotePage->getName() == $localName && $remotePage->getNamespace() == $localNamespace ) {
					$foundMatch = true;
					break;
				}
			}
			if ( !$foundMatch ) {
				$deletedPages[] = $localPage;
			}
		}

		foreach ( $deletedPages as $deletedPage ) {
			$deletedPage->deleteWikiPage( $user, $packageName, false );
		}

		foreach ( $this->mAssociatedRemotePackage->mPages as $page ) {
			$page->createOrUpdateWikiPage( $user, $this->mName, false );
		}

		$this->logAction( 'updatepackage', $user );
	}

	public function uninstall( $user, $deleteAll ) {
		$dbw = wfGetDB( DB_MASTER );

		if ( $deleteAll ) {
			foreach ( $this->mPages as $page ) {
				$page->deleteWikiPage( $user, $this->mName, true );
			}
		}

		$dbw->delete( 'px_packages', [ 'pxp_id' => $this->mID ] );

		$this->logAction( 'uninstallpackage', $user );
	}

}
