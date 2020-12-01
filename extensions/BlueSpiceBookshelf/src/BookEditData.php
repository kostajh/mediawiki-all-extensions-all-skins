<?php

namespace BlueSpice\Bookshelf;

use Exception;
use MediaWiki\MediaWikiServices;
use Message;
use MWException;
use RequestContext;
use stdClass;
use Title;
use WebRequest;

class BookEditData {
	/** @var array Type => basedOnTitle */
	protected $bookTypes = [
		'ns_book' => true,
		'user_book' => true,
		'local_storage' => false,
	];

	/** @var Title|bool */
	private $basedOnTitle = false;
	/** @var string */
	private $bookTitle = '';
	/** @var string */
	private $bookType = '';
	/** @var WebRequest */
	private $request;
	/** @var stdClass */
	private $bookData;

	/**
	 * @param string $name
	 * @param WebRequest $request
	 * @throws MWException
	 */
	protected function __construct( $name, WebRequest $request ) {
		$this->bookTitle = $name;
		$this->request = $request;

		$this->execute();
	}

	/**
	 * @param Title $title
	 * @param WebRequest $request
	 * @return static
	 * @throws MWException
	 */
	public static function newFromTitleAndRequest( Title $title, WebRequest $request ) {
		if ( !in_array( $title->getNamespace(), [ NS_USER, NS_BOOK ] ) ) {
			throw new MWException(
				Message::newFromKey( 'bs-bookshelf-error-type-title-mismatch' )->text()
			);
		}
		$name = $title->getPrefixedText();

		return new static( $name, $request );
	}

	/**
	 * @param string $name
	 * @param WebRequest $request
	 * @return static
	 * @throws MWException
	 */
	public static function newFromNameRequest( $name, WebRequest $request ) {
		if ( empty( $name ) ) {
			$name = $request->getVal( 'book', '' );
		}
		if ( empty( $name ) ) {
			throw new MWException(
				Message::newFromKey( 'bs-bookshelf-editor-no-title-provided' )->text()
			);
		}

		return new static( $name, $request );
	}

	/**
	 * @return string
	 */
	public function getBookTitle() {
		return $this->bookTitle;
	}

	/**
	 * @return Title|null
	 */
	public function getTitle() {
		return $this->basedOnTitle instanceof Title ? $this->basedOnTitle : null;
	}

	/**
	 * @return stdClass
	 */
	public function getBookData() {
		return $this->bookData;
	}

	/**
	 * @return string
	 */
	public function getBookType() {
		return $this->bookType;
	}

	/**
	 * @throws MWException
	 */
	protected function execute() {
		$this->parseBookType();
		$this->setTitle();

		$meta = new stdClass();
		$phpf = MediaWikiServices::getInstance()->getService(
			'BSBookshelfPageHierarchyProviderFactory'
		);
		try {
			$title = $this->bookTitle;
			$params = [
				'book_type' => $this->bookType
			];
			if ( $this->getTitle() instanceof Title ) {
				$title = $this->getTitle()->getPrefixedText();
			} else {
				$content = $this->request->getVal( 'content', '' );
				$params['content'] = $content;
			}
			$php = $phpf->getInstanceFor( $title, $params );

			$tree = $php->getExtendedTOCJSON( [ 'suppress-number-in-text' => true ] );
			$meta = (object)$php->getBookMeta();
			if ( $tree === null ) {
				throw new Exception();
			}
			$tree->text = $this->bookTitle;
		} catch ( Exception $ex ) {
			// This is a new book
			$tree = $this->getBlankTree();
		}

		$bookMetaConfig = [
			'title' => [
				'displayName' => wfMessage( 'bs-bookshelfui-bookmetatag-title' )->text()
			],
			'subtitle' => [
				'displayName' => wfMessage( 'bs-bookshelfui-bookmetatag-subtitle' )->text()
			],
			'author1' => [
				'displayName' => wfMessage( 'bs-bookshelfui-bookmetatag-author1' )->text()
			],
			'author2' => [
				'displayName' => wfMessage( 'bs-bookshelfui-bookmetatag-author2' )->text()
			],
			'docummentidentifier' => [
				'displayName' => wfMessage( 'bs-bookshelfui-bookmetatag-docummentidentifier' )->text()
			],
			'docummenttype' => [
				'displayName' => wfMessage( 'bs-bookshelfui-bookmetatag-docummenttype' )->text()
			],
			'department' => [
				'displayName' => wfMessage( 'bs-bookshelfui-bookmetatag-department' )->text()
			],
			'version' => [
				'displayName' => wfMessage( 'bs-bookshelfui-bookmetatag-version' )->text()
			],
		];

		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		$user = RequestContext::getMain()->getUser();

		$this->bookData = new stdClass();
		$this->bookData->bookTree = $tree;
		$this->bookData->bookMeta = $meta;
		$this->bookData->bookMetaConfig = $bookMetaConfig;
		$this->bookData->bookEdit = $this->basedOnTitle instanceof Title ?
			$pm->userCan( 'edit', $user, $this->basedOnTitle ) : true;
		$this->bookData->bookType = $this->bookType;

		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSBookshelfGetBookData', [
			$this,
			&$this->bookData
		] );
	}

	/**
	 * Set the book type
	 */
	private function parseBookType() {
		$type = $this->request->getVal( 'type', false );
		if ( !$type ) {
			// To be inferred from title - backwards compat
			return;
		}
		if ( isset( $this->bookTypes[$type] ) ) {
			$this->bookType = $type;
			$this->basedOnTitle = $this->bookTypes[$type];
			return;
		}

		throw new MWException(
			Message::newFromKey( 'bs-bookshelf-error-invalid-type' )->text()
		);
	}

	/**
	 * Set the Title object, if book is based on a title
	 * @throws MWException
	 */
	private function setTitle() {
		$inferType = $this->bookType === '';
		if ( !$inferType && !$this->basedOnTitle ) {
			$this->bookTitle = $this->getBookTitle();
			return;
		}
		$title = Title::newFromText( $this->getBookTitle(), NS_BOOK );
		if (
			( $this->bookType === 'ns_book' || $inferType ) &&
			$title->getNamespace() === NS_BOOK
		) {
			$this->bookTitle = $title->getText();
			$this->basedOnTitle = $title;
			if ( $inferType ) {
				$this->bookType = 'ns_book';
			}
			return;
		}
		if (
			( $this->bookType === 'user_book' || $inferType ) &&
			$title->getNamespace() === NS_USER
		) {
			$this->bookTitle = $title->getSubpageText();
			$this->basedOnTitle = $title;
			if ( $inferType ) {
				$this->bookType = 'user_book';
			}
			return;
		}
		if ( $inferType ) {
			$this->bookType = 'local_storage';
			$this->basedOnTitle = false;
			return;
		}

		throw new MWException(
			Message::newFromKey( 'bs-bookshelf-error-type-title-mismatch' )->text()
		);
	}

	/**
	 * @return array
	 */
	private function getBlankTree() {
		$articleTitle = $this->getTitle() instanceof Title ?
			$this->getTitle()->getPrefixedText() : $this->bookTitle;

		return [
			'text' => $this->bookTitle,
			'articleTitle' => $articleTitle,
			'articleDisplayTitle' => $articleTitle,
			'children' => []
		];
	}
}
