<?php

namespace BlueSpice\Social\ExtendedSearch\Job;

use BlueSpice\Social\Entity as SocialEntity;
use BS\ExtendedSearch\Source\Job\UpdateTitleBase;
use MediaWiki\MediaWikiServices;

class Entity extends UpdateTitleBase {

	protected $sSourceKey = 'socialentity';

	protected function doRun() {
		$oDP = $this->getSource()->getDocumentProvider();

		if ( !$this->getTitle()->exists() ) {
			$this->getSource()->deleteDocumentsFromIndex(
				[ $oDP->getDocumentId( $this->getTitle()->getCanonicalURL() ) ]
			);
			return [ 'id' => $oDP->getDocumentId( $this->getDocumentProviderUri() ) ];
		}

		$oDocumentProviderSource = $this->getDocumentProviderSource();
		if ( !$oDocumentProviderSource instanceof SocialEntity ) {
			return [ 'id' => $oDP->getDocumentId( $this->getDocumentProviderUri() ) ];
		}
		$aDC = $oDP->getDataConfig(
			$this->getDocumentProviderUri(), $oDocumentProviderSource
		);
		$this->getSource()->addDocumentsToIndex( [ $aDC ] );
		return $aDC;
	}

	/**
	 *
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct( 'updateEntityIndex', $title, $params );
	}

	/**
	 *
	 * @return SocialEntity
	 */
	protected function getDocumentProviderSource() {
		return MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
			->newFromSourceTitle( $this->getTitle() );
	}
}
