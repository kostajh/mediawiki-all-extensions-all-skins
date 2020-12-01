<?php

/**
 * @brief Class DpleFeatureCheck.
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
 * @brief Check whether the whole specification is acceptable.
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */

class DpleFeatureCheck extends DpleFeatureBase
implements DpleFeatureInterface {
	/* == private static variables == */

	/// Whether the global configuration has been initialized.
	private static $intitialized_ = false;

	/* == public static methods == */

	/// Initialize the global configuration.
	public static function initConf() {
		if ( self::$intitialized_ ) {
			return;
		}

		self::$intitialized_ = true;

		global $wgDpleMaxCost;

		global $wgDLPmaxCategories, $wgDLPAllowUnlimitedCategories;

		/**
		 * If the global configuration variable @ref $wgDpleMaxCost is
		 * unset, initialize it with the corresponding configuration
		 * from
		 * [Extension:DynamicPageList](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(Wikimedia%29);
		 * if the latter is unset as well, initialize with the
		 * defaults from Extension:DynamicPageList.
		 *
		 * For simplicity of implementation, 'unlimited' is
		 * implemented just with unrealistically large numbers.
		 */
		if ( !isset( $wgDpleMaxCost ) ) {
			if ( isset( $wgDLPAllowUnlimitedCategories )
				&& $wgDLPAllowUnlimitedCategories ) {
				$wgDpleMaxCost = 1000;
			} elseif ( isset( $wgDLPmaxCategories ) ) {
				$wgDpleMaxCost = $wgDLPmaxCategories;
			} else {
				$wgDpleMaxCost = 6;
			}
		}
	}

	/* == private data members == */

	private $totalCost_; ///< Total cost of the query.

	/* == magic methods == */

	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		/** Call initConf(). */
		self::initConf();

		parent::__construct( $features );
	}

	/* == accessors == */

	/**
	 * @brief Get @ref $totalCost_.
	 *
	 * Supplies a value only after invocation of modifyQuery().
	 */
	public function getTotalCost() {
		return $this->totalCost_;
	}

	/* == operations == */

	/// Modify a given query. @copydetails DpleFeatureBase::modifyQuery()
	public function modifyQuery( DpleQuery &$query ) {
		global $wgDpleMaxCost;

		/** Throw an exception if there is no WHERE clause. */
		if ( !$query->getConds() ) {
			throw new Scribunto_LuaError(
				wfMessage( 'dple-error-no-criteria' )->text() );
		}

		/** Sum up the total cost. */
		$this->totalCost_ = 0;

		foreach ( $this->getFeatures() as $feature ) {
			$this->totalCost_ += $feature->getCost();
		}

		/** Throw an exception if @ref $totalCost_ exceeds @ref
		 *	$wgDpleMaxCost#.
		 */
		if ( $this->totalCost_ > $wgDpleMaxCost ) {
			throw new Scribunto_LuaError(
				wfMessage( 'dple-error-too-expensive',
					$this->totalCost_,
					$wgDpleMaxCost )->text() );
		}
	}
}
