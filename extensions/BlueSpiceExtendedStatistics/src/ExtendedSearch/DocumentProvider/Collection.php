<?php

namespace BlueSpice\ExtendedStatistics\ExtendedSearch\DocumentProvider;

use BlueSpice\Data\FieldType;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Schema;
use BlueSpice\ExtendedStatistics\Entity\Collection as Entity;
use BS\ExtendedSearch\Source\DocumentProvider\DecoratorBase;

class Collection extends DecoratorBase {

	/**
	 *
	 * @param string $sUri
	 * @param Entity $entity
	 * @param Title $title
	 * @return array
	 */
	public function getDataConfig( $sUri, $entity, $title ) {
		$aDC = $this->oDecoratedDP->getDataConfig( $sUri, null );
		$aDC = array_merge( $aDC, [
			'basename' => $title->getBaseText(),
			'basename_exact' => $title->getBaseText(),
			'extension' => 'json',
			'mime_type' => 'text/json',
			'mtime' => wfTimestamp(
				TS_ISO_8601,
				$entity->get( Entity::ATTR_TIMESTAMP_TOUCHED )
			),
			'ctime' => wfTimestamp(
				TS_ISO_8601,
				$entity->get( Entity::ATTR_TIMESTAMP_CREATED )
			),
			'size' => 1,
			'categories' => [],
			'prefixed_title' => $title->getPrefixedText(),
			'sections' => [],
			'source_content' => '',
			'rendered_content' => '',
			'namespace' => $title->getNamespace(),
			'namespace_text' => 'Special',
			'tags' => [],
			'is_redirect' => $title->isRedirect(),
			'redirects_to' => null,
			'redirected_from' => null,
			'page_language' => $title->getPageLanguage()->getCode(),
			'display_title' => '',
			'used_files' => []
		] );
		$aDC['collectiondata'] = $this->normalizeEntityData( $entity );
		return $aDC;
	}

	/**
	 *
	 * @param Entity $entity
	 * @return array
	 */
	protected function normalizeEntityData( $entity ) {
		$normalData = [];

		$storeClass = $entity->getConfig()->get( 'StoreClass' );
		if ( !class_exists( $storeClass ) ) {
			return \Status::newFatal( "Store class '$storeClass' not found" );
		}
		$store = new $storeClass();
		$schema = $store->getWriter( \RequestContext::getMain() )->getSchema();
		$data = array_intersect_key(
			$entity->getFullData(),
			array_flip( $schema->getIndexableFields() )
		);
		foreach ( $data as $key => $val ) {
			if ( !$schema[$key] ) {
				continue;
			}
			if ( $schema[$key][Schema::TYPE] === FieldType::DATE ) {
				$normalData[$key] = wfTimestamp( TS_ISO_8601, $val );
				continue;
			}

			$normalValue = $val;
			if ( $schema[$key][Schema::TYPE] === FieldType::STRING ) {
				$normalValue = strip_tags( $val );
			}
			if ( $schema[$key][Schema::TYPE] === FieldType::TEXT ) {
				$normalValue = strip_tags( $val );
			}
			if ( $schema[$key][Schema::TYPE] === FieldType::LISTVALUE ) {
				$normalValue = array_values( $val );
			}
			if ( $schema[$key][Schema::TYPE] === FieldType::BOOLEAN ) {
				$normalValue = $val ? true : false;
			}

			// this is currently not in use... we may implement normalizer
			// callback for any store writers in the feature
			if ( is_object( $val ) ) {
				$normalValue = \FormatJson::encode( $val );
			}

			$normalData[$key] = $normalValue;
		}

		unset( $entity );
		unset( $store );
		unset( $schema );

		return $normalData;
	}

}
