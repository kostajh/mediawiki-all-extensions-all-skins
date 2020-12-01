<?php

/**
 * @brief Class DpleFeatureUsedby.
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
 * @brief Selection of pages used by specified pages.
 *
 * Recognizes the parameters `usedby` and `notusedby`. Each of them
 * may be a string or an array. The results are the same as with [Extension:DynamicPageList (third-party)](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(third-party%29).
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleFeatureUsedby extends DpleFeatureLinksBase
implements DpleFeatureInterface {
	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		parent::__construct(
			$params, $features,
			'usedby', NS_MAIN,
			'templatelinks', 'tlx', 'tl_namespace',
			[ 'page_namespace = $table.tl_namespace',
			  'page_title = $table.tl_title',
			  '$table.tl_from = $id' ] );
	}
}
