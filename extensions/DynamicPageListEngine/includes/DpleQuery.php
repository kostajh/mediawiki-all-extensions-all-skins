<?php

/**
 * @brief Class DpleQuery.
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
 * @brief Database query specification and result.
 *
 * This class is simply a wrapper for the parameters and the result of
 * DatabaseBase::select().
 *
 * @ingroup Extensions
 * @ingroup Extensions-DynamicPageListEngine
 */
class DpleQuery implements Countable {
	/* == private data members == */

	private $dbr_; ///< DatabaseBase object.
	private $tables_; ///< Tables for DatabaseBase::select().
	private $vars_; ///< Fields for DatabaseBase::select().
	private $conds_; ///< WHERE conditions for DatabaseBase::select().
	private $options_; ///< Options for DatabaseBase::select().
	private $joinConds_; ///< Join conditions for DatabaseBase::select().
	private $result_; ///< ResultWrapper containing the query result.

	/* == magic methods == */

	/// Constructor.
	public function __construct( $tables = null, $vars = null, $conds = null,
		$options = null, $joinConds = null ) {
		/** Get a database object. */
		$this->dbr_ = wfGetDB( DB_REPLICA );

		/** Initialize the class members with the arguments. */

		$this->tables_ = (array)$tables;
		$this->vars_ = (array)$vars;
		$this->conds_ = (array)$conds;
		$this->options_ = (array)$options;
		$this->joinConds_ = (array)$joinConds;
	}

	/**
	 * @brief Implementation of Countable::count.
	 *
	 * @return int Number of result rows, or 0 if the query has not
	 * yet been executed.
	 */
	public function count() {
		if ( isset( $this->result_ ) ) {
			return $this->result_->numRows();
		} else {
			return 0;
		}
	}

	/* == accessors == */

	/// Get @ref $dbr_.
	public function getDbr() {
		return $this->dbr_;
	}

	/// Get @ref $tables_.
	public function getTables() {
		return $this->tables_;
	}

	/// Get @ref $vars_.
	public function getVars() {
		return $this->vars_;
	}

	/// Get @ref $conds_.
	public function getConds() {
		return $this->conds_;
	}

	/// Get @ref $options_.
	public function getOptions() {
		return $this->options_;
	}

	/// Get @ref $joinConds_.
	public function getJoinConds() {
		return $this->joinConds_;
	}

	/// Get @ref $result_.
	public function getResult() {
		return $this->result_;
	}

	/* == mutators == */

	/**
	 * @brief Add to @ref $tables_.
	 *
	 * @param string|array $tables Additional tables to query.
	 */
	public function addTables( $tables ) {
		$this->tables_ = array_merge( $this->tables_, (array)$tables );
	}

	/**
	 * @brief Add to @ref $vars_.
	 *
	 * @param string|array $vars Additional fields to query.
	 */
	public function addVars( $vars ) {
		$this->vars_ = array_merge( $this->vars_, (array)$vars );
	}

	/**
	 * @brief Add to @ref $conds_.
	 *
	 * @param string|array $conds Additional WHERE conditions.
	 */
	public function addConds( $conds ) {
		$this->conds_ = array_merge( $this->conds_, (array)$conds );
	}

	/**
	 * @brief Set an option in $_option.
	 *
	 * @param string $key Option key. To set options which do not have
	 * keys (such as DISTINCT), pass the option as $key and do not
	 * pass $value.
	 *
	 * @param mixed $value Option value.
	 */
	public function setOption( $key, $value = null ) {
		if ( isset( $value ) ) {
			$this->options_[$key] = $value;
		} else {
			$this->options_[] = $key;
		}
	}

	/**
	 * @brief Add to @ref $joinConds_.
	 *
	 * @param string $table Table name or alias.
	 *
	 * @param string $joinType `INNER JOIN`, `LEFT OUTER JOIN` etc.
	 *
	 * @param string|array $conds Join conditions.
	 *
	 * @param string|array $joinConds Additional JOIN conditions.
	 */
	public function addJoinCond( $table, $joinType, $conds ) {
		$this->joinConds_[$table] = [ $joinType, $conds ];
	}

	/* == operations == */

	/**
	 * @brief Execute the query and store the result in $result_.
	 *
	 * @param string $fname
	 * @return ResultWrapper $result_.
	 */
	public function execute( $fname = __METHOD__ ) {
		return $this->result_ = $this->dbr_->select(
			$this->tables_, $this->vars_, $this->conds_, $fname,
			$this->options_, $this->joinConds_ );
	}
}
