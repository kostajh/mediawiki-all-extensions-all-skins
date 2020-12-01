<?php
/**
 * Unit test case to enable code sharing between unit test
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2014, Harry Burt
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

class TranslateSvgTestCase extends MediaWikiTestCase {
	protected $tablesUsed = [
		'user',
		'page',
		'translate_svg',
		'translate_metadata',
		'revision',
		'archive',
		'image',
		'comment',
	];
	/**
	 * @var string
	 */
	protected static $name;

	/**
	 * @var SVGMessageGroup
	 */
	protected $messageGroup;

	private $tempPath;

	protected function prepareFile( $path ) {
		$tempName = $this->getNewTempFile();
		copy( $path, $tempName );

		$name = substr( basename( $path ), 0, -4 ) . '_' . date( 'His' ) . '.svg';
		$name = str_replace( '_', ' ', $name );
		$title = Title::makeTitle( NS_FILE, $name );
		if ( $title->exists() ) {
			$user = $this->getTestSysop()->getUser();
			// @todo This should not happen
			$wikiPage = new WikiPage( $title );
			$wikiPage->doDeleteArticleReal( 'resetting', $user );
			$subpages = $title->getSubpages();
			foreach ( $subpages as $subpage ) {
				/** @var Title $subpage */
				$wikiPage = new WikiPage( $subpage );
				$wikiPage->doDeleteArticleReal( 'resetting', $user );
			}
		}

		// Prepare upload
		/** @var UploadBase|\PHPUnit\Framework\MockObject\MockObject $uploader */
		$uploader = $this->getMockBuilder( UploadBase::class )
			->setMethods( [ 'initializeFromRequest' ] )
			->getMock();
		$uploader->initializePathInfo( $name, $tempName, filesize( $tempName ), true );

		// Actually perform upload
		$status = $uploader->performUpload( 'testing', 'Created during testing', false,
			self::getTestUser()->getUser() );
		$title = Title::makeTitle( NS_FILE, $name );
		if ( !$status->isGood() || !$title->exists() ) {
			$this->fail( "Could not upload test file $name:\n$status" );
		}

		$this->tempPath = $uploader->getLocalFile()->getPath();

		$messageGroup = new SVGMessageGroup( $name );
		$messageGroup->register( false );

		self::$name = $name;
		$this->messageGroup = new SVGMessageGroup( self::$name );
	}

	public function setUp() : void {
		parent::setUp();

		$pairs = [
			// Enable uploads
			'wgEnableUploads' => true,

			// Add .svg to list of supported file extensions
			'wgFileExtensions' => [ 'png', 'gif', 'jpg', 'jpeg', 'svg' ],

			// Need to enable subpages in the File: namespace
			'wgNamespacesWithSubpages' => [ NS_FILE => true ]
		];
		$this->setMwGlobals( $pairs );
	}

	public function tearDown() : void {
		parent::tearDown();
		$this->messageGroup = null;
		self::$name = null;
		if ( $this->tempPath ) {
			$backend = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()->getBackend();
			$backend->delete( [ 'src' => $this->tempPath ], [ 'force' => 1 ] );
			$this->tempPath = null;
		}
	}
}
