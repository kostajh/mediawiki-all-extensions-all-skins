<?php

/**
 * @brief Class DpleFeatureUses.
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
 * @brief Selection of pages using specified pages.
 *
 * Recognizes the parameters `uses` and `notuses`. Each of them
 * may be a string or an array. The results are the same as with [Extension:DynamicPageList (third-party)](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(third-party)).
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleFeatureUses extends DpleFeatureLinksBase
implements DpleFeatureInterface {
	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		parent::__construct(
			$params, $features,
			'uses', NS_TEMPLATE,
			'templatelinks', 'tl', 'tl_namespace',
			[ 'page_id = $table.tl_from',
			  '$table.tl_namespace = $ns',
			  '$table.tl_title = $dbkey' ] );
	}
}
