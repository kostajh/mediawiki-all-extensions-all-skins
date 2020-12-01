<?php

/**
 * @brief Class DpleFeatureImagecontainer.
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
 * @brief Selection of images by pages that contain them.
 *
 * Recognizes the parameters `imagecontainer` and `notimagecontainer`. Each of
 * them may be a string or an array. The names are chosen for
 * compatibility with [Extension:DynamicPageList
 * (third-party)](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(third-party%29).
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleFeatureImagecontainer extends DpleFeatureLinksBase
implements DpleFeatureInterface {
	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		parent::__construct(
			$params, $features,
			'imagecontainer', NS_MAIN,
			'imagelinks', 'ilx', 'il_to',
			[ 'page_title = $table.il_to',
			  'page_namespace = ' . NS_FILE,
			  '$table.il_from = $id' ] );
	}
}
