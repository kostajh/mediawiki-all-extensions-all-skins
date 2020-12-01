<?php

/**
 * @brief [Scribunto](https://www.mediawiki.org/wiki/Extension:Scribunto)
 * Lua library for the @ref Extensions-DataTable2.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup Extensions-DataTable2
 *
 * @author [RV1971](https://www.mediawiki.org/wiki/User:RV1971)
 */

/**
 * @brief [Scribunto](https://www.mediawiki.org/wiki/Extension:Scribunto)
 * Lua library for the @ref Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 */

class Scribunto_LuaDataTable2Library extends Scribunto_LuaLibraryBase {

	/* == private data members == */

	private $database_; ///< See @ref getDatabase().

	/* == magic methods == */

	/**
	 * @brief Constructor.
	 *
	 * Initialize data members.
	 *
	 * @param Scribunto_LuaEngine $engine Scribunto engine.
	 */
	public function __construct( $engine ) {
		parent::__construct( $engine );

		$this->database_ = new DataTable2Database;
	}

	/* == accessors == */

	/// Get the instance of DataTable2Database.
	public function getDatabase() {
		return $this->database_;
	}

	/* == special functions == */

	/// Register this library.
	public function register() {
		$lib = [
			'select' => [ $this, 'select' ]
		];

		$this->getEngine()->registerInterface(
			__DIR__ . '/../lua/DataTable2.lua',
			$lib, [] );
	}

	/* == functions to be called from Lua == */

	/**
	 * @brief Select records from the database.
	 *
	 * @param string $table Logical table to select from.
	 *
	 * @param string|null $where WHERE clause or null.
	 *
	 * @param string|null $orderBy ORDER BY clause or null.
	 *
	 * @return array Numerically-indexed array (with indexes
	 * starting at 1) of associative arrays, each of which represents
	 * a record. False if the table does not exist.
	 */
	public function select( $table, $where, $orderBy = null ) {
		/** Increment the expensive function count. */
		$this->incrementExpensiveFunctionCount();

		/** Get the records. */
		$tableObj = DataTable2Parser::table2title( $table );

		$records = $this->database_->select( $tableObj, $where, $orderBy,
			$pages, __METHOD__ );

		/** Renumber the records starting with 1, to match the Lua
		 * convention.
		 */
		if ( $records ) {
			$records = array_combine( range( 1, count( $records ) ),
				$records );
		}

		/** Call DataTable2::addDependencies_(). */
		DataTable2::singleton()->addDependencies(
			$this->getParser(), $pages, $tableObj );

		return [ $records ];
	}
}
