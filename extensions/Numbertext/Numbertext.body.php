<?php
/**
 * Main classes used by the Numbertext extension.
 * Is based on the source code from http://numbertext.org/
 *
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license LGPL/BSD dual-license
 */
class Numbertext {
	private static function load( $lang ) {
		$url = __DIR__ . "/data/" . $lang . ".sor";
		$st = file_get_contents( $url );
		if ( $st === false ) {
			return null;
		}
		$s = new Soros( $st );
		if ( $lang != null ) {
			self::addModule( [ $lang, $s ] );
		}
		return $s;
	}

	private static function getModules( $m = null ) {
		static $modules = [];
		if ( is_array( $m ) ) {
			$modules[] = $m;
		}
		return $modules;
	}

	private static function getLangModule( $lang ) {
		$modules = self::getModules();
		if ( isset( $modules[$lang] ) ) {
			return $modules[$lang];
		}
		return null;
	}

	private static function addModule( $m ) {
		self::getModules( $m );
	}

	public function __construct() {
	}

	/**
	 * Number to text conversion
	 *
	 * @param Parser &$parser
	 * @param string $input
	 * @param string $lang default 'en_US'
	 * @return string
	 */
	public static function numbertext( &$parser, $input = '', $lang = '' ) {
		$fileLang = self::getLangFileName( $lang );

		$s = self::getLangModule( $fileLang );
		if ( $s === null ) {
			$s = self::load( $fileLang );
		}
		if ( $s === null ) {
			return null;
		}
		return $s->run( $input );
	}

	/**
	 * Money to text conversion
	 *
	 * @param Parser &$parser
	 * @param string $input
	 * @param string $money
	 * @param string $lang default 'en_US'
	 * @return string
	 */
	public static function moneytext( &$parser, $input = '', $money = '', $lang = '' ) {
		return self::numbertext( $parser, $money . " " . $input, $lang );
	}

	private static function getLangFileName( $lang, $except = '' ) {
		global $wgNumbertext_defaultLang, $wgNumbertextLang;

		if ( $lang == '' ) {
			if ( ( $wgNumbertext_defaultLang == '' || $wgNumbertext_defaultLang === null )
				&& $except == ''
			) {
				$lang = $GLOBALS['wgUser']->getOption( 'language' );
				$except = 'user';
			} elseif ( $except != 'content' ) {
				$lang = $wgNumbertext_defaultLang;
				$except = 'content';
			} else {
				return 'en_US';
			}
		}

		if ( array_key_exists( $lang, $wgNumbertextLang ) ) {
			return $lang;
		}

		$ret = self::recursive_array_search( strtolower( $lang ), $wgNumbertextLang );
		if ( $ret === false ) {
			return self::getLangFileName( '', $except );
		}
		return $ret;
	}

	private static function recursive_array_search( $needle, $haystack ) {
		foreach ( $haystack as $key => $value ) {
			$current_key = $key;
			if (
				$needle === $value ||
				( is_array( $value ) && self::recursive_array_search( $needle, $value ) !== false )
			) {
				return $current_key;
			}
		}
		return false;
	}
}
