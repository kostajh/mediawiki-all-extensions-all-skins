<?php

/**
 * @brief Class DpleFeatureLinksto.
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
 * @brief Selection by links to specified pages.
 *
 * Recognizes the parameters `linksto` and `notlinksto`. Each of them
 * may be a string or an array. The result set differs from the result
 * of "What links here" because it does not contain redirects, for
 * compatibility with [Extension:DynamicPageList
 * (third-party)](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(third-party%29).
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleFeatureLinksto extends DpleFeatureLinksBase
implements DpleFeatureInterface {
	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		parent::__construct(
			$params, $features,
			'linksto', NS_MAIN,
			'pagelinks', 'pl', 'pl_namespace',
			[ 'page_id = $table.pl_from',
			  '$table.pl_namespace = $ns',
			  '$table.pl_title = $dbkey' ] );
	}
}
