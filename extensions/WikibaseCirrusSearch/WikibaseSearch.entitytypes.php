<?php

/**
 * Search configs for entity types for use with Wikibase.
 */

use MediaWiki\MediaWikiServices;
use Wikibase\DataModel\Services\Lookup\InProcessCachingDataTypeLookup;
use Wikibase\Lib\EntityTypeDefinitions as Def;
use Wikibase\Lib\SettingsArray;
use Wikibase\Lib\Store\LanguageFallbackLabelDescriptionLookup;
use Wikibase\Repo\Api\CombinedEntitySearchHelper;
use Wikibase\Repo\Api\EntityIdSearchHelper;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Search\Elastic\EntitySearchElastic;
use Wikibase\Search\Elastic\Fields\DescriptionsProviderFieldDefinitions;
use Wikibase\Search\Elastic\Fields\ItemFieldDefinitions;
use Wikibase\Search\Elastic\Fields\LabelsProviderFieldDefinitions;
use Wikibase\Search\Elastic\Fields\PropertyFieldDefinitions;
use Wikibase\Search\Elastic\Fields\StatementProviderFieldDefinitions;

return [
	'item' => [
		Def::ENTITY_SEARCH_CALLBACK => function ( WebRequest $request ) {
			$repo = WikibaseRepo::getDefaultInstance();
			$entityIdParser = WikibaseRepo::getEntityIdParser();

			return new CombinedEntitySearchHelper(
				[
					new EntityIdSearchHelper(
						$repo->getEntityLookup(),
						$entityIdParser,
						new LanguageFallbackLabelDescriptionLookup(
							$repo->getTermLookup(),
							$repo->getLanguageFallbackChainFactory()->newFromLanguage( $repo->getUserLanguage() )
						),
						$repo->getEntityTypeToRepositoryMapping()
					),
					new EntitySearchElastic(
						$repo->getLanguageFallbackChainFactory(),
						$entityIdParser,
						$repo->getUserLanguage(),
						$repo->getContentModelMappings(),
						$request
					)
				]
			);
		},
		Def::SEARCH_FIELD_DEFINITIONS => function ( array $languageCodes, SettingsArray $searchSettings ) {
			$repo = WikibaseRepo::getDefaultInstance();
			$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'WikibaseCirrusSearch' );
			return new ItemFieldDefinitions( [
				new LabelsProviderFieldDefinitions( $languageCodes ),
				new DescriptionsProviderFieldDefinitions( $languageCodes, $config->get( 'UseStemming' ) ),
				StatementProviderFieldDefinitions::newFromSettings(
					new InProcessCachingDataTypeLookup( $repo->getPropertyDataTypeLookup() ),
					WikibaseRepo::getDataTypeDefinitions()->getSearchIndexDataFormatterCallbacks(),
					$searchSettings
				)
			] );
		},
		Def::FULLTEXT_SEARCH_CONTEXT => EntitySearchElastic::CONTEXT_WIKIBASE_FULLTEXT,
	],
	'property' => [
		Def::SEARCH_FIELD_DEFINITIONS => function ( array $languageCodes, SettingsArray $searchSettings ) {
			$repo = WikibaseRepo::getDefaultInstance();
			$services = MediaWikiServices::getInstance();
			$config = $services->getConfigFactory()->makeConfig( 'WikibaseCirrusSearch' );
			return new PropertyFieldDefinitions( [
				new LabelsProviderFieldDefinitions( $languageCodes ),
				new DescriptionsProviderFieldDefinitions( $languageCodes, $config->get( 'UseStemming' ) ),
				StatementProviderFieldDefinitions::newFromSettings(
					new InProcessCachingDataTypeLookup( $repo->getPropertyDataTypeLookup() ),
					WikibaseRepo::getDataTypeDefinitions( $services )
						->getSearchIndexDataFormatterCallbacks(),
					$searchSettings
				)
			] );
		},
		Def::ENTITY_SEARCH_CALLBACK => function ( WebRequest $request ) {
			$repo = WikibaseRepo::getDefaultInstance();
			$entityIdParser = WikibaseRepo::getEntityIdParser();

			return new \Wikibase\Repo\Api\PropertyDataTypeSearchHelper(
				new CombinedEntitySearchHelper(
					[
						new EntityIdSearchHelper(
							$repo->getEntityLookup(),
							$entityIdParser,
							new LanguageFallbackLabelDescriptionLookup(
								$repo->getTermLookup(),
								$repo->getLanguageFallbackChainFactory()->newFromLanguage( $repo->getUserLanguage() )
							),
							$repo->getEntityTypeToRepositoryMapping()
						),
						new EntitySearchElastic(
							$repo->getLanguageFallbackChainFactory(),
							$entityIdParser,
							$repo->getUserLanguage(),
							$repo->getContentModelMappings(),
							$request
						)
					]
				),
				$repo->getPropertyDataTypeLookup()
			);
		},
		Def::FULLTEXT_SEARCH_CONTEXT => EntitySearchElastic::CONTEXT_WIKIBASE_FULLTEXT,
	]
];
