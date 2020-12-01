<?php

namespace BlueSpice\Social\ExtendedSearch\Formatter;

use BlueSpice\EntityFactory;
use BlueSpice\Social\Entity;
use BlueSpice\Social\ExtendedSearch\Formatter\Internal\EntityFormatter as InternalEntityFormatter;
use BS\ExtendedSearch\Source\Base as Source;
use BS\ExtendedSearch\Source\Formatter\WikiPageFormatter;

class EntityFormatter extends WikiPageFormatter {
	/** @var EntityFactory */
	protected $entityFactory;

	/**
	 * @param Source $source
	 * @param EntityFactory $entityFactory
	 */
	public function __construct( $source, $entityFactory ) {
		parent::__construct( $source );

		$this->entityFactory = $entityFactory;
	}

	/**
	 *
	 * @param array $defaultResultStructure
	 * @return array
	 */
	public function getResultStructure( $defaultResultStructure = [] ) {
		$resultStructure = $defaultResultStructure;
		$resultStructure['headerText'] = 'entitydata.header';
		$resultStructure['page_anchor'] = 'page_anchor';
		$resultStructure['highlight'] = 'highlight';

		$resultStructure['secondaryInfos']['top']['items'][] = [
			"name" => "owner"
		];
		$resultStructure['secondaryInfos']['top']['items'][] = [
			"name" => "entity_type"
		];

		$resultStructure['featured']['highlight'] = "rendered_content_snippet";

		return $resultStructure;
	}

	/**
	 *
	 * @param array &$result
	 * @param \Elastica\Result $resultObject
	 * @return bool
	 */
	public function format( &$result, $resultObject ) {
		if ( $this->source->getTypeKey() !== $resultObject->getType() ) {
			return true;
		}

		parent::format( $result, $resultObject );

		$entity = $this->getEntityFromResult( $result );
		if ( !$entity ) {
			return true;
		}

		// Transfer formatting to internal formatter - different depending on the Entity type
		$internalFormatterClass = $entity->getConfig()->get( 'ExtendedSearchResultFormatter' );
		/** @var InternalEntityFormatter $internalFormatter */
		$internalFormatter = new $internalFormatterClass( $entity, $result, $resultObject );
		$internalFormatter->setLinkRenderer( $this->linkRenderer );
		$internalFormatter->format();
	}

	/**
	 *
	 * @param array &$results
	 * @param array $searchData
	 */
	public function formatAutocompleteResults( &$results, $searchData ) {
		parent::formatAutocompleteResults( $results, $searchData );
		foreach ( $results as &$result ) {
			if ( $result['type'] !== $this->source->getTypeKey() ) {
				continue;
			}

			$result['basename'] = $result['entitydata']['header'];
			$result['type'] .= " ({$result['entitydata']['type']})";

			$entity = $this->getEntityFromResult( $result );
			if ( !$entity ) {
				return;
			}

			$typeMsg = $this->getContext()->msg( $entity->getConfig()->get( 'TypeMessageKey' ) );
			$strippedHeader = strip_tags( $entity->getHeader() );
			$pageHeaderText = "({$typeMsg->plain()}) $strippedHeader";
			$result['page_anchor'] = $this->linkRenderer->makeLink(
				$entity->getTitle(), $pageHeaderText
			);
			$result['image_uri'] = $this->getImageUri(
				$entity->getTitle()->getPrefixedText(), 150
			);
		}
	}

	/**
	 *
	 * @param array &$results
	 * @param array $searchData
	 */
	public function rankAutocompleteResults( &$results, $searchData ) {
		foreach ( $results as &$result ) {
			if ( $result['type'] !== $this->source->getTypeKey() ) {
				parent::rankAutocompleteResults( $results, $searchData );
				continue;
			}

			$val = strtolower( $searchData['value'] );
			$header = strtolower( $result['entitydata']['header'] );
			if ( $header == $val ) {
				$result['rank'] = self::AC_RANK_TOP;
			} elseif ( strpos( $header, $val ) !== false ) {
				$result['rank'] = self::AC_RANK_NORMAL;
			} else {
				$result['rank'] = self::AC_RANK_SECONDARY;
			}

			$result['is_ranked'] = true;
		}
	}

	/**
	 * @param array $result
	 * @return Entity|null
	 */
	private function getEntityFromResult( $result ) {
		/** @var Entity $entity */
		$entity = $this->entityFactory->newFromID(
			$result['entitydata']['id'], $result['entitydata']['type']
		);
		if ( !$entity instanceof Entity || $entity->exists() == false ) {
			return null;
		}

		return $entity;
	}
}
