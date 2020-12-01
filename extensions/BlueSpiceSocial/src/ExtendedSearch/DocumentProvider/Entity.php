<?php

namespace BlueSpice\Social\ExtendedSearch\DocumentProvider;

use BlueSpice\Data\FieldType;
use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Social\Entity as SocialEntity;
use BS\ExtendedSearch\Source\DocumentProvider\WikiPage;

class Entity extends WikiPage {

	/**
	 *
	 * @param string $sUri
	 * @param SocialEntity $oEntity
	 * @return array
	 */
	public function getDataConfig( $sUri, $oEntity ) {
		$oWikiPage = \WikiPage::factory( $oEntity->getTitle() );
		$aDC = parent::getDataConfig( $sUri, $oWikiPage );
		$aDC['entitydata'] = $this->normalizeEntityData( $oEntity );
		return $aDC;
	}

	/**
	 *
	 * @param SocialEntity $oEntity
	 * @return array
	 */
	protected function normalizeEntityData( $oEntity ) {
		$aNormalData = [];

		$sStoreClass = $oEntity->getConfig()->get( 'StoreClass' );
		if ( !class_exists( $sStoreClass ) ) {
			return \Status::newFatal( "Store class '$sStoreClass' not found" );
		}
		$oStore = new $sStoreClass();
		$oSchema = $oStore->getWriter( \RequestContext::getMain() )->getSchema();
		$aData = array_intersect_key(
			$oEntity->getFullData(),
			array_flip( $oSchema->getIndexableFields() )
		);
		foreach ( $aData as $sKey => $mValue ) {
			if ( !$oSchema[$sKey] ) {
				continue;
			}
			if ( $oSchema[$sKey][Schema::TYPE] === FieldType::DATE ) {
				$aNormalData[$sKey] = wfTimestamp( TS_ISO_8601, $mValue );
				continue;
			}

			$mNormalValue = $mValue;
			if ( $oSchema[$sKey][Schema::TYPE] === FieldType::STRING ) {
				$mNormalValue = strip_tags( $mValue );
			}
			if ( $oSchema[$sKey][Schema::TYPE] === FieldType::TEXT ) {
				$mNormalValue = strip_tags( $mValue );
			}
			if ( $oSchema[$sKey][Schema::TYPE] === FieldType::LISTVALUE ) {
				$mNormalValue = array_values( $mValue );
			}
			if ( $oSchema[$sKey][Schema::TYPE] === FieldType::BOOLEAN ) {
				$mNormalValue = $mValue ? true : false;
			}

			// this is currently not in use... we may implement normalizer
			// callback for any store writers in the feature
			if ( is_object( $mValue ) ) {
				$mNormalValue = \FormatJson::encode( $mValue );
			}

			$aNormalData[$sKey] = $mNormalValue;
		}

		unset( $oEntity );
		unset( $oStore );
		unset( $oSchema );

		return $aNormalData;
	}

}
