<?php

use Wikimedia\AtEase\AtEase;

/**
 * Class for a JSON file holding the definitions of one or more packages.
 *
 * @author Yaron Koren
 * @ingroup PX
 */
class PXPackageFile {

	private $mData;
	private $mFileNum;
	private $mInstalledExtensions;
	private $mInstalledPackages;

	public static function init( $url, $fileNum, $installedExtensions, $installedPackages ) {
		$json = self::getWebPageContents( $url );
		if ( $json === null ) {
			throw new MWException( 'Error: no file found at '. $url );
		}
		$fileData = json_decode( $json );
		if ( $fileData === null ) {
			throw new MWException( 'Error: the file at '. $url . ' contains invalid JSON.' );
		}

		$packageFile = new PXPackageFile();
		$packageFile->mData = $fileData;
		$packageFile->mFileNum = $fileNum;
		$packageFile->mInstalledExtensions = $installedExtensions;
		$packageFile->mInstalledPackages = $installedPackages;

		return $packageFile;
	}

	public function getPackage( $packageName, $user ) {
		$packageData = $this->mData->packages->$packageName;
		return PXRemotePackage::newFromData(
			$this->mFileNum,
			$this->mData,
			$packageName,
			$packageData,
			$this->mInstalledExtensions,
			$this->mInstalledPackages,
			$user
		);
	}

	public function getAllPackages( $user ) {
		$packages = [];
		foreach ( $this->mData->packages as $name => $packageData ) {
			$packages[] = PXRemotePackage::newFromData(
				$this->mFileNum,
				$this->mData,
				$name,
				$packageData,
				$this->mInstalledExtensions,
				$this->mInstalledPackages,
				$user
			);
		}
		return $packages;
	}

	/**
	 * Utility function. This should really go into a "PXUtils" class,
	 * but it seemed silly to create a class just for this one function.
	 */
	public static function getWebPageContents( $url ) {
		// Use cURL, if it's installed - it seems to have a better
		// chance of working.
		if ( function_exists( 'curl_init' ) ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_URL, $url );
			return curl_exec( $ch );
		}

		if ( method_exists( AtEase::class, 'suppressWarnings' ) ) {
			// MW >= 1.33
			AtEase::suppressWarnings();
		} else {
			\MediaWiki\suppressWarnings();
		}
		$contents = file_get_contents( $url );
		if ( method_exists( AtEase::class, 'restoreWarnings' ) ) {
			// MW >= 1.33
			AtEase::restoreWarnings();
		} else {
			\MediaWiki\restoreWarnings();
		}

		return $contents;
	}

}
