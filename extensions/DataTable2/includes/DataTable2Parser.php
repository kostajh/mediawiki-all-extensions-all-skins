<?php

/**
 * @brief Parsers for the @ref DataTable2.php "DataTable2" extension.
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
 * @brief Auxiliary class to parse the content of a \<datatable2> or
 * \<dt2-showtable> tag for the @ref Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 */
class DataTable2Parser {
	/* == public static methods == */

	/**
	 * @brief Non-emptiness test for use in array_filter().
	 *
	 * @param mixed $s Argument.
	 *
	 * @return bool FALSE if $s is unset or an empty string, else TRUE.
	 */
	public static function isNotEmpty( $s ) {
		return isset( $s ) && $s !== '';
	}

	/**
	 * @brief Transform a table name to a Title object.
	 *
	 * @param string $table Table name.
	 *
	 * @return Title Title object.
	 *
	 * @throws DataTable2Exception if $table is not a valid table
	 * name.
	 */
	public static function table2title( $table ) {
		/**
		 * @xrefitem userdoc "User Documentation" "User Documentation"
		 * Table names are treated like page titles: They must be
		 * composed of characters which are legal for page titles;
		 * spaces and underscores are equivalent; the first character
		 * is converted to uppercase if this is configured for titles.
		 */
		$title = Title::makeTitleSafe( NS_MAIN, trim( $table ) );

		if ( !$title ) {
			throw new DataTable2Exception( 'datatable2-error-table-name',
				htmlspecialchars( $table ) );
		}

		return $title;
	}

	/**
	 * @brief Extract a tag at the beginning of $input.
	 *
	 * Extract content of $tag at beginning of $input, if any, and
	 * remove it from $input.
	 *
	 * @param string &$input Input text.
	 *
	 * @param string $tag Tag name to look for.
	 *
	 * @return string|null Tag content, if any; NULL if no such
	 * tag or if tag is empty.
	 *
	 * @throws DataTable2Exception if an unterminated tag is
	 * encountered.
	 */
	public static function extractTag( &$input, $tag ) {
		/** Always remove leading whitespace. */
		$input = ltrim( $input );

		// length of tag plus surronding angle brackets
		$tagLen = strlen( $tag ) + 2;

		/** Then look for the opening tag. */
		if ( substr( $input, 0, $tagLen ) == "<$tag>" ) {
			$endPos = strpos( $input, "</$tag>", $tagLen );

			if ( $endPos === false ) {
				throw new DataTable2Exception(
					'datatable2-error-unterminated-tag',
					$tag, htmlspecialchars( $input ) );
			} else {
				$content = substr( $input, $tagLen, $endPos - $tagLen );

				$input = substr( $input, $endPos + $tagLen + 1 );

				/** Return extracted content, or null if empty. */
				return $content != '' ? $content : null;
			}
		}

		/** Return NULL if no tag was found. */
		return null;
	}

	/* == private data members == */

	private $args_;			///< See @ref getArgs().
	private $head_;			///< See @ref getHead().
	private $templateText_; ///< See @ref getTemplateText().
	private $text_;			///< See @ref getText().

	/* == magic methods == */

	/**
	 * @brief Constructor.
	 *
	 * @param string $input The text content of a \<datatable2>
	 * or \<dt2-showtable> tag.
	 *
	 * @param array|null $args Associative array of arguments indexed
	 * by attribute name.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation"
	 * A <b>table name</b> must obey the same rules as a valid
	 * page name. This was implemented in order to have a simple
	 * rule enforcing reasonable table names. However, table names
	 * are always case-sensitive, and no automatic uppercasing of
	 * the first character takes place.
	 *
	 * @sa DataTable2Parser::getArg() for a description of valid arguments.
	 */
	public function __construct( $input, $args = null ) {
		global $wgDataTable2Args;

		/** Initialize @ref $args_ with defaults from the global
		 *	variable @ref $wgDataTable2Args and merge with $args,
		 *	excluding arguments which are empty or null.
		 */
		$this->args_ = array_filter( (array)$args, 'self::isNotEmpty' )
			+ (array)$wgDataTable2Args;

		/** Transform the `table`argument to a Title object, if
		 *	any.
		 */
		if ( isset( $this->args_['table'] ) ) {
			$this->args_['table']
				= self::table2title( $this->args_['table'] );
		}

		/** Extract \<head> and \<template> tags, if any. */
		$this->head_ = self::extractTag( $input, 'head' );

		$this->templateText_ = self::extractTag( $input, 'template' );

		/** Assign the remaining $input to @ref $text_. */
		$this->text_ = $input;
	}

	/* == accessors == */

	/**
	 * @brief Get normalized arguments.
	 *
	 * @return array Arguments, including defaults where applicable.
	 */
	public function getArgs() {
		return $this->args_;
	}

	/**
	 * @brief Whether to wrap the records into a wiki table.
	 *
	 * Wrap into a wiki table if a head or a class is specified or no
	 * template is specified.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" You
	 * may provide a <b>table head</b> by including \<head>
	 * ... \</head> as the first thing in the \<datatable2> or
	 * \<dt2-showtable>. Do not include the opening {| into your head;
	 * this is automatically inserted by the extension.
	 *
	 * @return bool
	 */
	public function isToBeWrapped() {
		return isset( $this->head_ )
			|| isset( $this->args_['class'] )
			|| !isset( $this->args_['template'] )
			&& !isset( $this->templateText_ );
	}

	/**
	 * @brief Get an argument by key.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation"
	 * Arguments to \<datatable2> and \<dt2-showtable> are written as
	 * key-value pairs in the usual XML notation. The <b>valid
	 * argument keys</b> are:
	 * - <tt>args</tt> Additional arguments to pass to the template, in the
	 * usual wiki syntax, with or without names.
	 * - <tt>class</tt> CSS class for the table. Implies that the data are
	 * wrapped into a table.
	 * - <tt>debug</tt> Show the generated wikitext instead of interpreting it.
	 * - <tt>where</tt> WHERE clause. Only for \<dt2-showtable>.
	 * - <tt>order-by</tt> ORDER BY clause. Only for \<dt2-showtable>.
	 * - <tt>table</tt> Table where data is stored/retrieved. Mandatory for
	 * \<dt2-showtable>.
	 * - <tt>template</tt> Name of a template to use to display the data.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" Furthermore,
	 * \<datatable2> tags may have the following arguments:
	 * - <tt>columns</tt> Pipe-separated list of column names. Do not use
	 * names with leading underscores since those are reverved for
	 * internal use. You must specify names at least for those columns that
	 * you will use in WHERE clauses.
	 * - <tt>fs</tt> Field separator when parsing data. Either a string or a
	 * <a href="http://www.php.net/manual/en/pcre.pattern.php">PCRE</a>
	 * included in slashes.
	 * - <tt>rs</tt> Record separator when parsing data. Either a string or a
	 * <a href="http://www.php.net/manual/en/pcre.pattern.php">PCRE</a>
	 * included in slashes.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation"
	 * If you use the pipe character as a field separator (which is
	 * the default), you cannot use pipe characters in links, template
	 * invocations etc. in the data. You can either define a template
	 * `{{!}}` containing just the pipe character and use it instead, or
	 * set another <tt>fs</tt> in the tag or in the global variable
	 * @ref $wgDataTable2Args.
	 *
	 * @param string $key Argument key.
	 *
	 * @return mixed Argument value. May be a default value if no
	 * explicit non-empty argument was given. NULL if there is no such
	 * argument.
	 */
	public function getArg( $key ) {
		return isset( $this->args_[$key] ) ? $this->args_[$key] : null;
	}

	/**
	 * @brief Get content of the \<head> tag, if any.
	 *
	 * @return string|null Content of \<head> tag, if any;
	 * NULL if no such tag.
	 */
	public function getHead() {
		return $this->head_;
	}

	/**
	 * @brief Get content of the \<template> tag, if any.
	 *
	 * @return string|null Content of \<template> tag, if any;
	 * NULL if no such tag.
	 */
	public function getTemplateText() {
		return $this->templateText_;
	}

	/**
	 * @brief Get remaining text content.
	 *
	 * @return string Text with leading tags removed.
	 */
	public function getText() {
		return $this->text_;
	}
}

/**
 * @brief Auxiliary class to parse the content of a \<datatable2>
 * tag, splitting data into records for the @ref
 * Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 */
class DataTable2ParserWithRecords extends DataTable2Parser {
	/* == public static methods == */

	/**
	 * @brief Split text either by string or by [PCRE]
	 * (http://www.php.net/manual/en/pcre.pattern.php).
	 *
	 * @param string $delim Delimiter. Interpreted as a [PCRE]
	 * (http://www.php.net/manual/en/pcre.pattern.php) if it
	 * starts with a '/' character, otherwise as a string.
	 *
	 * @param string $input Input text.
	 *
	 * @return array Components.
	 */
	public static function split( $delim, $input ) {
	  if ( $delim[0] == '/' ) {
		  return preg_split( $delim, $input );
	  }

	  return explode( $delim, $input );
	}

	/* == private data members == */

	private $columns_; ///< See @ref getColumns().
	private $records_; ///< See @ref getRecords().

	/* == magic methods == */

	/**
	 * @brief Constructor.
	 *
	 * Invoke DataTable2Parser::__constructor, then split remaining
	 * text into records.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" In
	 * the <b>data</b>, xml comments (\<!-- ... -->) are detected and
	 * removed from each row. Comments spanning multiple rows and
	 * syntax errors such as double dashes appearing inside comments
	 * will not be detected.
	 *
	 * @param string $input The text content of a datatable2 tag.
	 *
	 * @param array|null $args Associative array of arguments indexed
	 * by attribute name.
	 *
	 * @param bool $assoc Whether the records returned by
	 * getRecords() should be indexed by column names.
	 *
	 * @sa DataTable2Parser::getArg() for a description of valid arguments.
	 */
	public function __construct( $input, array $args = null, $assoc = true ) {
		parent::__construct( $input, $args );

		$this->parseWiki_( $assoc );
	}

	/* == accessors == */

	/**
	 * @brief Get column names.
	 *
	 * @return array Column names, eventually including additional
	 * numeric keys if there are more fields than names.
	 */
	public function getColumns() {
		return $this->columns_;
	}

	/**
	 * @brief Get data records.
	 *
	 * @return array Numerically-indexed array of associative
	 * arrays, each of which represents a record.
	 */
	public function getRecords() {
		return $this->records_;
	}

	/* == private methods == */

	/**
	 * @brief Parse content in wiki format.
	 *
	 * Other input formats might be implemented in the future.
	 *
	 * @param bool $assoc Whether the records returned by
	 * getRecords() should be indexed by column names.
	 *
	 * @throws DataTable2Exception if the data need more columns
	 * than provided in the database.
	 */
	private function parseWiki_( $assoc ) {
		/** Parse list of column names, if any. */
		$this->columns_ = $this->getArg( 'columns' ) === null
			? [] : explode( '|', $this->getArg( 'columns' ) );

		/** Count column names and save the original count. */
		$origNameCount = count( $this->columns_ );
		$nameCount = $origNameCount;

		/** Split data into rows using split() with the `rs`
		 *	argument as a delimiter.
		 */
		$rows = self::split( $this->getArg( 'rs' ),
			trim( $this->getText() ) );

		/** Convert rows into records. */
		foreach ( $rows as $row ) {
			/** Strip xml comments from each row. */
			$row = preg_replace( '/<!--.*-->/U', '', $row );

			/** Trim whitespace surrounding a row. */
			$row = trim( $row );

			/** Ignore rows which are empty (after stripping xml
			 *	comments and trimming surrounding whitespace).
			 */
			if ( $row == '' ) {
				continue;
			}

			/** Split each row into fields using @ref split with
			 *	the `fs` argument as a delimiter.
			 */
			$fields = self::split( $this->getArg( 'fs' ), $row );

			$fieldCount = count( $fields );

			if ( $fieldCount > DataTable2Database::MAX_FIELDS ) {
				throw new DataTable2Exception(
					'datatable2-error-too-many-columns',
					htmlspecialchars( $row ),
					$fieldCount, DataTable2Database::MAX_FIELDS );
			}

			/** Enlarge names by numeric keys if there are more
			 * fields than names.
			 */
			if ( $fieldCount > $nameCount ) {
				$this->columns_ = array_merge( $this->columns_, range(
						$nameCount - $origNameCount + 1,
						$fieldCount - $origNameCount ) );

				$nameCount = $fieldCount;
			}

			/** If $assoc is true, index fields with column
			 *	names.
			 */
			if ( $assoc ) {
				if ( $fieldCount == $nameCount ) {
					$fields = array_combine( $this->columns_, $fields );
				} else {
					$fields = array_combine(
						array_slice( $this->columns_, 0, $fieldCount ),
						$fields );
				}
			}

			/** Add the result to @ref $records_. */
			$this->records_[] = $fields;
		}
	}
}
