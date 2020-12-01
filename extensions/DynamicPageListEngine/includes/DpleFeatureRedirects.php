<?php

/**
 * @brief Class DpleFeatureRedirects.
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
 * @brief Include or exclude redirects.
 *
 * Recognizes the parameter `redirects`, which may be one of
 * `exclude|include|only`. Default is `exclude`, for compatibility
 * with
 * [Extension:DynamicPageList](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(Wikimedia%29).
 * This implies that enabling this feature in @ref
 * $wgDpleFeatures may change the result set of a
 * dynamic page list even for parameter sets which do not contain the
 * `redirects` parameter.
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleFeatureRedirects extends DpleFeatureBase
implements DpleFeatureInterface {
	/* == private variables == */

	private $redirects_; ///< include|only|exclude.
	private $resolve_; ///< Whether to resolve redirects.

	/* == magic methods == */

	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		parent::__construct( $features );

		if ( isset( $params['redirects'] )
			&& $params['redirects'] == 'resolve' ) {
			$this->resolve_ = true;
			$params['redirects'] = 'only';
		}

		$this->redirects_ = $this->parseIncludeExclude(
			isset( $params['redirects'] ) ? $params['redirects'] : null );
	}

	/* == accessors == */

	/// Get @ref $redirects_.
	public function getRedirects() {
		return $this->redirects_;
	}

	/// Get @ref $resolve_.
	public function ifResolve() {
		return $this->resolve_;
	}

	/* == operations == */

	/// Modify a given query. @copydetails DpleFeatureBase::modifyQuery()
	public function modifyQuery( DpleQuery &$query ) {
		switch ( $this->redirects_ ) {
			case 'only':
				$query->addConds( [ 'page_is_redirect' => 1 ] );
				break;

			case 'exclude':
				$query->addConds( [ 'page_is_redirect' => 0 ] );
				break;
		}
	}
}
