<?php

/**
 * @license GPL-2.0-or-later
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

class ApiLifeWeb extends \ApiQueryBase {

	public function execute() {
		$result = $this->getResult();

		$params = $this->extractRequestParams();
		if ( $params['what'] == 'taxonData' ) {
			$result->addValue( null, 'taxonData',
				\LifeWeb\Taxon::getTaxonData( $params['clearCache'] ) );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'characterData' ) {
			$result->addValue( null, 'characterData',
				\LifeWeb\Character::getCharacterData( $params['clearCache'] ) );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'questionData' ) {
			$result->addValue( null, 'questionData',
				\LifeWeb\Question::getQuestionData( $params['clearCache'] ) );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'degreeData' ) {
			$result->addValue( null, 'degreeData',
				\LifeWeb\Degree::getDegreeData( $params['clearCache'] ) );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'equipmentData' ) {
			$result->addValue( null, 'equipmentData',
				\LifeWeb\Equipment::getEquipmentData( $params['clearCache'] ) );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'difficultyData' ) {
			$result->addValue( null, 'difficultyData',
				\LifeWeb\Difficulty::getDifficultyData( $params['clearCache'] ) );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'collectionData' ) {
			$result->addValue( null, 'collectionData',
				\LifeWeb\Collection::getCollectionData( $params['clearCache'] ) );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'componentData' ) {
			$result->addValue( null, 'componentData',
				\LifeWeb\Component::getComponentData( $params['clearCache'] ) );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'topicData' ) {
			$result->addValue( null, 'topicData',
				\LifeWeb\Topic::getTopicData( $params['clearCache'] ) );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'questionEquipmentData' ) {
			$result->addValue( null, 'data', [] );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'taxonCollectionData' ) {
			$result->addValue( null, 'data', [] );
			$result->addValue( null, 'ok', 'true' );

		} elseif ( $params['what'] == 'taxonCharacterData' ) {
			$taxa = \LifeWeb\Taxon::getTaxonData();
			$arr = [];
			foreach ( $taxa as $taxon ) {
				foreach ( $taxon['characters'] as $character ) {
					$arr[] = [
						'taxon' => $taxon['id'],
						'character' => $character
					];
				}
			}

			$result->addValue( null, 'data', $arr );
			$result->addValue( null, 'ok', 'true' );

		} else {
			$result->addValue( null, 'error', 'Not supported: ' . $params['what'] );
			$result->addValue( null, 'ok', 'false' );
		}

		return $result;
	}

	public function getAllowedParams() {
		return [
			'what' => [
				ApiBase::PARAM_TYPE => [
					'topicData',
					'taxonData',
					'characterData',
					'questionData',
					'degreeData',
					'equipmentData',
					'difficultyData',
					'collectionData',
					'componentData',
					'questionEquipmentData',
					'taxonCharacterData',
					'taxonCollectionData'
				],
				ApiBase::PARAM_REQUIRED => true
			],
			'clearCache' => [
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
				ApiBase::PARAM_REQUIRED => false,
			]
		];
	}

	public function getExamples() {
		return [
			'api.php?action=query&list=LifeWeb&what=taxonData&format=json'
			=> 'apihelp-query+LifeWeb-example-1',
			'api.php?action=query&list=LifeWeb&format=jsonfm&what=question'
			=> 'apihelp-query+LifeWeb-example-2'
		];
	}
}
