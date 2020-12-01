<?php

/**
 * @brief Class DpleFeatureCount.
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
 * @brief Limit the number of records to fetch from the database.
 *
 * Recognizes the parameter `count` which limits the number of records
 * to fetch. Invalid values (including 0) are interpreted as 1, for
 * compatibility with
 * [Extension:DynamicPageList](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(Wikimedia%29).
 * In any case, the number is limited to @ref
 * $wgDpleMaxResultCount.
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleFeatureCount extends DpleFeatureBase
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

		global $wgDpleMaxResultCount;
		global $wgDLPMaxResultCount, $wgDLPAllowUnlimitedResults;

		/**
		 * If the global configuration variable @ref
		 * $wgDpleMaxResultCount is unset, initialize it with the
		 * corresponding configuration from
		 * [Extension:DynamicPageList](https://www.mediawiki.org/wiki/Extension:DynamicPageList_(Wikimedia%29);
		 * if the latter is unset as well, initialize with the
		 * defaults from Extension:DynamicPageList.
		 *
		 * For simplicity of implementation, 'unlimited' is
		 * implemented just with unrealistically large numbers.
		 */

		if ( !isset( $wgDpleMaxResultCount ) ) {
			if ( isset( $wgDLPAllowUnlimitedResults )
				&& $wgDLPAllowUnlimitedResults ) {
				$wgDpleMaxResultCount = 100000;
			} elseif ( isset( $wgDLPMaxResultCount ) ) {
				$wgDpleMaxResultCount = $wgDLPMaxResultCount;
			} else {
				$wgDpleMaxResultCount = 200;
			}
		}
	}

	/* == private variables == */

	private $count_; /** < @brief Maxium number of records to fetch. */

	/* == magic methods == */

	/// Constructor. Evaluate parameters.
	public function __construct( array $params, array &$features ) {
		self::initConf();

		parent::__construct( $features );

		$this->count_ = $this->parse( isset( $params['count'] )
			? $params['count'] : null );
	}

	/* == accessors == */

	/// Get @ref $count_.
	public function getCount() {
		return $this->count_;
	}

	/* == operations == */

	/**
	 * @brief Parse a count specification.
	 *
	 * @param int|string $param Parameter value.
	 *
	 * @return int Count.
	 */
	public function parse( $param ) {
		global $wgDpleMaxResultCount;

		if ( isset( $param ) ) {
			$count = intval( $param );

			if ( $count < 1 ) {
				/** Return 1 if $param is less then 1 (including the
				 *	case that it is invalid).
				 */
				$count = 1;
			} elseif ( $count > $wgDpleMaxResultCount ) {
				/** Return @ref $wgDpleMaxResultCount
				 *	if a larger value than that was requested.
				 */
				$count = $wgDpleMaxResultCount;
			}
		} else {
			$count = $wgDpleMaxResultCount;
		}

		return $count;
	}

	/// Modify a given query. @copydetails DpleFeatureBase::modifyQuery()
	public function modifyQuery( DpleQuery &$query ) {
		/** Set the LIMIT clause. */
		$query->setOption( 'LIMIT', $this->count_ );
	}
}
