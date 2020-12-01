<?php

namespace Wikibase\Lexeme\Search\Elastic\Tests;

use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;
use Wikibase\Lexeme\Domain\Model\Lexeme;
use Wikibase\Lexeme\Domain\Model\LexemeId;
use Wikibase\Lexeme\MediaWiki\Content\LexemeContent;
use Wikibase\Lexeme\MediaWiki\Content\LexemeHandler;
use Wikibase\Lib\SettingsArray;
use Wikibase\Repo\Content\EntityHandler;
use Wikibase\Repo\Tests\Content\EntityHandlerTestCase;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \Wikibase\Lexeme\MediaWiki\Content\LexemeHandler
 *
 * @license GPL-2.0-or-later
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class LexemeHandlerTest extends EntityHandlerTestCase {

	/**
	 * @return string
	 */
	public function getModelId() {
		return LexemeContent::CONTENT_MODEL_ID;
	}

	public function exportTransformProvider() {
		return [];
	}

	public function testSupportsRedirects() {
		$this->assertTrue( $this->getHandler()->supportsRedirects() );
	}

	/**
	 * @param SettingsArray|null $settings
	 *
	 * @return EntityHandler
	 */
	protected function getHandler( SettingsArray $settings = null ) {
		$wikibaseRepo = $this->getWikibaseRepo( $settings );
		$globalRepo = WikibaseRepo::getDefaultInstance();
		return new LexemeHandler(
			$globalRepo->getEntityContentDataCodec(),
			$wikibaseRepo->getEntityConstraintProvider(),
			$wikibaseRepo->getValidatorErrorLocalizer(),
			WikibaseRepo::getEntityIdParser(),
			$wikibaseRepo->getEntityIdLookup(),
			$wikibaseRepo->getEntityLookup(),
			$wikibaseRepo->getLanguageFallbackLabelDescriptionLookupFactory(),
			$wikibaseRepo->getFieldDefinitionsByType( Lexeme::ENTITY_TYPE )
		);
	}

	/**
	 * @param EntityId|null $id
	 *
	 * @return EntityDocument
	 */
	protected function newEntity( EntityId $id = null ) {
		if ( !$id ) {
			$id = new LexemeId( 'L7' );
		}

		$lexeme = new Lexeme( $id );
		$lexeme->setLemmas(
			new TermList(
				[
					new Term( 'en', 'goat' ),
					new Term( 'de', 'Ziege' ),
				]
			)
		);
		$lexeme->setLanguage( new ItemId( 'Q123' ) );
		$lexeme->setLexicalCategory( new ItemId( 'Q567' ) );

		return $lexeme;
	}

	/**
	 * Returns EntityContents that can be serialized by the EntityHandler deriving class.
	 *
	 * @return array[]
	 */
	public function contentProvider() {
		$content = $this->newEntityContent();

		return [
			[ $content ],
		];
	}

	/**
	 * @return array
	 */
	public function entityIdProvider() {
		return [
			[ 'L7' ],
		];
	}

	/**
	 * @return array
	 */
	protected function getExpectedSearchIndexFields() {
		return [ 'statement_count' ];
	}

	/**
	 * @return LexemeContent
	 */
	protected function getTestContent() {
		return $this->newEntityContent();
	}

	protected function getEntityTypeDefinitionsConfiguration(): array {
		return array_merge(
			wfArrayPlus2d(
				require __DIR__ . '/../../WikibaseSearch.entitytypes.repo.php',
				array_merge_recursive(
					require __DIR__ . '/../../../WikibaseLexeme/WikibaseLexeme.entitytypes.php',
					require __DIR__ . '/../../../WikibaseLexeme/WikibaseLexeme.entitytypes.repo.php'
				)
			),
			parent::getEntityTypeDefinitionsConfiguration()
		);
	}

	protected function getEntitySerializer() {
		$baseModelSerializerFactory = WikibaseRepo::getDefaultInstance()
			->getBaseDataModelSerializerFactory();
		$entityTypeDefinitions = $this->getEntityTypeDefinitions();
		$serializerFactoryCallbacks = $entityTypeDefinitions->getSerializerFactoryCallbacks();
		return $serializerFactoryCallbacks['lexeme']( $baseModelSerializerFactory );
	}

	public function testDataForSearchIndex() {
		$handler = $this->getHandler();
		$engine = $this->createMock( \SearchEngine::class );

		$page = $this->getMockWikiPage( $handler );

		// TODO: test with statements!
		$data = $handler->getDataForSearchIndex( $page, new \ParserOutput(), $engine );
		$this->assertSame( 0, $data['statement_count'], 'statement_count' );
	}

}
