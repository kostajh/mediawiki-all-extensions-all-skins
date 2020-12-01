<?php
namespace PhpTagsObjects;

use PhpTags\PhpTagsException;
use PhpTags\Runtime;

/**
 *
 *
 * @file PhpTagsFunc.php
 * @ingroup PhpTagsFunctions
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 */
class PhpTagsFunc extends \PhpTags\GenericObject {

	private static $bannedFunctions = [
		'compact' => true,
		'extract' => true,
		'array_diff_uassoc' => true, // @todo callback
		'array_diff_ukey' => true, // @todo callback
//		'array_filter' => true, // @todo callback
		'array_intersect_uassoc' => true, // @todo callback
		'array_intersect_ukey' => true, // @todo callback
		'array_map' => true, // @todo callback
		'array_reduce' => true, // @todo callback
		'array_udiff_assoc' => true, // @todo callback
		'array_udiff_uassoc' => true, // @todo callback
		'array_udiff' => true, // @todo callback
		'array_uintersect_assoc' => true, // @todo callback
		'array_uintersect_uassoc' => true, // @todo callback
		'array_uintersect' => true, // @todo callback
		'array_walk_recursive' => true, // @todo callback
		'array_walk' => true, // @todo callback
		'uasort' => true, // @todo callback
		'uksort' => true, // @todo callback
		'usort' => true, // @todo callback
		'preg_replace_callback' => true, // @todo callback
		'is_callable' => true, // @todo callback
		'setlocale' => true, // @todo use virtual locale???
		'import_request_variables' => true,
		'unserialize' => true,
		'fprintf' => true,
		'eval' => true, // @todo
		'parse_str' => true,
	];

	public static function __callStatic( $name, $arguments ) {
		list( $callType, $subname ) = explode( '_', $name, 2 );

		if ( $callType === 'f' ) {
			if ( isset( self::$bannedFunctions[$subname] ) ) {
				throw new PhpTagsException( PhpTagsException::FATAL_CALLFUNCTION_INVALID_HOOK, get_called_class() );
			}
			if ( $subname === 'array_multisort' ) {
				return self::array_multisort( $arguments );
			}
			return call_user_func_array( $subname, $arguments );
		}
		return parent::__callStatic( $name, $arguments );
	}

	public static function array_multisort( $arguments ) {
		$n = 0;

		foreach ( $arguments as $k => $v ) {
			if ( is_array( $v ) ) {
				$n = 0;
			} elseif ( is_int( $v ) ) {
				$n++;
				if ( $n > 2 ) {
					return self::pushExceptionExpectsParameter( $k + 1, 'array', $v );
				}
			} else {
				return self::pushExceptionExpectsParameter( $k + 1, 'array or int', $v );
			}
		}

		return call_user_func_array( 'array_multisort', $arguments );
	}

	public static function f_hexdec( $value ) {
		if ( $value && $value !== true && !ctype_xdigit( $value ) ) {
			Runtime::pushException( new PhpTagsException( PhpTagsException::DEPRECATED_INVALID_CHARACTERS ) );
			$value = preg_replace( '/[^0-9a-fA-F]/', '', $value );
		}
		return hexdec( $value );
	}

	public static function f_each( &$var ) {
		// The each() function is deprecated.
		$key = key( $var );
		if ( $key === null ) {
			// the latest element
			return false;
		}
		$value = current( $var );
		next( $var );

		return [
			0 => $key,
			'key' => $key,
			1 => $value,
			'value' => $value,
		];
	}

	public static function f_boolval( $var ) {
		return (bool)$var;
	}

	public static function f_get_defined_vars() {
		$variables = \PhpTags\Runtime::getVariables();
		return array_combine( array_keys( $variables ), array_map( "reset", array_chunk( $variables, 1 ) ) );
	}

	public static function f_get_defined_functions() {
		return \PhpTags\Hooks::getDefinedFunctions();
	}

	public static function f_function_exists( $function_name ) {
		$functions = \PhpTags\Hooks::getDefinedFunctions();
		return isset( $functions[$function_name] );
	}

	public static function f_printf() {
		$args = func_get_args();
		$v = [];
		foreach ( $args as $key => $value ) {
			$v[$key] = self::getValidDumpValue( $value );
		}
		$ret = call_user_func_array( 'sprintf', $v );
		return new \PhpTags\outPrint( strlen( $ret ), $ret, false, false );
	}

	public static function f_vprintf() {
		$args = func_get_args();
		$v = [];
		foreach ( $args as $key => $value ) {
			$v[$key] = self::getValidDumpValue( $value );
		}
		$ret = call_user_func_array( 'vsprintf', $v );
		return new \PhpTags\outPrint( strlen( $ret ), $ret, false, false );
	}

	public static function f_var_export( $expression, $return = false ) {
		$v = self::getValidDumpValue( $expression );
		$ret = var_export( $v, true );
		return $return ? $ret : new \PhpTags\outPrint( null, $ret );
	}

	public static function f_var_dump() {
		$args = func_get_args();
		$v = [];
		foreach ( $args as $key => $value ) {
			$v[$key] = self::getValidDumpValue( $value );
		}
		ob_start();
		call_user_func_array( 'var_dump', $v );
		return new \PhpTags\outPrint( null, ob_get_clean() );
	}

	public static function f_print_r( $expression, $return = false ) {
		$v = self::getValidDumpValue( $expression );
		$ret = print_r( $v, true );
		return $return ? $ret : new \PhpTags\outPrint( true, $ret );
	}

	private static function getValidDumpValue( $expression, $arrayDepth = 0 ) {
		global $wgPhpTagsFunctionDumpDepth, $wgPhpTagsFunctionDumpAmount;

		if ( is_object( $expression ) ) {
			if ( $expression instanceof \PhpTags\GenericObject ) {
				return (array)$expression->getDumpValue();
			} else {
				throw new PhpTagsException( PhpTagsException::FATAL_INTERNAL_ERROR );
			}
		} elseif ( is_array( $expression ) ) {
			if ( $arrayDepth >= $wgPhpTagsFunctionDumpDepth ) {
				return '... Depth limit reached ...';
			}
			$return = [];
			$n = 0;
			foreach ( $expression as $key => $e ) {
				if ( $n >= $wgPhpTagsFunctionDumpAmount ) {
					$return[ $key ] = '... Amount limit reached ...';
					break;
				}
				$n++;
				$return[ $key ] = self::getValidDumpValue( $e, $arrayDepth + 1 );
			}
			return $return;
		}
		return $expression;
	}

	/**
	 * @todo remove it for PHP >= 5.4.0
	 */
	public static function f_preg_match_all( $pattern, $subject, &$matches = null, $flags = PREG_PATTERN_ORDER, $offset = 0 ) {
		// 1) PhpTags\PhpTagsFunctions_PCRE_Test::testRun_preg_match_all_1
		// Parameter 3 to preg_match_all() expected to be a reference, value given
		// 2) PhpTags\PhpTagsFunctions_PCRE_Test::testRun_preg_match_all_2
		// Parameter 3 to preg_match_all() expected to be a reference, value given
		//
		// // $trace = debug_backtrace();
		// // $args = $trace[1]['args'];
		// $args = func_get_args();
		// if ( ! array_key_exists( 2, $args ) ) {
		// $args[2] = null;
		// }
		// return call_user_func_array( 'preg_match_all', $args );

		return preg_match_all( $pattern, $subject, $matches, $flags, $offset );
	}

	protected static function f_preg_replace() {
		$args = func_get_args();
		try {
			if ( is_array( $args[0] ) ) {
				$tmp = [];
				foreach ( $args[0] as $key => $value ) {
					$tmp[$key] = self::getValidPattern( $value );
				}
				$args[0] = $tmp;
			} else {
				$args[0] = self::getValidPattern( $args[0] );
			}
		} catch ( \Exception $exc ) {
			throw new \PhpTags\HookException( $exc->getMessage() );
		}

		return call_user_func_array( 'preg_replace', $args );
	}

	private static function getValidPattern( $pattern_value ) {
		$pattern = str_replace( chr( 0 ), '', $pattern_value );
		// Set basic statics
		$regexStarts = '`~!@#$%^&*-_+=.,?"\':;|/<([{';
		$regexEnds   = '`~!@#$%^&*-_+=.,?"\':;|/>)]}';
		$regexModifiers = 'imsxADU';

		$delimPos = strpos( $regexStarts, $pattern[0] );
		if ( $delimPos === false ) {
			throw new \PhpTags\HookException( wfMessage( 'phptagsfunctions-preg-bad-delimiter' )->text() );
		}

		$end = $regexEnds[$delimPos];
		$pos = 1;
		$endPos = null;
		while ( !isset( $endPos ) ) {
			$pos = strpos( $pattern, $end, $pos );
			if ( $pos === false ) {
				throw new \PhpTags\HookException( wfMessage( 'phptagsfunctions-preg-no-ending-delimiter', $end )->text() );
			}
			$backslashes = 0;
			for ( $l = $pos - 1; $l >= 0; $l-- ) {
				if ( $pattern[$l] == '\\' ) {
					$backslashes++;
				} else {
					break;
				}
			}
			if ( $backslashes % 2 == 0 ) {
				$endPos = $pos;
			}
			$pos++;
		}
		$startRegex = (string)substr( $pattern, 0, $endPos ) . $end;
		$endRegex = (string)substr( $pattern, $endPos + 1 );
		$len = strlen( $endRegex );
		for ( $c = 0; $c < $len; $c++ ) {
			if ( strpos( $regexModifiers, $endRegex[$c] ) === false ) {
				throw new \PhpTags\HookException( wfMessage( 'phptagsfunctions-preg-unknown-modifier', $endRegex[$c] )->text() );
			}
		}
		return $startRegex . $endRegex;
	}

	public static function f_settype( &$var, $type ) {
		if ( false === in_array( $type, [ 'boolean', 'bool', 'integer', 'int', 'float', 'double', 'string', 'array', 'object', 'null' ] ) ) {
			throw new \PhpTags\HookException( 'Invalid type' );
		}
		if ( $var instanceof \PhpTags\GenericObject ) {
			if ( $type === 'object' ) {
				return true;
			}
			throw new \PhpTags\HookException( "object  {$var->getName()} could not be converted to $type", \PhpTags\HookException::EXCEPTION_NOTICE );
		}
		return settype( $var, $type );
	}

	public static function f_max() {
		$values = func_get_args();
		if ( func_num_args() === 1 && false === is_array( $values[0] ) ) {
			return self::pushExceptionExpectsParameter( 1, 'array', $values[0] );
		}
		return call_user_func_array( 'max', $values );
	}

	public static function f_min() {
		$values = func_get_args();
		if ( func_num_args() === 1 && false === is_array( $values[0] ) ) {
			return self::pushExceptionExpectsParameter( 1, 'array', $values[0] );
		}
		return call_user_func_array( 'min', $values );
	}

	public static function f_implode() {
		$values = func_get_args();

		if ( func_num_args() === 1 ) {
			if ( false === is_array( $values[0] ) ) {
				return self::pushExceptionExpectsParameter( 1, 'array', $values[0] );
			}
		} elseif ( false === is_string( $values[0] ) ) {
			return self::pushExceptionExpectsParameter( 1, 'string', $values[0] );
		}

		return call_user_func_array( 'implode', $values );
	}

	public static function f_mt_rand() {
		switch ( func_num_args() ) {
			case 1:
				return mt_rand( func_get_arg( 0 ), mt_getrandmax() );
			case 2:
				return mt_rand( func_get_arg( 0 ), func_get_arg( 1 ) );
		}
		return mt_rand();
	}

	public static function f_rand() {
		switch ( func_num_args() ) {
			case 1:
				return rand( func_get_arg( 0 ), getrandmax() );
			case 2:
				return rand( func_get_arg( 0 ), func_get_arg( 1 ) );
		}
		return rand();
	}

	/**
	 * Make a string's first character lowercase
	 * @global \Language $wgContLang
	 * @param string $str
	 * @return string
	 */
	public static function f_lcfirst( $str ) {
		global $wgContLang;
		return $wgContLang->lcfirst( $str );
	}

	/**
	 * Make a string's first character uppercase
	 * @global \Language $wgContLang
	 * @param string $str
	 * @return string
	 */
	public static function f_ucfirst( $str ) {
		global $wgContLang;
		return $wgContLang->ucfirst( $str );
	}

	/**
	 * Uppercase the first character of each word in a string
	 * @global \Language $wgContLang
	 * @param string $str
	 * @return string
	 */
	public static function f_ucwords( $str ) {
		global $wgContLang;
		return $wgContLang->ucwords( $str );
	}

	/**
	 * Make a string lowercase
	 * @global \Language $wgContLang
	 * @param string $str
	 * @return string
	 */
	public static function f_strtolower( $str ) {
		global $wgContLang;
		return $wgContLang->lc( $str );
	}

	/**
	 * Make a string uppercase
	 * @global \Language $wgContLang
	 * @param string $str
	 * @return string
	 */
	public static function f_strtoupper( $str ) {
		global $wgContLang;
		return $wgContLang->uc( $str );
	}

	public static function f_levenshtein() {
		$argCount = func_num_args();

		switch ( $argCount ) {
			case 2:
			case 5:
				return call_user_func_array( 'levenshtein', func_get_args() );
		}
		\PhpTags\Runtime::pushException( new PhpTagsException( PhpTagsException::WARNING_EXPECTS_EXACTLY_PARAMETER, [ '2 or 5', $argCount ] ) );
		return \PhpTags\Hooks::getCallInfo( \PhpTags\Hooks::INFO_RETURNS_ON_FAILURE );
	}

	public static function f_strtr( $str, $from, $to = null ) {
		switch ( func_num_args() ) {
			case 2:
				if ( is_array( $from ) ) {
					return strtr( $str, $from );
				}
				return self::pushExceptionExpectsParameter( 2, 'array', $from );
			case 3:
				if ( false === is_array( $from ) ) {
					return strtr( $str, (string)$from, $to );
				}
				return self::pushExceptionExpectsParameter( 2, 'string', $from );
		}
	}

	public static function f_array_chunk( $array, $size, $preserve_keys = false ) {
		if ( $size < 1 ) {
			throw new \PhpTags\HookException( 'Size parameter expected to be greater than 0' );
		}
		return array_chunk( $array, $size, $preserve_keys );
	}

	public static function f_array_combine( $keys, $values ) {
		$k = count( $keys );
		if ( $k !== count( $values ) ) {
			throw new \PhpTags\HookException( 'Both parameters should have an equal number of elements' );
		}
		if ( $k === 0 ) { // @todo this is only for compatibility with PHP < 5.4.0
			return [];
		}
		return array_combine( self::mapArrayKeys( $keys ), $values );
	}

	public static function f_array_fill_keys( $keys, $values ) {
		return array_fill_keys( self::mapArrayKeys( $keys ), $values );
	}

	private static function mapArrayKeys( array $array ) {
		return array_map(
			function ( $key ) {
				if ( is_array( $key ) ) {
					\PhpTags\Runtime::pushException( new PhpTagsException( PhpTagsException::NOTICE_ARRAY_TO_STRING ) );
					return 'Array';
				} elseif ( $key instanceof \PhpTags\GenericObject ) {
					throw new PhpTagsException( PhpTagsException::FATAL_OBJECT_COULD_NOT_BE_CONVERTED, [ $key->getName(), 'string' ] );
				}
				return $key;
			},
			$array
		);
	}

	public static function f_array_count_values( $array ) {
		return array_count_values( self::filterArrayKeys( $array ) );
	}

	public static function f_array_flip( $array ) {
		return array_flip( self::filterArrayKeys( $array ) );
	}

	private static function filterArrayKeys( array $array ) {
		return array_filter(
			$array,
			function ( $value ) {
				return is_string( $value ) || is_int( $value ) || \PhpTags\Runtime::pushException( new \PhpTags\HookException( 'Can only count STRING and INTEGER values!' ) );
			}
		);
	}

	public static function f_array_fill( $start_index, $num, $value ) {
		if ( $num < 1 ) {
			throw new \PhpTags\HookException( 'Number of elements must be positive' );
		}
		return array_fill( $start_index, $num, $value );
	}

	/**
	 * @todo callback
	 * @param array $array
	 * @return array
	 */
	public static function f_array_filter( array $array ) {
		return array_filter( $array );
	}

	public static function f_array_rand( $array, $num = 1 ) {
		if ( $num < 1 || $num > count( $array ) ) {
			throw new \PhpTags\HookException( 'Second argument has to be between 1 and the number of elements in the array' );
		}
		return array_rand( $array, $num );
	}

	public static function f_range( $start, $end, $step = 1 ) {
		if ( is_numeric( $start ) && is_numeric( $end ) && abs( $step ) > abs( $start - $end ) ) {
			throw new \PhpTags\HookException( 'step exceeds the specified range' );
		}
		return range( $start, $end, $step );
	}

}
