<?php
/**
 * Extension:Josa
 * Author: Bae Junehyeon <devunt@gmail.com>
 * Original implementation by Park Shinjo <peremen@gmail.com>
 * License: MIT License
 */

class JosaHooks {
	private static $josaMap = [
		'Eul/Ruel' => [ '을', '를', '을(를)' ], // 곶감을 / 사과를
		'Eun/Neun' => [ '은', '는', '은(는)' ], // 곶감은 / 사과는
		'E/Ga' => [ '이', '가', '이(가)' ], // 곶감이 / 사과가
		'Gwa/Wa' => [ '과', '와', '과(와)' ], // 곶감과 / 사과와
		'A/Ya' => [ '아', '야', '아(야)' ], // 태준아 / 철수야
		'Euro/Ro'  => [ '으로', '로', '(으)로' ], // 집으로 / 학교로
		'E/'  => [ '이', '', '(이)' ], // 태준이가 / 철수가
	];

	// This dictionary matches each numbers and alphabets to the corresponding hangul pronounces
	private static $pronounceMap = [
		'0' => '영', '1' => '일', '2' => '이',
		'3' => '삼', '4' => '사', '5' => '오',
		'6' => '육', '7' => '칠', '8' => '팔',
		'9' => '구',

		'A' => '이', 'B' => '비', 'C' => '씨',
		'D' => '디', 'E' => '이', 'F' => '프',
		'G' => '지', 'H' => '치', 'I' => '이',
		'J' => '이', 'K' => '이', 'L' => '엘',
		'M' => '엠', 'N' => '엔', 'O' => '오',
		'P' => '피', 'Q' => '큐', 'R' => '알',
		'S' => '스', 'T' => '티', 'U' => '유',
		'V' => '이', 'W' => '유', 'X' => '스',
		'Y' => '이', 'Z' => '지',
	];

	/**
	 * @param Parser $parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		foreach ( self::$josaMap as $key => $value ) {
			// @codingStandardsIgnoreLine
			$parser->setFunctionHook( $key, function ( $parser, $str, $param1 = null, $param2 = null ) use ( $key ) {
				$josa = JosaHooks::getJosa( $key, $str );

				foreach ( [ $param1, $param2 ] as $param ) {
					if ( $param === null ) {
						break;
					}

					$override = JosaHooks::checkOverrideParam( $param );
					if ( $override !== false ) {
						$josa = $override;
						continue;
					}

					$josaonly = JosaHooks::checkJosaonlyParam( $param );
					if ( $josaonly === true ) {
						$str = '';
						continue;
					}
				}

				return $str . $josa;
			} );
		}
		return true;
	}

	/**
	 * @param string $param String to check
	 * @return bool|string Value of override param or false if it isn't presented
	 */
	public static function checkOverrideParam( $param ) {
		if ( preg_match( '/^(override|덮어쓰기|오버라이드)=(.*)$/', $param, $matches ) === 0 ) {
			return false;
		}
		return $matches[2];
	}

	/**
	 * @param string $param String to check
	 * @return bool Existence of josaonly param
	 */
	public static function checkJosaonlyParam( $param ) {
		return ( $param === 'josaonly' || $param === '조사만' );
	}

	/**
	 * @param string $type Type of the last letter in the word (see JosaHooks::$josaMap's keys)
	 * @param string $str Word to determine the josa
	 * @return string Josa
	 */
	public static function getJosa( $type, $str ) {
		// @codingStandardsIgnoreLine
		$str = preg_replace( '/[\x{0000}-\x{0027}\x{002A}-\x{002F}\x{003A}-\x{0040}\x{005B}-\x{0060}\x{007B}-\x{00FF}]/u', '', $str );
		$chr = mb_substr( $str, -1, 1, 'utf-8' );
		if ( array_key_exists( $chr, self::$pronounceMap ) ) {
			$chr = self::$pronounceMap[$chr];
		}
		$code = self::convertToJohabCode( $chr );
		if ( !$code ) {
			// Not hangul
			$idx = 2;
		} elseif ( ( $code - 0xAC00 ) % 28 === 0 ) {
			// No trailing consonant
			$idx = 1;
		} elseif ( ( $type === 'Euro/Ro' ) && ( ( $code - 0xAC00 ) % 28 === 8 ) ) {
			// $type is Euro/Ro and trailing consonant is rieul(ㄹ).
			// This is an exception on Korean postposition rules.
			$idx = 1;
		} else {
			// Trailing consonant exists
			$idx = 0;
		}
		return self::$josaMap[$type][$idx];
	}

	/*
	 * @param string $str String to convert
	 * @return int Converted Johab code
	 * @codingStandardsIgnoreStart
	 * see https://ko.wikipedia.org/wiki/%ED%95%9C%EA%B8%80_%EC%83%81%EC%9A%A9_%EC%A1%B0%ED%95%A9%ED%98%95_%EC%9D%B8%EC%BD%94%EB%94%A9
	 * @codingStandardsIgnoreEnd
	 */
	private static function convertToJohabCode( $str ) {
		$values = [];
		$lookingFor = 1;
		for ( $i = 0, $len = strlen( $str ); $i < $len; $i++ ) {
			$thisValue = ord( $str[ $i ] );
			if ( $thisValue < 128 ) {
				return false;
			} else {
				if ( count( $values ) === 0 ) {
					$lookingFor = ( $thisValue < 224 ) ? 2 : 3;
				}
				$values[] = $thisValue;
				if ( count( $values ) === $lookingFor ) {
					$number = ( $lookingFor === 3 ) ?
						( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ) :
						( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
					return $number;
				}
			}
		}
		return false;
	}
}
