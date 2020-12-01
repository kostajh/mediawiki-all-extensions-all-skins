<?php
/**
 * This file contains classes for uploading and thumbnailing SVG files
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2012 Harry Burt
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Class for writing SVG files
 */
class SVGFormatWriter {

	/**
	 * @var SVGMessageGroup
	 */
	protected $group;

	/**
	 * @var SVGFile
	 */
	protected $svg;
	protected $url;
	protected $filename;
	protected $file;

	protected $inProgressTranslations = [];

	/**
	 * @param SVGMessageGroup $group Message group to write to file
	 * @param array[] $inProgressTranslations Possible array of overriddes (unsaved translations
	 *  that should take preference over saved ones), format: [id][langcode][property name]
	 */
	public function __construct( SVGMessageGroup $group, $inProgressTranslations = [] ) {
		$this->group = $group;
		$this->svg = SVGFile::newFromMessageGroup( $this->group );
		$this->inProgressTranslations = $inProgressTranslations;
		$this->filename = $this->group->getId();
		$this->file = MediaWikiServices::getInstance()->getRepoGroup()
			->findFile( Title::makeTitle( NS_FILE, $this->filename ) );
	}

	/**
	 * Get the array of in-progress translations
	 * @return array[]
	 */
	public function getInProgressTranslations() {
		return $this->inProgressTranslations;
	}

	/**
	 * Collate and prepare an array of translations from multiple sources:
	 * in file, on wiki, filteredTextNodes and in-progress.
	 *
	 * @return array[] Array of translations
	 */
	protected function getPreferredTranslations() {
		$inFileTranslations = $this->svg->getInFileTranslations();
		$onWikiTranslations = $this->group->getOnWikiTranslations();
		$inProgressTranslations = $this->getInProgressTranslations();

		// Collapse in-progress translations into on-wiki translations
		foreach ( $inProgressTranslations as $key => $languages ) {
			foreach ( $languages as $language => $translation ) {
				$language = ( $this->group->getSourceLanguage() === $language ) ? 'fallback' : $language;
				$onWikiTranslations[$key][$language] = TranslateSvgUtils::translationToArray( $translation );
			}
		}

		// Collapse on-wiki translations translations into in-progress translations
		foreach ( $onWikiTranslations as $key => $languages ) {
			foreach ( $languages as $language => $translation ) {
				$oldItem = $inFileTranslations[$key][$language] ?? [];
				$inFileTranslations[$key][$language] = $onWikiTranslations[$key][$language] + $oldItem;
				if ( $language !== 'fallback' ) {
					$inFileTranslations[$key][$language]['id'] = $inFileTranslations[$key]['fallback']['id'] . "-$language";
				}
			}
		}

		// "Unfilter" translations
		$inFileTranslations = array_merge( $inFileTranslations, $this->svg->getFilteredTextNodes() );

		// Ensure that child tspan translations prompt new <text>s to be created
		// by duplicating the fallback version.
		foreach ( $inFileTranslations as $languages ) {
			foreach ( $languages as $language => $translation ) {
				if ( isset( $languages['fallback']['data-parent'] ) ) {
					$parent = $languages['fallback']['data-parent'];
					$inFileTranslations[$parent][$language] = $inFileTranslations[$parent]['fallback'];
					if ( $language !== 'fallback' ) {
						$inFileTranslations[$parent][$language]['id'] .= "-$language";
					}
				}
			}
		}

		return $inFileTranslations;
	}

	/**
	 * Returns a thumbnail of an SVG translated into the language provided.
	 * The thumbnail will include all translations, including in progress and
	 * onwiki translations, rather than just those uploaded.
	 *
	 * @param string|bool $language Code of the language to translate into
	 * @param int $size The length (in px) of one side of a bounding box square: aspect ratio will
	 *  always be preserved. Default 275.
	 * @return array Array with keys 'success'=>true|false and
	 *  'message'=>/web/friendly/path/to/the/new/thumbnail.png|error output
	 */
	public function thumbnailExport( $language, $size = 275 ) {
		global $wgTranslateSvgDirectory, $wgTranslateSvgPath,
			   $wgUploadDirectory, $wgUploadPath;

		if ( !$wgTranslateSvgDirectory ) {
			$wgTranslateSvgDirectory = "{$wgUploadDirectory}/translatesvg";
		}
		if ( !$wgTranslateSvgPath ) {
			$wgTranslateSvgPath = "{$wgUploadPath}/translatesvg";
		}

		$this->svg->switchToTranslationSet( $this->getPreferredTranslations() );

		$srcPath = tempnam( wfTempDir(), 'trans' );
		$srcTempFile = new TempFSFile( $srcPath );
		$srcTempFile->autocollect(); // destroy file when $tempFsFile leaves scope

		$intPath = tempnam( wfTempDir(), 'trans' );
		$intTempFile = new TempFSFile( $srcPath );
		$intTempFile->autocollect(); // destroy file when $tempFsFile leaves scope

		$contentsHash = substr( md5( $this->svg->saveToString() ), 0, 12 );
		$nameHash = md5( $this->filename );
		$nameHashPath = substr( $nameHash, 0, 1 ) . '/' . substr( $nameHash, 0, 2 );
		$dstPath = $this->getBackend()->getRootStoragePath() .
			'/translatesvg-render/';
		$dstName = "$nameHashPath/$contentsHash-" . $this->filename . '.png';
		$dstUrl = $wgTranslateSvgPath . '/' . $dstName;

		if ( $this->getBackend()->fileExists( [ 'src' => $dstPath . $dstName ] ) ) {
			// We've already generated this SVG; no point regenerating
			return [
				'success' => true,
				'message' => $dstUrl,
			];
		}

		// Save the SVG to a temporary file
		if ( !$this->svg->saveToPath( $srcPath ) ) {
			return [ 'success' => false, 'message' => wfMessage( 'thumbnail-temp-create' )->text() ];
		}

		// Work out appropriate height and width for thumbnail
		$width = $this->file->getWidth();
		$height = $this->file->getHeight();
		$ratio = $width / $height;
		if ( $width > $height ) {
			$width = $size;
			$height = round( $size / $ratio );
		} else {
			$height = $size;
			$width = round( $size * $ratio );
		}

		// MediaWiki's SvgHandler() class, not to be confused with SVGFormatReader
		// Used to rasterize the SVG into a PNG of a thumbnail size
		$svgHandler = new SvgHandler();
		if ( !$svgHandler->rasterize( $srcPath, $intPath, $width, $height, $language ) ) {
			return [ 'success' => false, 'message' => wfMessage( 'thumbnail-dest-create' )->text() ];
		}

		// Create any containers/directories as needed...
		$backend = $this->getBackend();
		if ( !$backend->prepare( [ 'dir' => "$dstPath/$nameHashPath/" ] )->isOK() ) {
			return [ 'success' => false, 'message' => wfMessage( 'thumbnail_dest_directory' )->text() ];
		}
		// Store the file at the final storage path...
		if ( !$backend->quickStore(
			[
				'src' => $intPath, 'dst' => $dstPath . $dstName
			]
		)->isOK()
		) {
			return [ 'success' => false, 'message' => wfMessage( 'thumbnail-dest-create' )->text() ];
		}
		return [
			'success' => true,
			'message' => $dstUrl
		];
	}

	/**
	 * Handles the actual upload process for a given SVG in DOMDocument form
	 *
	 * @param User $user The user to use for the upload
	 * @return bool|string True on success, error message on failure
	 */
	public function exportToSVG( User $user ) {
		global $wgTranslateSvgBotName, $wgContLang, $wgOut;

		$languages = $this->svg->switchToTranslationSet( $this->getPreferredTranslations() );

		// Analyze what changes have been made:
		// * $started contains new languages;
		// * $expanded contains old languages with new translations
		$started = $languages['started'];
		$expanded = $languages['expanded'];
		if ( count( $started ) === 0 && count( $expanded ) === 0 ) {
			// No real change, jump to save just a null edit might
			return true;
		}

		// Prepare edit summary accordingly
		$startedString =
		$expandedString = wfMessage( 'translate-svg-upload-none' )->inContentLanguage();
		if ( count( $started ) !== 0 ) {
			$startedString = $wgContLang->commaList( $started );
		}
		if ( count( $expanded ) !== 0 ) {
			$expandedString = $wgContLang->commaList( $expanded );
		}
		$comment = wfMessage(
			'translate-svg-upload-comment',
			[ $startedString, $expandedString ]
		)->inContentLanguage();

		// Save SVG to temp
		$temp = tempnam( wfTempDir(), 'trans' );
		$this->svg->saveToPath( $temp );

		// Prepare upload
		$uploader = new TranslateSvgUpload();
		$filePath = $this->file->getLocalRefPath();
		$uploader->initializePathInfo( $this->filename, $temp, filesize( $filePath ), true );
		$details = $uploader->verifyUpload();
		if ( $details['status'] != UploadBase::OK ) {
			return $this->processVerificationError( $details );
		}

		// Actually perform upload
		$bot = User::newFromName( $wgTranslateSvgBotName, false );
		$status = $uploader->performUpload( $comment, false, false, $bot );
		if ( !$status->isGood() ) {
			return $wgOut->parseAsInterface( $status->getWikiText() );
		}

		// If the user would have watched a normal reupload, watch this
		// reupload.
		if ( $user->getOption( 'watchcreations' ) ) {
			$user->addWatch( $uploader->getLocalFile()->getTitle() );
		}

		return true;
	}

	/**
	 * Provides output to the user for a result of UploadBase::verifyUpload
	 * Copied from SpecialUpload (c) Authors
	 *
	 * @param array $details Result of UploadBase::verifyUpload
	 * @return Message
	 */
	protected function processVerificationError( $details ) {
		switch ( $details['status'] ) {
			case UploadBase::FILE_TOO_LARGE:
				return wfMessage( 'largefileserver' )->plain();
			case UploadBase::VERIFICATION_ERROR:
				unset( $details['status'] );
				$code = array_shift( $details['details'] );
				return wfMessage( $code, $details['details'] )->parse();
			default:
				// A random error, possibly that the file got corrupted and ended
				// up having an illegal length of 0 bytes
				$link = Html::element(
					'a',
					[ 'href' => 'https://phabricator.wikimedia.org/maniphest/task/create/?projects=TranslateSVG' ],
					'phabricator.wikimedia.org'
				);
				return wfMessage( 'translate-svg-export-error' )->rawParams( $link )->escaped();
		}
	}

	/**
	 * Find and return the current FSFileBackend object
	 * Copied from the Math extension
	 *
	 * @return FileBackend
	 */
	protected function getBackend() {
		global $wgTranslateSvgBackend, $wgTranslateSvgDirectory;

		if ( $wgTranslateSvgBackend ) {
			return MediaWikiServices::getInstance()->getFileBackendGroup()
				->get( $wgTranslateSvgDirectory );
		} else {
			static $backend = null;
			if ( !$backend ) {
				$backend = new FSFileBackend( [
					'name'           => 'translatesvg-backend',
					'lockManager'    => 'nullLockManager',
					'containerPaths' => [ 'translatesvg-render' => $wgTranslateSvgDirectory ],
					'fileMode'       => 0777
				] );
			}
			return $backend;
		}
	}
}

/**
 * Empty class used to instantiate an instance of the otherwise abstract UploadBase
 * class.
 */

class TranslateSvgUpload extends UploadBase {
	public function initializeFromRequest( &$request ) {
	}
}
