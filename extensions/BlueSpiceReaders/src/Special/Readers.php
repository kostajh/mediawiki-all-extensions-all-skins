<?php
namespace BlueSpice\Readers\Special;

use Html;
use MediaWiki\MediaWikiServices;
use PermissionsError;
use Title;
use User;
use ViewTagError;
use ViewTagErrorList;

class Readers extends \BlueSpice\SpecialPage {

	/**
	 * @param string $name
	 * @param string $restrictions
	 */
	public function __construct( $name = 'Readers', $restrictions = 'viewreaders' ) {
		parent::__construct( $name, $restrictions, false );
	}

	/**
	 * @param string $parameters
	 * @throws PermissionsError
	 */
	public function execute( $parameters ) {
		$this->checkPermissions();
		$requestedTitle = null;
		$out = $this->getOutput();

		if ( !empty( $parameters ) ) {
			$requestedTitle = Title::newFromText( $parameters );

			if (
				$requestedTitle->exists() &&
				(
					$requestedTitle->getNamespace() !== NS_USER ||
					strpos( $requestedTitle->getText(), '/' ) !== false
				)
			) {
				$stringOut = $this->pageReaders( $requestedTitle );
				if ( !$stringOut ) {
					$this->pageNotExistError();
				}
			} elseif (
				$requestedTitle->getNamespace() === NS_USER &&
				strpos( $requestedTitle->getText(), '/' ) === false
			) {
				$stringOut = $this->readByUser( $requestedTitle );
				if ( !$stringOut ) {
					$stringOut = $this->pageNotExistError();
				}
			} else {
				$stringOut = $this->pageNotExistError();
			}
		} else {
			$stringOut = $this->emptyInputError();
		}

		if ( $requestedTitle === null ) {
			$out->setPageTitle( $out->getPageTitle() );
		}

		$out->addHTML( $stringOut );
	}

	/**
	 * @return string
	 */
	protected function pageNotExistError() {
		$this->getOutput()->setPageTitle( wfMessage( 'bs-readers-pagenotexists' ) );
		$errorView = new ViewTagErrorList();
		$errorView->addItem(
			new ViewTagError( wfMessage( 'bs-readers-pagenotexists' )->plain() )
		);
		return $errorView->execute();
	}

	/**
	 * @return string
	 */
	protected function emptyInputError() {
		$this->getOutput()->setPageTitle( wfMessage( 'bs-readers-emptyinput' ) );
		$errorView = new ViewTagErrorList(
			MediaWikiServices::getInstance()->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceReaders' )
		);
		$errorView->addItem( new ViewTagError( wfMessage( 'bs-readers-emptyinput' )->plain() ) );
		return $errorView->execute();
	}

	/**
	 * @param Title $requestedTitle
	 * @return string
	 */
	protected function pageReaders( Title $requestedTitle ) {
		$this->getOutput()->addModules( [ 'ext.bluespice.readers.specialreaders' ] );
		$this->getOutput()->setPageTitle(
			wfMessage( 'readers', $requestedTitle->getFullText() )->text()
		);
		$this->getOutput()->addJsConfigVars( "bsReadersTitle", $requestedTitle->getPrefixedText() );

		return Html::element( 'div', [
			'id' => 'bs-readers-grid'
		] );
	}

	/**
	 * @param Title $requestedTitle
	 * @return string
	 */
	protected function readByUser( Title $requestedTitle ) {
		$this->getOutput()->addModules( [ 'ext.bluespice.readers.specialreaderspath' ] );
		$oUser = User::newFromName( $requestedTitle->getText() );
		$this->getOutput()->setPageTitle( wfMessage( 'readers-user', $oUser->getName() )->text() );

		$this->getOutput()->addJsConfigVars( "bsReadersUserID", $oUser->getId() );

		return Html::element( 'div', [
			'id' => 'bs-readerspath-grid'
		] );
	}
}
