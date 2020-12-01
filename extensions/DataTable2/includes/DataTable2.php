<?php

/**
 * @brief Main code for the @ref Extensions-DataTable2.
 *
 * @file
 *
 * @ingroup Extensions
 * @ingroup Extensions-DataTable2
 *
 * @author [RV1971](https://www.mediawiki.org/wiki/User:RV1971)
 */

use MediaWiki\MediaWikiServices;

/**
 * @brief Class implementing the @ref Extensions-DataTable2.
 *
 * @ingroup Extensions-DataTable2
 *
 * The diagram below shows the data flow:
 *
 * - For \<datatable2> tags, wikitext on a page is transformed into
 * PHP arrays using the DataTable2ParserWithRecords class, and the PHP
 * arrays are saved to the database in the columns `dtd_01` etc. via
 * DataTable2Database::save().
 *
 * - For \<dt2-showtable> tags as well as the parser functions
 * dt2-expand and dt2-get, database data is transformed into PHP
 * arrays via DataTable2Database::select().
 *
 * - For \<datatable2> and \<dt2-showtable> tags, PHP arrays
 * are transformed into HTML output via DataTable2::renderRecords().
 *
 * - For the parser functions dt2-expand, dt2-get and dt2-lastget, PHP
 * arrays are transformed into wikitext output via renderExpand(),
 * renderGet() and renderLastGet().
 *
 * @note The \<datatable2> tag shows data obtained from the page text
 * (even when they are saved to the database) while all other tags and
 * parser functions show data from the database. This makes a
 * difference when using the preview.
 *
 * @dot
 * digraph dataflow {
 * nodesep=.5;
 * node [shape="box",fontsize=10,fixedsize=true,height=.25,width=2,color="#c4cfe5"];
 *
 * page [label="Page (wikitext)"];
 * php [label="PHP code (array)"];
 * database [label="Database (dtd_01, ...)"];
 * output [label="Output (HTML)"];
 * wikitext [label="Output (wikitext)"];
 *
 * node [shape="ellipse",height=.5,fillcolor="#f9fafc",style=filled,fontcolor="#3d578c"];
 *
 * parse [label="DataTable2ParserWithRecords"];
 * render [label="renderRecords"];
 * render2 [label="renderExpand,\nrenderGet, renderLastGet"];
 *
 * subgraph db {
 * rank="same";
 * select [label="select"];
 * save [label="save"];
 * }
 *
 * page -> parse -> php;
 * database -> select -> php;
 * php -> save -> database;
 * php -> render -> output;
 * php -> render2 -> wikitext;
 * }
 * @enddot
 */

class DataTable2 {
	/* == public static methods == */

	/// Get an instance of this class.
	public static function &singleton() {
		static $instance;

		if ( !isset( $instance ) ) {
			$instance = new static;
		}

		return $instance;
	}

	/// Initialize this extension.
	public static function init() {
		global $wgDataTable2ReadSrc;
		global $wgDataTable2WriteDest;

		/** Set @ref $wgDataTable2ReadSrc to @ref
		 *	$wgDataTable2WriteDest if unset.
		 */
		if ( !isset( $wgDataTable2ReadSrc ) ) {
			$wgDataTable2ReadSrc = $wgDataTable2WriteDest;
		}

		global $wgDataTable2MetaReadSrc;
		global $wgDataTable2MetaWriteDest;

		/** Set @ref $wgDataTable2MetaReadSrc to @ref
		 *	$wgDataTable2MetaWriteDest if unset.
		 */
		if ( !isset( $wgDataTable2MetaReadSrc ) ) {
			$wgDataTable2MetaReadSrc = $wgDataTable2MetaWriteDest;
		}

		global $wgHooks;

		$wgHooks['ArticleDelete'][] = self::singleton();

		$wgHooks['LoadExtensionSchemaUpdates'][] = self::singleton();

		$wgHooks['NewRevisionFromEditComplete'][] = self::singleton();

		$wgHooks['ParserFirstCallInit'][] = self::singleton();

		$wgHooks['ParserTestTables'][] = self::singleton();
	}

	/**
	 * @brief [Workaround #1]
	 * (https://www.mediawiki.org/wiki/Manual:Special_pages#workaround_.231)
	 * for parsing included special pages.
	 *
	 * Tests have shown that in MW 1.21.1, this workaround is still
	 * necessary.
	 *
	 * @param string $wikiText Wiki text to parse.
	 * @param User $user User for parser options
	 *
	 * @return string HTML code.
	 */
	public static function sandboxParse( $wikiText, User $user ) {
		global $wgTitle;

		static $myParser;
		static $myParserOptions;

		if ( !isset( $myParser ) ) {
			$myParser = MediaWikiServices::getInstance()->getParserFactory()->create();
		}

		if ( !isset( $myParserOptions ) ) {
			$myParserOptions = ParserOptions::newFromUser( $user );
		}

		$result = $myParser->parse( $wikiText, $wgTitle, $myParserOptions );

		return $result->getText();
	}

	/* == private data members == */

	private $lastGet_; ///< Result of last invocation of renderGet().

	private $database_; ///< See @ref getDatabase.

	/* == magic methods == */

	public function __construct() {
		$this->database_ = new DataTable2Database;
	}

	/* == accessors == */

	/// Get the instance of DataTable2Database.
	public function getDatabase() {
		return $this->database_;
	}

	/* == event handlers == */

	/**
	 * @brief [ArticleDelete]
	 * (https://www.mediawiki.org/wiki/Manual:Hooks/ArticleDelete) hook.
	 *
	 * When an article is deleted, delete related data.
	 *
	 * @param WikiPage &$article The article that was deleted.
	 *
	 * @param User &$user The user deleting the article.
	 *
	 * @param string &$reason The reason the article is being deleted.
	 *
	 * @param string &$error If the requested article deletion was
	 * prohibited, the (raw HTML) error message to display.
	 *
	 * @return bool|string Success or failure.
	 */
	public function onArticleDelete( WikiPage &$article, User &$user,
		&$reason, &$error ) {
		/** Call DataTable2Database::delete(). */
		return $this->database_->delete( $article->getId(), __METHOD__ );
	}

	/**
	 * @brief [LoadExtensionSchemaUpdates]
	 * (https://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates)
	 * hook.
	 *
	 * Add the tables used to store DataTable2 data and metadata to
	 * the updater process.
	 *
	 * @param DatabaseUpdater $updater Object that updates the database.
	 *
	 * @return bool Always TRUE.
	 */
	public function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'datatable2_data',
			__DIR__ . '/../sql/datatable2_data.sql', true );
		$updater->addExtensionTable( 'datatable2_meta',
			__DIR__ . '/../sql/datatable2_meta.sql', true );

		return true;
	}

	/**
	 * @brief [NewRevisionFromEditComplete]
	 * (https://www.mediawiki.org/wiki/Manual:Hooks/NewRevisionFromEditComplete)
	 * hook.
	 *
	 * Save data and potentially metadata to the database when a
	 * revision is saved. This hook has been preferred over
	 * [ArticleSaveComplete]
	 * (https://www.mediawiki.org/wiki/Manual:Hooks/ArticleSaveComplete)
	 * because the latter is not executed when importing data from xml
	 * files.
	 *
	 * @param WikiPage $article The article edited.
	 *
	 * @param Revision $rev The new revision.
	 *
	 * @param int $baseID The revision ID this was based off, if any. For
	 * example, for a rollback, this will be the rev_id that is being
	 * rolled back to.
	 *
	 * @param User $user The revision author.
	 *
	 * @return bool Always true.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation"
	 * Since data are <b>saved to the database</b> using the <a
	 * href="https://www.mediawiki.org/wiki/Manual:Hooks/NewRevisionFromEditComplete">NewRevisionFromEditComplete</a>
	 * hook, no data is stored in the database when the page has not
	 * changed. Therefore, you should install this extension
	 * <i>before</i> creating \<datatable2> tags in your wiki
	 * pages. Otherwise, after installing the extension, you need to
	 * modify each page containing \<datatable2> tags in order to
	 * get the data actually saved.
	 */
	public function onNewRevisionFromEditComplete( $article, Revision $rev,
		$baseID, User $user ) {
		/** Call DataTable2Database::save(). */
		return $this->database_->save(
			$article, $rev->getContent()->getWikitextForTransclusion(),
			__METHOD__ );
	}

	/**
	 * @brief [ParserFirstCallInit]
	 * (https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit) hook.
	 *
	 * @param Parser &$parser Parser object being cleared.
	 *
	 * @return bool Always TRUE.
	 */
	public function onParserFirstCallInit( Parser &$parser ) {
		global $wgExtensionCredits, $wgHooks;

		/** Set [tag hooks](https://www.mediawiki.org/wiki/Manual:Tag
		 * extensions) and [parser function hooks]
		 * (https://www.mediawiki.org/wiki/Manual:Parser functions).
		 */
		$parser->setHook( 'datatable2', [ $this, 'renderDataTable' ] );
		$parser->setHook( 'dt2-showtable', [ $this, 'renderShowTable' ] );

		/** All parser functions get their arguments as PPNode
		 *	objects, so that arguments are expanded only when
		 *	needed. This is particularly useful if default values are
		 *	complex expressions and rarely needed.
		 */
		$parser->setFunctionHook( 'dt2-expand',
			[ $this, 'renderExpand' ], SFH_OBJECT_ARGS );
		$parser->setFunctionHook( 'dt2-get', [ $this, 'renderGet' ],
			SFH_OBJECT_ARGS );
		$parser->setFunctionHook( 'dt2-lastget',
			[ $this, 'renderLastGet' ],
			SFH_OBJECT_ARGS );

		/**
		 * Add Scribunto support if the [Scribunto
		 * Extension](https://www.mediawiki.org/wiki/Extension:Scribunto) is
		 * installed.
		 */
		if ( isset( $wgExtensionCredits['parserhook']['Scribunto'] ) ) {
			$wgHooks['ScribuntoExternalLibraries'][] = $this;
		}

		return true;
	}

	/**
	 * @brief [ParserTestTables]
	 * (https://www.mediawiki.org/wiki/Manual:Hooks/ParserTestTables) hook.
	 *
	 * Add the tables used to store DataTable2 data and metadata to
	 * the tables required for parser tests.
	 *
	 * @param array &$tables Tables needed to run parser tests.
	 *
	 * @return bool Always TRUE.
	 */
	public function onParserTestTables( &$tables ) {
		$tables[] = 'datatable2_data';
		$tables[] = 'datatable2_meta';

		return true;
	}

	/**
	 * @brief ScribuntoExternalLibraries hook.
	 *
	 * @param Scribunto_LuaEngine $engine Scribunto engine.
	 *
	 * @param array &$extraLibraries Libraries to register.
	 *
	 * @return bool Always TRUE.
	 */
	public function onScribuntoExternalLibraries( $engine,
		array &$extraLibraries ) {
		$extraLibraries['mw.ext.datatable2']
			= 'Scribunto_LuaDataTable2Library';

		return true;
	}

	/* == other public methods == */

	/**
	 * @brief Transform an array of arguments into wikitext.
	 *
	 * @param array|null $data Input data. Numerical indexes must start at 1.
	 *
	 * @return string Wikitext as used in template invocations.
	 */
	public function implodeArgs( $data ) {
		$result = [];

		/** If $array is not an array (e.g. NULL), the return value is
		 * an empty string.
		 */
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$result[] = "$key=$value";
			}
		}

		return implode( '|', $result );
	}

	/**
	 * @brief Merge argument arrays, re-indexing numeric keys.
	 *
	 * @param array $array1 Array to merge.
	 *
	 * @param array $array2 Array to merge.
	 *
	 * @return array Result.
	 */
	public function mergeArgs( $array1, $array2 ) {
		/** Items with non-numeric index in $array2 override the
		 * corresponding ones in $array1, items with numeric keys are
		 * appended.
		 */
		$tmp = array_merge( $array1, $array2 );

		$result = [];

		/** The numeric keys are re-indexed starting at 1. */
		foreach ( $tmp as $key => $value ) {
			if ( is_numeric( $key ) ) {
				$result[$key + 1] = $value;
			} else {
				$result[$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * @brief Create an array of properties from a Title.
	 *
	 * @param Title $title Page title.
	 *
	 * @return array Associative array.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" The
	 * <b>\<dt2-showtable></b> tag provides for each record also
	 * the arguments {{{dt2-src-fullpagename}}} and
	 * {{{dt2-src-pagename}}} which correspond to {{FULLPAGENAME}} and
	 * {{PAGENAME}} of the page which defines the record. All other
	 * page- and namespace-related variables (like SUBPAGENAME or
	 * TALKPAGENAMEE) can be derived from this with constructs like
	 * {{SUBPAGENAME:{{{dt2-src-fullpagename}}}}}.
	 */
	public function title2array( Title $title ) {
		return [
			'dt2-src-fullpagename' => $title->getPrefixedText(),
			'dt2-src-pagename' => $title->getText() ];
	}

	/**
	 * @brief Render a \<datatable2>
	 * [tag](https://www.mediawiki.org/wiki/Manual:Tag extensions).
	 *
	 * @param string $input Text between the \<datatable2> and
	 * \</datatable2> tags.
	 *
	 * @param array $args Associative array of arguments. In the
	 * wikipage, the arguments are entered as XML attributes of the
	 * \<datatable2> tag.
	 *
	 * @param Parser $parser The parent parser.
	 *
	 * @param PPFrame $frame The parent frame.
	 *
	 * @return string HTML text.
	 *
	 * @bug If several \<datatable2> tags provide data for the
	 * same table and have different values for the <tt>columns</tt>
	 * argument (including the case that some specify it while others
	 * don't), the column names found in the database will be those of
	 * the last \<datatable2> that was saved. Similarly, if
	 * several \<datatable2> tags provide data for the same table
	 * without specifying <tt>columns</tt> and the number of used
	 * columns differs between them, then the columns will be
	 * numbered, but the number of column numbers will be that of the
	 * last \<datatable2> saved. In both cases, the inconsistency
	 * will not be detected. To fix this, a warning message could be
	 * displayed here if the column names specified differ from those
	 * in the database. Such a warning should not be shown in the
	 * preview because the page being previewed might be the only
	 * point where data for this table are provided, and the purpose
	 * of the new revision might be to update the column names. The
	 * warning should link to all pages where data for this table is
	 * defined, and there is no obvious way to decide which of them
	 * generates the conflict.
	 */
	public function renderDataTable( $input, array $args, Parser $parser,
		PPFrame $frame ) {
		try {
			/** Use DataTable2ParserWithRecords to parse the data in
			 *	$input.
			 */
			$dataParser = new DataTable2ParserWithRecords( $input, $args );

			/** Add the page to the [tracking category]
			 * (https://www.mediawiki.org/wiki/Help:Tracking_categories)
			 * `datatable2-producer-category` if the data are saved.
			 *
			 * @xrefitem userdoc "User Documentation" "User Documentation"
			 * All pages storing data in DataTable2 tables are added
			 * to the <a
			 * href="https://www.mediawiki.org/wiki/Help:Tracking_categories">tracking
			 * category</a> defined by the system message
			 * <tt>datatable2-producer-category</tt>. You might decide
			 * to add some explanatory text to the category page.
			 */
			if ( isset( $args['table'] ) ) {
				$parser->addTrackingCategory(
					'datatable2-producer-category' );
			}

			/** Call DataTable2::renderRecords() to create
			 *	wikitext from the records.
			 */
			$wikitext = $this->renderRecords( $dataParser->getRecords(),
				$dataParser, $parser );

			/** Parse the wikitext, or display it verbatim for
			 *	debugging.
			 */

			return isset( $args['debug'] )
				? "<pre>$wikitext</pre>"
				: $parser->recursiveTagParse( $wikitext, $frame );
		} catch ( DataTable2Exception $e ) {
			return $e->getHTML();
		}
	}

	/**
	 * @brief Render a \<dt2-showtable>
	 * [tag](https://www.mediawiki.org/wiki/Manual:Tag extensions).
	 *
	 * @param string $input Input between the \<dt2-showtable> and
	 * \</dt2-showtable> tags.
	 *
	 * @param array $args Associative array of arguments. In the
	 * wikipage, the arguments are entered as XML attributes of the
	 * \<dt2-showtable> tag.
	 *
	 * @param Parser $parser The parent parser.
	 *
	 * @param PPFrame $frame The parent frame.
	 *
	 * @return string HTML text.
	 */
	public function renderShowTable( $input, array $args, Parser $parser,
		PPFrame $frame ) {
		try {
			/** Increment the [expensive function count]
			 * (https://www.mediawiki.org/wiki/Manual:$wgExpensiveParserFunctionLimit).
			 */
			if ( !$parser->incrementExpensiveFunctionCount() ) {
				throw new DataTable2Exception(
					'datatable2-error-expensive-function' );
			}

			/** @exception DataTable2Exception if no table specified. */
			if ( !$args['table'] ) {
				throw new DataTable2Exception( 'datatable2-error-table-name',
					'(empty string)' );
			}

			/** Use DataTable2Parser to parse the tag content, which
			 *	may contain a \<head> and/or a \<template> tag.
			 */
			$dataParser = new DataTable2Parser( $input, $args );

			/** Call DataTable2Database::select() to select the records
			 *	from the database.
			 */
			$records = $this->database_->select(
				$dataParser->getArg( 'table' ),
				$dataParser->getArg( 'where' ),
				$dataParser->getArg( 'order-by' ),
				$pages, __METHOD__ );

			/** Call DataTable2::renderRecords() to create wikitext
			 *	from the records.
			 */
			$wikitext = $this->renderRecords( $records, $dataParser,
				$parser );

			/** Call DataTable2::addDependencies(). */
			if ( $pages ) {
				$this->addDependencies( $parser, $pages,
					DataTable2Parser::table2title(
						$dataParser->getArg( 'table' ) ) );
			}

			/** Parse the wikitext, or display it verbatim for
			 *	debugging.
			 */
			return isset( $args['debug'] )
				? "<pre>$wikitext</pre>"
				: $parser->recursiveTagParse( $wikitext, $frame );
		} catch ( DataTable2Exception $e ) {
			return $e->getHTML();
		}
	}

	/**
	 * @brief Render a dt2-expand [parser function]
	 * (https://www.mediawiki.org/wiki/Manual:Parser functions).
	 *
	 * @param Parser &$parser Parent parser.
	 *
	 * @param PPFrame $frame The parent frame.
	 *
	 * @param array $args PPNode objects for the template arguments.
	 *
	 * @return string Wikitext containing a template with data as
	 * arguments.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" The
	 * <b>dt2-expand</b> parser function takes three or more arguments:
	 * - The name of the <i>template</i> to expand.
	 * - The <i>table</i> defined with the \<datatable2> tag where the
	 * data should be taken from.
	 * - The <i>where</i> clause, that should select at most one record.
	 * If more than one record is found, an error message is returned.
	 * - Optionally the <i>default text</i> to return if no data are found.
	 * It is expanded only if needed, so using a complex template here does
	 * not lead to performance issues if used for unexpected errors only.
	 * - Optionally further arguments that are appended to those selected
	 * from the database.
	 */
	public function renderExpand( Parser &$parser, PPFrame $frame, $args ) {
		try {
			/** Return error message if less then 3 arguments are
			 *	provided.
			 */
			if ( count( $args ) < 3 ) {
				throw new DataTable2Exception(
					'datatable2-error-too-few-args',
					'dt2-get', count( $args ), 3 );
			}

			/** Increment the [expensive function count]
			 * (https://www.mediawiki.org/wiki/Manual:$wgExpensiveParserFunctionLimit).
			 */
			if ( !$parser->incrementExpensiveFunctionCount() ) {
				throw new DataTable2Exception(
					'datatable2-error-expensive-function' );
			}

			$template = $frame->expand( $args[0] );
			$table = DataTable2Parser::table2title(
				$frame->expand( $args[1] ) );
			$where = $frame->expand( $args[2] );

			/** Get unsorted data from the database. */
			$data = $this->database_->select( $table, $where,
				false, $pages, __METHOD__ );

			/** Return error message if more than one record is found. */
			if ( count( $data ) > 1 ) {
				throw new DataTable2Exception(
					'datatable2-error-multiple-records',
					$table->getText(), htmlspecialchars( $where ),
					count( $data ) );
			}

			/** Return default if no record is selected; empty string if
			 *	default is unset.
			 */
			if ( !count( $data ) ) {
				return isset( $args[3] ) ? $frame->expand( $args[3] ) : '';
			}

			/** Call DataTable2::addDependencies. */
			$this->addDependencies( $parser, $pages, $table );

			/** Compose array of template arguments, appending further
			 *	parser function arguments (if any) to the dtaa got
			 *	from the database.
			 */
			$templateArgs = current( $data );

			for ( $i = 4; array_key_exists( $i, $args ); $i++ ) {
				$templateArgs[$i - 3] = $frame->expand( $args[$i] );
			}

			/** Return preprocessed template with these arguments. */
			return [ $parser->recursivePreprocess(
					$parser->fetchTemplateAndTitle(
						Title::newFromText( $template, NS_TEMPLATE ) )[0],
					$parser->getPreprocessor()->newCustomFrame(
						$templateArgs ) ), 'noparse' => false ];
		} catch ( DataTable2Exception $e ) {
			return $e->getText();
		}
	}

	/**
	 * @brief Render a dt2-get [parser function]
	 * (https://www.mediawiki.org/wiki/Manual:Parser functions).
	 *
	 * @param Parser &$parser Parent parser.
	 *
	 * @param PPFrame $frame The parent frame.
	 *
	 * @param array $args PPNode objects for the template arguments.
	 *
	 * @return string Content of the selected column.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" The
	 * <b>dt2-get</b> parser function returns the content of the
	 * selected column (if any) if a record is found. If no column is
	 * specified, the record is retrieved from that database but
	 * nothing is returned. If no record is found, the default (if
	 * any) is returned, even if no column was specified. You can use
	 * this in conditional constructs to test first whether there is a
	 * record, and if so, to display its data in some way. The
	 * parser function takes three or four arguments:
	 * - The <i>table</i> defined with the \<datatable2> tag where the
	 * data should be taken from.
	 * - The <i>column</i> name defined with the \<datatable2> tag; if
	 * no column names have been defined in the \<datatable2> tag,
	 * they are numbered starting with 1. The column field may be left
	 * blank, in which case, data is retrieved for later usage with
	 * the dt2-lastget parser function, but nothing is displayed.
	 * - The <i>where</i> clause that should select at most one
	 * record. If more than one record is found, an error message is
	 * returned.
	 * - Optionally the <i>default text</i> to return if no data are found.
	 * It is expanded only if needed, so using a complex template here does
	 * not lead to performance issues if used for unexpected errors only.
	 */
	public function renderGet( Parser &$parser, PPFrame $frame, $args ) {
		try {
			/** Return error message if less then 3 arguments are
			 *	provided.
			 */
			if ( count( $args ) < 3 ) {
				throw new DataTable2Exception(
					'datatable2-error-too-few-args',
					'dt2-get', count( $args ), 3 );
			}

			/** Increment the [expensive function count]
			 * (https://www.mediawiki.org/wiki/Manual:$wgExpensiveParserFunctionLimit).
			 */
			if ( !$parser->incrementExpensiveFunctionCount() ) {
				throw new DataTable2Exception(
					'datatable2-error-expensive-function' );
			}

			$table = DataTable2Parser::table2title(
				$frame->expand( $args[0] ) );
			$where = $frame->expand( $args[2] );

			/** Get unsorted data from the database. */
			$data = $this->database_->select( $table, $where,
				false, $pages, __METHOD__ );

			/** Return error message if more than one record is found. */
			if ( count( $data ) > 1 ) {
				throw new DataTable2Exception(
					'datatable2-error-multiple-records',
					$table->getText(), htmlspecialchars( $where ),
					count( $data ) );
			}

			/** If no record is selected, clear @ref
			 *	$lastGet_ and return default; return
			 *	empty string if default is unset.
			 */
			if ( !count( $data ) ) {
				$this->lastGet_ = null;
				return isset( $args[3] ) ? $frame->expand( $args[3] ) : '';
			}

			/** Call DataTable2::addDependencies. */
			$this->addDependencies( $parser, $pages, $table );

			/** Save result in @ref $lastGet_. */
			$this->lastGet_ = current( $data );

			/** If a column was specified, return its content. No
			 *	check is performed whether the column exists.
			 */
			if ( $args[1] != '' ) {
				$column = $frame->expand( $args[1] );

				if ( isset( $this->lastGet_[$column] ) ) {
					return [ $this->lastGet_[$column],
						'noparse' => false ];
				} else {
					return '';
				}
			}

			return '';
		} catch ( DataTable2Exception $e ) {
			return $e->getText();
		}
	}

	/**
	 * @brief Render a dt2-lastget [parser function]
	 * (https://www.mediawiki.org/wiki/Manual:Parser functions).
	 *
	 * @param Parser &$parser Parent parser.
	 *
	 * @param PPFrame $frame The parent frame.
	 *
	 * @param array $args PPNode objects for the template arguments.
	 *
	 * @return string Content of the selected column.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" The
	 * <b>dt2-lastget</b> parser function returns the content of the
	 * selected column in the data row fetched with the last
	 * invocation of dt2-get. This is much more efficient than
	 * invoking dt2-get a second time and therefore does not increment the [expensive function count]
	 * (https://www.mediawiki.org/wiki/Manual:$wgExpensiveParserFunctionLimit).
	 * If dt2-get has not yet been called or the last invocation has
	 * not found any records, the default is returned. The parser
	 * function takes one or two arguments:
	 * - The <i>column</i> name defined with the \<datatable2> tag; if
	 * no column names have been defined in the \<datatable2> tag,
	 * they are numbered starting with 1.
	 * - Optionally the <i>default text</i> to return if no data are found.
	 * It is expanded only if needed, so using a complex template here does
	 * not lead to performance issues if used for unexpected errors only.
	 */
	public function renderLastGet( Parser &$parser, PPFrame $frame, $args ) {
		try {
			/** Return error message if no arguments are provided. */
			if ( !$args ) {
				throw new DataTable2Exception(
					'datatable2-error-too-few-args',
					'dt2-lastget', count( $args ), 1 );
			}

			/** Return default if there is no record available; empty
			 *	string if default is unset.
			 */
			if ( !isset( $this->lastGet_ ) ) {
				return isset( $args[1] ) ? $frame->expand( $args[1] ) : '';
			}

			/** Otherwise return the specified column. No check is
			 *	performed whether the column exists.
			 */
			return [ $this->lastGet_[$frame->expand( $args[0] )],
				'noparse' => false ];
		} catch ( DataTable2Exception $e ) {
			return $e->getText();
		}
	}

	/**
	 * @brief Render data records in (more or less) tabular form.
	 *
	 * @param array|null $records Numerically-indexed array of associative
	 * arrays, each of which represents a record.
	 *
	 * @param DataTable2Parser $dataParser Parser object for the
	 * \<datatable2> or \<dt2-showtable> contents.
	 *
	 * @param Parser $parser The parent parser.
	 *
	 * @return string Wikitext.
	 */
	public function renderRecords( $records,
		DataTable2Parser $dataParser, Parser $parser ) {
		$wikitext = '';

		$head = $dataParser->getHead();

		$isToBeWrapped = $dataParser->isToBeWrapped();

		if ( $isToBeWrapped ) {
			if ( !isset( $head ) ) {
				$head = '';
			} elseif ( substr( $head, -1 ) != "\n" ) {
				/** If there is a user-supplied head that does not
				 *	end with a newline, append a newline.
				 */
				$head .= "\n";
			}

			$classAttr = $dataParser->getArg( 'class' ) !== null
				? "class='{$dataParser->getArg( 'class' )}'" : '';

			$wikitext .= "{| $classAttr\n$head";
		}

		/** Parse `args` argument, if any. */
		if ( $dataParser->getArg( 'args' ) !== null ) {
			$args = self::createAssocArgs(
				explode( '|', $dataParser->getArg( 'args' ) ) );
		} else {
			$args = null;
		}

		/** Call DataTable2::renderRecord() to create wikitext
		 *	from each record.
		 */
		if ( isset( $records ) ) {
			foreach ( $records as $record ) {
				$wikitext .= $this->renderRecord( $record,
					$dataParser->getArg( 'template' ),
					$dataParser->getTemplateText(),
					$args, $parser );
			}
		}

		/** Close table, if any. */
		if ( $isToBeWrapped ) {
			$wikitext .= "\n|}";
		}

		return $wikitext;
	}

	/**
	 * Clean up argument array - borrowed from a deprecated/unused function
	 * in core's Parser.php.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private static function createAssocArgs( $args ) {
		$assocArgs = [];
		$index = 1;
		foreach ( $args as $arg ) {
			$eqpos = strpos( $arg, '=' );
			if ( $eqpos === false ) {
				$assocArgs[$index++] = $arg;
			} else {
				$name = trim( substr( $arg, 0, $eqpos ) );
				$value = trim( substr( $arg, $eqpos + 1 ) );
				if ( $value === false ) {
					$value = '';
				}
				if ( $name !== false ) {
					$assocArgs[$name] = $value;
				}
			}
		}

		return $assocArgs;
	}

	/**
	 * @brief Render a record.
	 *
	 * @param array $record Associative array representing the data record.
	 *
	 * @param string|null $template A template name.
	 *
	 * @param string|null $templateText A template text.
	 *
	 * @param string|null $args Associative array of args to prepend
	 * to each record.
	 *
	 * @param Parser $parser The parent parser.
	 *
	 * @return string Wikitext.
	 */
	public function renderRecord( array $record, $template,
		$templateText, $args, Parser $parser ) {
		/** Prepend $args to $record, if set. */
		if ( isset( $args ) ) {
			$record = $this->mergeArgs( $args, $record );
		}

		/** If __pageId is present, create a Title object out of
		 *	it.
		 */
		if ( isset( $record['__pageId'] ) ) {
			$sourceTitle = Title::newFromID( $record['__pageId'] );

			/** If as template name or text is provided, append the
			 *	data about the source page to the record.
			 */
			if ( isset( $template ) || isset( $templateText ) ) {
				$record += $this->title2array( $sourceTitle );
			}
		}

		if ( isset( $template ) ) {
			/** If a template name is given, use that template. */
			$result = '{{' . "$template|"
				. $this->implodeArgs( $record ) . '}}';
		} elseif ( isset( $templateText ) ) {
			/** Else if a template text is given, use it. */
			$result = $parser->recursivePreprocess( $templateText,
				$parser->getPreprocessor()->newCustomFrame( $record ) );
		} else {
			/** Else format record as a table row, inserting a
			 * line break after each pipe character so that wiki
			 * markup like * etc. can be used within content.
			 */

			$result = "|-\n|\n"
				. implode( "\n|\n", array_values( $record ) ) . "\n";

			/** Append a link to the source page, if any, as the
			 *	last field.
			 */
			if ( isset( $sourceTitle ) ) {
				$result .= "| [[{$sourceTitle->getPrefixedText()}]]\n";
			}
		}

		return $result;
	}

	/**
	 * @brief Add to tracking categories, handle caching, and add
	 * dependencies on pages defining data.
	 *
	 * @xrefitem userdoc "User Documentation" "User Documentation" All
	 * pages using data from DataTable2 tables are added to the <a
	 * href="https://www.mediawiki.org/wiki/Help:Tracking_categories">tracking
	 * category</a> defined by the system message
	 * <tt>datatable2-consumer-category</tt>. Furthermore, these pages
	 * will be added to individual tracking categories for each table
	 * used. The names of these tracking categories are created from
	 * the system message
	 * <tt>datatable2-consumer-detail-category</tt>. You might decide
	 * to add some explanatory text to the category pages. As usual,
	 * all tracking categories can be disabled by setting the
	 * respective message to a single dash.
	 *
	 * @param Parser $parser The parent parser.
	 *
	 * @param array $pages Distinct IDs of the pages where data was
	 * taken from. This may include the page itself.
	 *
	 * @param Title $table Logical table name.
	 *
	 * @sa The source code to set the detail tracking category is
	 * largely inspired by the source code of
	 * Parser::addTrackingCategory().
	 */
	public function addDependencies( Parser $parser, array $pages,
		Title $table ) {
		/** Add this page to the [tracking category]
		 * (https://www.mediawiki.org/wiki/Help:Tracking_categories)
		 * defined by the message `datatable2-consumer-category`.
		 */
		$parser->addTrackingCategory( 'datatable2-consumer-category' );

		/** Add to the detail tracking category created from the
		 *	message `datatable2-consumer-detail-category` unless
		 *	that message is a single dash.
		 */
		$detailTrackingCategoryName = wfMessage(
			'datatable2-consumer-detail-category', $table->getText() )
			->title( $parser->getTitle() )
			->inContentLanguage()
			->text();

		if ( $detailTrackingCategoryName !== '-' ) {
			$detailTrackingCategory = Title::makeTitleSafe(
				NS_CATEGORY, $detailTrackingCategoryName );

			if ( $detailTrackingCategory ) {
				$parser->getOutput()->addCategory(
					$detailTrackingCategory->getDBkey(),
					$parser->getDefaultSort() );
			} else {
				wfDebug( __METHOD__
					. ": [[MediaWiki:datatable2-consumer-detail-category]] is not a valid title!\n" );
			}
		}

		/** Add [dependencies]
		 * (https://www.mediawiki.org/wiki/Manual:Tag_extensions#How_do_I_disable_caching_for_pages_using_my_extension.3F)
		 * on those pages where data is taken from.
		 */
		$revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		foreach ( $pages as $pageId ) {
			/** Disable caching completely if the page uses data
			 *	from a non-wiki source.
			 */
			if ( !is_int( $pageId ) ) {
				$parser->getOutput()->updateCacheExpiry( 0 );
				continue;
			}

			$page = WikiPage::newFromID( $pageId );

			if ( isset( $page ) ) {
				$revisionRecord = $revisionLookup->getRevisionByPageId( $pageId );
				if ( $revisionRecord !== null ) {
					$parser->getOutput()->AddTemplate(
						$page->getTitle(),
						$pageId,
						$revisionRecord->getId()
					);
				}
			}
		}
	}
}
