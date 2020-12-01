<?php

/**
 * @brief Class DpleFeatureImageused.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 *
 * @author [RV1971](https://www.mediawiki.org/wiki/User:RV1971)
 *
 */

/**
 * @brief Selection by links to images.
 *
 * Recognizes the parameters `imageused` and `notimageused`. Each of them
 * may be a string or an array. The names are chosen for
 * compatibility with [Extension:DynamicPageList
 * (third-party)](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(third-party%29).
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleFeatureImageused extends DpleFeatureLinksBase
implements DpleFeatureInterface {
	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		parent::__construct(
			$params, $features,
			'imageused', NS_FILE,
			'imagelinks', 'il', 'il_to',
			[ 'page_id = $table.il_from', '$table.il_to = $dbkey' ] );
	}
}
