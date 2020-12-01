<?php

/**
 * @brief Database access layer for the @ref DataTable2.php "DataTable2"
 * extension.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup Extensions-DataTable2
 *
 * @author [RV1971](https://www.mediawiki.org/wiki/User:RV1971)
 *
 */

/**
 * @brief Auxiliary class to access the database tables of the @ref
 * Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 *
 * @sa [MediaWiki Manual:Database_access]
 * (https://www.mediawiki.org/wiki/Manual:Database_access)
 */
class DataTable2Database {
	/* == constants == */

	/**
	 * @brief Maximum number of fields in a table.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" By
	 * default, this extension allows up to 30 columns in a table. In
	 * the unlikely case that you would like to <b>enlarge the maximum
	 * number of columns</b>, you need to add columns to the table
	 * <tt>datatable2_data</tt> defined in the file
	 * <tt>datatable2_data.sql</tt> and to adapt the class constant
	 * DataTable2Database::MAX_FIELDS.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" By
	 * default, the first 10 columns are indexed. In order to <b>index
	 * more (or less) columns</b>, you need to create (or drop)
	 * indexes; see file <tt>datatable2_data.sql</tt>.
	 */
	const MAX_FIELDS = 30;

	/* == private data members == */

	private $columns_; ///< Array caching the results of getColumns().

	/* == public static functions == */

	/**
	 * @brief Return the database column name for a field index.
	 *
	 * @param int $i Field index (1 .. @ref MAX_FIELDS).
	 *
	 * @return string The column name in the database.
	 */
	public static function dataCol( $i ) {
		return sprintf( 'dtd_%02d', $i );
	}

	/**
	 * @brief Create an array of column names as used in the database.
	 *
	 * @param int $num Desired number of columns.
	 *
	 * @return string[] Array of column names, consisting of values
	 * dtd_<i>nn</i> with *nn* ranging from 1 to $num.
	 */
	public static function dataCols( $num ) {
		return array_map( 'self::dataCol', range( 1, $num ) );
	}

	/* == public functions == */

	/**
	 * @brief Return the array of logical column names for a table.
	 *
	 * @param string $table Logical table name.
	 *
	 * @param string $fname Caller function name.
	 *
	 * @return array Column names. Empty array if the table does not
	 * exist.
	 *
	 * @throws DataTable2Exception if the table has records but no
	 * meta data are found for the table.
	 */
	public function getColumns( $table, $fname = __METHOD__ ) {
		/** If the result is already cached in @ref $columns_, get
		 *	it from the cache and return.
		 */
		if ( isset( $this->columns_[$table] ) ) {
			return $this->columns_[$table];
		}

		$dbr = wfGetDB( DB_REPLICA );

		/** The table to select column names from is specified in
		 *	the global variable @ref $wgDataTable2MetaReadSrc.
		 */
		global $wgDataTable2MetaReadSrc;

		$res = $dbr->select( $wgDataTable2MetaReadSrc, 'dtm_columns',
			[ 'dtm_table' => $table ], $fname );

		if ( !$res->numRows() ) {
			/** If no meta data are found, check whether there are
			 * records for the table. Silently accept non-existing
			 * meta data if there are no rows.
			 */
			global $wgDataTable2ReadSrc;

			$res = $dbr->select( $wgDataTable2ReadSrc, 'dtd_table',
				[ 'dtd_table' => $table ], $fname,
				[ 'LIMIT' => 1 ] );

			if ( $res->numRows() ) {
				throw new DataTable2Exception(
					'datatable2-error-no-meta',
					htmlspecialchars( $table ) );
			} else {
				$this->columns_[$table] = [];

				return [];
			}
		}

		$columns = explode( '|', $res->fetchObject()->dtm_columns );

		/** Cache the result in @ref $columns_. */
		$this->columns_[$table] = $columns;

		return $columns;
	}

	/**
	 * @brief Delete data related to a specific page.
	 *
	 * @param int $pageId Page ID.
	 *
	 * @param string $fname Name of the calling function.
	 *
	 * @return bool Always TRUE.
	 */
	public function delete( $pageId, $fname = __METHOD__ ) {
		/** The table to delete from is specified in the global
		 *	variable @ref $wgDataTable2WriteDest.
		 */
		global $wgDataTable2WriteDest;

		$dbw = wfGetDB( DB_MASTER );

		// $dbw->begin( $fname );

		/** Delete all data for this page. */
		$dbw->delete( $wgDataTable2WriteDest,
			[ 'dtd_page' => $pageId ], $fname );

		/** The table to delete metadata from is specified in the global
		 *	variable @ref $wgDataTable2MetaWriteDest.
		 */
		global $wgDataTable2MetaWriteDest;

		/** Delete any metadata that has become unused, by this or
		 *	by any preceding delete operation.
		 */
		$subquery = $dbw->selectSQLText( $wgDataTable2WriteDest,
			'dtd_table', '', $fname );

		$dbw->delete( $wgDataTable2MetaWriteDest,
			[ "dtm_table not in ($subquery)" ], $fname );

		// $dbw->commit( $fname );

		return true;
	}

	/**
	 * @brief Save data from a wiki page.
	 *
	 * Save data to the database when an article is saved.
	 *
	 * @param WikiPage $article The page object.
	 *
	 * @param string $text The new article text.
	 *
	 * @param string $fname Name of the calling function.
	 *
	 * @return bool Always TRUE.
	 */
	function save( $article, $text, $fname = __METHOD__ ) {
		/** The table to save to is specified in the global
		 *	variable @ref $wgDataTable2WriteDest.
		 */
		global $wgDataTable2WriteDest;

		/** Extract data from all \<datatable2> tags on the
		 *	page.
		 */
		Parser::extractTagsAndParams( [ 'datatable2' ],
			$text, $datatables );

		/** Invoke Invoke DataTable2::deleteData() to delete all
		 *	existing data for the page.
		 */
		$this->delete( $article->getId(), $fname );

		$dbw = wfGetDB( DB_MASTER );

		// $dbw->begin( $fname );

		/** Loop through the \<datatable2> tags found. */
		foreach ( $datatables as $datatable ) {
			list( $element, $content, $args ) = $datatable;

			if ( !isset( $args['table'] ) || $args['table'] == '' ) {
				/** Nothing to do if the `table` argument is not
				 *	given.
				 */
				continue;
			}

			$table = DataTable2Parser::table2title( $args['table'] );

			/** Use DataTable2ParserWithRecords to parse the data
			 *	in each tag.
			 */
			$parser = new DataTable2ParserWithRecords( $content, $args,
				false );

			foreach ( $parser->getRecords() as $record ) {
				$dbRecord = array_combine(
					$this->dataCols( count( $record ) ), $record );

				$dbRecord['dtd_table'] = $table->getDBkey();
				$dbRecord['dtd_page'] = $article->getId();

				wfDebug( "**** here\n" );
				wfDebug( var_export( $dbRecord, true ) );

				/** Insert resulting records into the
				 *	database. Each record must be inserted
				 *	individually since the number of columns might
				 *	differ among records.
				 */
				$dbw->insert( $wgDataTable2WriteDest, $dbRecord, $fname );
			}

			/** The table to save metadata to is specified in the
			 *	global variable @ref
			 *	$wgDataTable2MetaWriteDest.
			 */
			global $wgDataTable2MetaWriteDest;

			$metaCond = [ 'dtm_table' => $table->getDBkey() ];

			$res = $dbw->select( $wgDataTable2MetaWriteDest, 'dtm_table',
				$metaCond, $fname );

			if ( $res->numRows() ) {
				/** Update the metadata record if there is one. */
				$dbw->update( $wgDataTable2MetaWriteDest,
					[ 'dtm_columns'
						=> implode( '|', $parser->getColumns() ) ],
					$metaCond, $fname );
			} else {
				/** Otherwise insert a new one. */
				$dbw->insert( $wgDataTable2MetaWriteDest,
					[ 'dtm_table' => $table->getDBkey(),
						'dtm_columns' =>
						implode( '|', $parser->getColumns() ) ],
					$fname );
			}
		}

		// $dbw->commit( $fname );

		return true;
	}

	/**
	 * @brief Select data.
	 *
	 * @param Title $table Logical table to select from.
	 *
	 * @param string|null $where WHERE clause or null.
	 *
	 * @param string|bool|null $orderBy ORDER BY clause, FALSE (to
	 * return results unsorted), or NULL (to sort by the first
	 * five columns).
	 *
	 * @param array|null &$pages Is returned as an array of distinct
	 * IDs of the pages where data was taken from.
	 *
	 * @param string $fname Name of the calling function.
	 *
	 * @return array Numerically-indexed array of associative
	 * arrays, each of which represents a record. Empty array if the
	 * table does not exist.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation"
	 * When displaying data on a page, all <b>pages where data are
	 * taken from</b> are added to the list of used templates. This
	 * implies that a page will not be taken from a cache if
	 * underlying data have changed, and that the data source pages
	 * will be listed as if they were templates used on the
	 * page. Hence, in the edit preview, dependencies will be shown as
	 * "templates used on this page" (potentially including the page
	 * itself), and on the other hand, in the "What links here" page
	 * of data source page, all pages using this data will be shown as
	 * if they transcluded the page. Unfortunately, there is currently
	 * no wiki feature that allows to distinguish this kind of
	 * dependency from a normal template dependency.
	 */
	public function select( $table, $where = null, $orderBy = null,
		&$pages = null, $fname = __METHOD__ ) {
		/** Work with a static instance of
		 *	DataTable2SqlTransformer.
		 */
		static $transformer;

		if ( !isset( $transformer ) ) {
			$transformer = new DataTable2SqlTransformer;
		}

		/** The table to select from is specified in the global
		 *	variable @ref $wgDataTable2ReadSrc.
		 */
		global $wgDataTable2ReadSrc;

		$conds = [ 'dtd_table' => $table->getDBkey() ];

		$columns = $this->getColumns( $table->getDBkey() );

		/** If getColumns() returns an empty array without
		 *	throwing, we know that there is no data and hence
		 *	return an empty array.
		 */
		if ( !$columns ) {
			return [];
		}

		$dbColumns = $this->dataCols( count( $columns ) );

		/** Always select page id as __pageId. */
		$columns[] = '__pageId';
		$dbColumns['__pageId'] = 'dtd_page';

		if ( isset( $where ) && $where != '' ) {
			$conds[] = $transformer->transform( $where, $columns );
		}

		/** If the ORDER BY clause is NULL, sort by the first five
		 *	columns.
		 */
		if ( isset( $orderBy ) && $orderBy !== '' ) {
			if ( $orderBy !== false ) {
				$orderBy = $transformer->transform( $orderBy, $columns );
			}
		} else {
			$orderBy = $this->dataCols( 5 );
		}

		/** Get the database records. */
		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select( $wgDataTable2ReadSrc, $dbColumns, $conds,
			$fname, $orderBy ? [ 'ORDER BY' => $orderBy ] : [] );

		/** Transform the query result into an array of arrays. */
		$records = [];
		$pageIds = [];

		foreach ( $res as $dbRecord ) {
			$pageIds[$dbRecord->__pageId] = true;

			$records[] = array_combine( $columns,
				get_object_vars( $dbRecord ) );
		}

		$pages = array_keys( $pageIds );

		return $records;
	}
}
