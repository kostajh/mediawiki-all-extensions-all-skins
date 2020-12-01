<?php

/**
 * @brief Class DpleFeatureLinksfrom.
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
 * @brief Selection by links from specified pages.
 *
 * Recognizes the parameters `linksfrom` and `notlinksfrom`. Each of
 * them may be a string or an array. The names are chosen for
 * compatibility with [Extension:DynamicPageList
 * (third-party)](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(third-party%29).
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleFeatureLinksfrom extends DpleFeatureLinksBase
implements DpleFeatureInterface {
	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		parent::__construct(
			$params, $features,
			'linksfrom', NS_MAIN,
			'pagelinks', 'plx', 'pl_namespace',
			[ 'page_namespace = $table.pl_namespace',
			  'page_title = $table.pl_title',
			  '$table.pl_from = $id' ] );
	}
}
