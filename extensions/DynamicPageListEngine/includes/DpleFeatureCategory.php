<?php

/**
 * @brief Class DpleFeatureCategory.
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
 * @brief Selection by category.
 *
 * Recognizes the parameters `category` and `notcategory`. Each of
 * them may be a string or an array. If `category` is an array, the
 * result is the intersection of categories.
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleFeatureCategory extends DpleFeatureLinksBase
implements DpleFeatureInterface {
	/* == magic methods == */

	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		parent::__construct(
			$params, $features,
			'category', NS_CATEGORY,
			'categorylinks', 'cl', 'cl_to',
			[ 'page_id = $table.cl_from', '$table.cl_to = $dbkey' ] );
	}

	/* == operations == */

	/// Modify a given query. @copydetails DpleFeatureBase::modifyQuery()
	public function modifyQuery( DpleQuery &$query ) {
		parent::modifyQuery( $query );

		/** Also select timestamp if at least one category is specified. */
		if ( $this->getLinkedCount() ) {
			$query->addVars( [ 'cl_timestamp' => 'cl1.cl_timestamp' ] );
		}
	}
}
