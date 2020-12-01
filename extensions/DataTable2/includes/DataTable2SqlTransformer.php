<?php

/**
 * @brief SQL Transformer for the @ref DataTable2.php "DataTable2"
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
 * @brief Auxiliary class to transform an SQL expression from logical
 * to database representation for the @ref Extensions-DataTable2.
 *
 * The tokenizer has a very simple approach: it uses the next charcter
 * to decide which kind of token is the next one, and then uses a
 * regexp to get the token. Special rules are applied to recognize
 * C-style and SQL-style comments. This is sufficient to distinguish
 * identifiers from quoted strings, and we do not need more to
 * accomplish the task of this class.
 *
 * @ingroup Extensions-DataTable2
 */
class DataTable2SqlTransformer {
	/* == public constants == */

	const INVALID = -1;	  ///< Token is invalid.
	const SPACE = 0;	  ///< Token is whitespace.
	const NUMBER = 1;	  ///< Token is a number.
	const STRING = 2;	  ///< Token is a quoted string.
	const IDENTIFIER = 3; ///< Token is (possibly quoted) identifier.
	const MATH = 4;		  ///< Token is a math character.
	const COMMA = 5;	  ///< Token is a comma.

	/* == private static data members == */

	/**
	 * @brief Array mapping each token type to the set of characters
	 * it may start with.
	 *
	 * Any character not mentioned in any of these introduced an @ref
	 * INVALID token.
	 */
	private static $tokenTypes_ = [
		self::SPACE => " \t\n\r",
		self::NUMBER => '0123456789.',
		self::STRING => '\'',
		self::IDENTIFIER
		=> '"ABCDEFGHIJKLMNOPQRSTUVWXYZ_`abcdefghijklmnopqrstuvwxyz',
		self::MATH => '!()*+-/<=>|',
		self::COMMA => ','
	];

	/**
	 * @brief Array mapping each token type to the regular expression
	 * matching the entire token.
	 *
	 * An @ref IDENTIFIER is either quoted with double quotes (ISO
	 * style) or quoted with backquotes (default MySQL style) or is a
	 * legal unquoted identifier. Case-insensitive matching is used to
	 * simplify the regexp.
	 */
	private static $tokenRegexps_ = [
		self::SPACE => '/\s+/',
		self::NUMBER => '/[0-9]+(\.[0-9]*)?|\.[0-9]+/',
		self::STRING => "/'[^']*'/",
		self::IDENTIFIER
		=> '/`[^`]*`|"[^"]*"|[A-Z_]+[\.0-9A-Z_]*/i',
		self::MATH => '/[\(\)*+-\/<=>]|<=|>=|<>|!=|\|\|/',
		self::COMMA => '/,/'
	];

	/* == private data members == */

	/**
	 * @brief Flipped @ref $wgDataTable2SqlWhiteList.
	 *
	 * Used for fast test whether an identifier is on the white list.
	 */
	private $whiteList_;

	/* == magic methods == */

	/**
	 * @brief Constructor.
	 *
	 * Initialize data members.
	 */
	public function __construct() {
		global $wgDataTable2SqlWhiteList;

		$this->whiteList_ = array_flip( $wgDataTable2SqlWhiteList );
	}

	/* == operations == */

	/**
	 * @brief Identify the type of the next token by its first character.
	 *
	 * @param string $c Character.
	 *
	 * @return int One of the [class constants](@ref INVALID).
	 */
	public function getType( $c ) {
		foreach ( self::$tokenTypes_ as $type => $charset ) {
			if ( strpos( $charset, $c ) !== false ) {
				return $type;
			}
		}

		/** If no correspondig token type can be found, the type is
		 *	invalid.
		 */
		return self::INVALID;
	}

	/**
	 * @brief Return the next token from the input string.
	 *
	 * @param string $input Input string.
	 *
	 * @param int &$offset Current offset in the input
	 * string. Updated to the position just after the extracted
	 * token. When extraction fails, the position is unchanged.
	 *
	 * @return array Pair consisting of token type and token value.
	 */
	public function getToken( $input, &$offset ) {
		/** Invoke DataTable2SqlTransformer::getType() to obtain the
		 *	type of the next token.
		 */
		$type = $this->getType( $input[$offset] );

		if ( $type == self::INVALID ) {
			/** If the next character is invalid, the token consists
			 *	of the next character.
			 */
			return [ $type, $input[$offset++] ];
		}

		if ( !preg_match( self::$tokenRegexps_[$type], $input, $matches, 0,
				$offset ) ) {
			/** If the regexp cannot be matched, return an invalid
			 *	token without content.
			 */
			return [ self::INVALID, null ];
		}

		$offset += strlen( $matches[0] );
		return [ $type, $matches[0] ];
	}

	/**
	 * @brief Transform an SQL expression from logical to database
	 * representation.
	 *
	 * Applicable to WHERE clauses as well as to ORDER BY clauses.
	 *
	 * @param string $sql SQL text to transform.
	 *
	 * @param array $columns Numerically-indexed array of logical
	 * column names.
	 *
	 * @return string Transformed SQL.
	 *
	 * @throws DataTable2Exception if one of the following is found:
	 * - an unterminated SQL comment;
	 * - an invalid token;
	 * - an identifier that is neither a column name nor on the whitelist
	 *   configured with @ref $wgDataTable2SqlWhiteList.
	 */
	public function transform( $sql, $columns ) {
		/** Get mapping of logical colum names to database colum
		 *	names.
		 */
		$columnMap = array_combine( $columns,
			DataTable2Database::dataCols( count( $columns ) ) );

		$result = '';

		/** Loop through $sql doing the following steps. */
		for ( $i = 0; $i < strlen( $sql ); ) {
			/** Skip C-style comments. */
			if ( substr( $sql, $i, 2 ) == '/*' ) {
				$endPos = strpos( $sql, '*/', $i );

				if ( $endPos === false ) {
					throw new DataTable2Exception(
						'datatable2-error-sql-unterminated-comment',
						htmlspecialchars( substr( $sql, $i ) ) );
				}

				$i = $endPos + 2;
				continue;
			} elseif ( substr( $sql, $i, 2 ) == '--' ) {
				/** Skip anything after an SQL-style comment. */
				break;
			}

			/** Get the next token. */
			$token = $this->getToken( $sql, $i );

			if ( $token[0] == self::INVALID ) {
				throw new DataTable2Exception(
					'datatable2-error-sql-token',
					htmlspecialchars( substr( $sql, $i ) ) );
			}

			/** If the token is an identifier:
			 * - replace it by the database column name
			 *	if it indicates a logical column name
			 * - otherwise verify if it is legal.
			 * This is the main purpose of the entire class.
			 */
			if ( $token[0] == self::IDENTIFIER ) {
				$identifier = $token[1];

				// remove quotes, if any
				$quoted = $identifier[0] == '"'
					|| $identifier[0] == '`';

				if ( $quoted ) {
					$identifier = substr( $identifier, 1,
						strlen( $identifier - 2 ) );
				}

				if ( isset( $columnMap[$identifier] ) ) {
					$token[1] = $columnMap[$identifier];
				} else {
					if ( !$quoted ) {
						/** Unquoted identifiers <i>which are not
						 *	column names</i> are
						 *	case-insensitive.
						 */
						$identifier = strtoupper( $identifier );
					}

					if ( !isset( $this->whiteList_[$identifier] ) ) {
						throw new DataTable2Exception(
							'datatable2-error-sql-identifier',
							htmlspecialchars( $identifier ) );
					}
				}
			}

			/** Append the token content to the result. */
			$result .= $token[1];
		}

		return $result;
	}
}
