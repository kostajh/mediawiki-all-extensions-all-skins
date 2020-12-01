<?php

/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

namespace LifeWeb;

class Query {

	/**
	 * @param string $language
	 * @param string $term
	 * @param string $type
	 * @return null|\Wikibase\EntityId
	 */
	static function searchEntity( $language, $term, $type = 'item' ) {
		$ids = \Wikibase\StoreFactory::getStore()->getTermIndex()->getMatchingIDs(
			[
				new \Wikibase\Term( [
					'termType' 		=> \Wikibase\Term::TYPE_LABEL,
					'termLanguage' 	=> $language,
					'termText' 		=> $term
				] )
			],
			$type,
			[
				'caseSensitive' => false,
				'prefixSearch' => false,
				'LIMIT' => 2,
			]
		);

		if ( count( $ids ) > 0 ) {
			return $ids[0];
		}
		return null;
	}

}
