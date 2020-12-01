<?php

namespace Wikibase\Lexeme\Search\Elastic\Tests;

use DataValues\StringValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\EntityLookup;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\Lexeme\Search\Elastic\LexemeLanguageField;

/**
 * @covers \Wikibase\Lexeme\Search\Elastic\LexemeLanguageField
 */
class LexemeLanguageFieldTest extends LexemeFieldTest {

	/**
	 * @return StatementList
	 */
	private function getStatList( PropertyId $propId, $code ) {
		$statList = new StatementList();
		$statList->addStatement( new Statement( new PropertyValueSnak( $propId,
			new StringValue( $code ) ) ) );
		return $statList;
	}

	/**
	 * @return EntityLookup
	 */
	private function getEntityLookup( StatementList $statList ) {
		$langEntity = $this->createMock( Item::class );
		$langEntity->method( 'getStatements' )->willReturn( $statList );

		$lookup = $this->createMock( EntityLookup::class );
		$lookup->method( 'getEntity' )
			->with( new ItemId( self::LANGUAGE_ID ) )
			->willReturn( $langEntity );
		return $lookup;
	}

	/**
	 * @return array
	 */
	public function getTestData() {

		$propId = new PropertyId( 'P42' );

		return [
			'no property id' => [
				new LexemeLanguageField( $this->createMock( EntityLookup::class ), null ),
				[
					'entity' => self::LANGUAGE_ID,
					'code' => null
				]
			],
			'no entity' => [
				new LexemeLanguageField( $this->createMock( EntityLookup::class ), $propId ),
				[
					'entity' => self::LANGUAGE_ID,
					'code' => null
				]
			],
			'with property id' => [
				new LexemeLanguageField(
					$this->getEntityLookup( $this->getStatList( $propId, 'fr' ) ),
					$propId ),
				[
					'entity' => self::LANGUAGE_ID,
					'code' => 'fr'
				]
			],
			'with property id, no statement' => [
				new LexemeLanguageField( $this->getEntityLookup( new StatementList() ), $propId ),
				[
					'entity' => self::LANGUAGE_ID,
					'code' => null
				]
			],
		];
	}

}
