<?php
/**
 * DiscussionTools data generators
 *
 * @file
 * @ingroup Extensions
 * @license MIT
 */

namespace MediaWiki\Extension\DiscussionTools;

use Config;
use DateTimeZone;
use ExtensionRegistry;
use ILanguageConverter;
use Language;
use MediaWiki\MediaWikiServices;
use ResourceLoaderContext;
use ResourceLoaderFileModule;
use ResourceLoaderModule;

class Data {
	/**
	 * Part of the 'ext.discussionTools.init' module.
	 *
	 * We need all of this data *in content language*. Some of it is already available in JS, but only
	 * in client language, so it's useless for us (e.g. digit transform table, month name messages).
	 *
	 * @param ResourceLoaderContext|null $context
	 * @param Config $config
	 * @param string|Language|null $lang
	 * @return array
	 */
	public static function getLocalData(
		?ResourceLoaderContext $context, Config $config, $lang = null
	) : array {
		if ( !$lang ) {
			$lang = MediaWikiServices::getInstance()->getContentLanguage();
		} elseif ( !( $lang instanceof Language ) ) {
			$lang = MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( $lang );
		}

		$langConv = $lang->getConverter();

		$data = [];

		$data['dateFormat'] = [];
		$dateFormat = $lang->getDateFormatString( 'both', $lang->dateFormat( false ) );
		foreach ( $lang->getVariants() as $variant ) {
			$convDateFormat = self::convertDateFormat( $dateFormat, $langConv, $variant );
			$data['dateFormat'][$variant] = $convDateFormat;
		}

		$data['digits'] = [];
		foreach ( $lang->getVariants() as $variant ) {
			$data['digits'][$variant] = [];
			foreach ( str_split( '0123456789' ) as $digit ) {
				if ( $config->get( 'TranslateNumerals' ) ) {
					$localDigit = $lang->formatNumNoSeparators( $digit );
				} else {
					$localDigit = $digit;
				}
				$convLocalDigit = $langConv->translate( $localDigit, $variant );
				$data['digits'][$variant][] = $convLocalDigit;
			}
		}

		// ApiQuerySiteinfo
		$data['localTimezone'] = $config->get( 'Localtimezone' );

		$data['specialContributionsName'] = MediaWikiServices::getInstance()
			->getSpecialPageFactory()->getLocalNameFor( 'Contributions' );

		$localTimezone = $config->get( 'Localtimezone' );
		// Return all timezone abbreviations for the local timezone (there will often be two, for
		// non-DST and DST timestamps, and sometimes more due to historical data, but that's okay).
		// Avoid DateTimeZone::listAbbreviations(), it returns some half-baked list that is different
		// from the timezone data used by everything else in PHP.
		$timezoneAbbrs = array_values( array_unique(
			array_map( function ( $transition ) {
				return $transition['abbr'];
			}, ( new DateTimeZone( $localTimezone ) )->getTransitions() )
		) );

		$data['timezones'] = [];
		foreach ( $lang->getVariants() as $variant ) {
			$data['timezones'][$variant] = array_combine(
				array_map( function ( string $tzMsg ) use ( $lang, $langConv, $variant ) {
					// MWTimestamp::getTimezoneMessage()
					// Parser::pstPass2()
					// Messages used here: 'timezone-utc' and so on
					$key = 'timezone-' . strtolower( trim( $tzMsg ) );
					$msg = wfMessage( $key )->inLanguage( $lang );
					// TODO: This probably causes a similar issue to https://phabricator.wikimedia.org/T221294,
					// but we *must* check the message existence in the database, because the messages are not
					// actually defined by MediaWiki core for any timezone other than UTC...
					if ( $msg->exists() ) {
						$text = $msg->text();
					} else {
						$text = strtoupper( $tzMsg );
					}
					$convText = $langConv->translate( $text, $variant );
					return $convText;
				}, $timezoneAbbrs ),
				array_map( 'strtoupper', $timezoneAbbrs )
			);
		}

		// Messages in content language
		$messagesKeys = array_merge(
			Language::WEEKDAY_MESSAGES,
			Language::WEEKDAY_ABBREVIATED_MESSAGES,
			Language::MONTH_MESSAGES,
			Language::MONTH_GENITIVE_MESSAGES,
			Language::MONTH_ABBREVIATED_MESSAGES
		);
		$data['contLangMessages'] = [];
		foreach ( $lang->getVariants() as $variant ) {
			$data['contLangMessages'][$variant] = array_combine(
				$messagesKeys,
				array_map( function ( $key ) use ( $lang, $langConv, $variant ) {
					$text = wfMessage( $key )->inLanguage( $lang )->text();
					return $langConv->translate( $text, $variant );
				}, $messagesKeys )
			);
		}

		// How far backwards we look for a signature associated with a timestamp before giving up.
		// Note that this is not a hard limit on the length of signatures we detect.
		$data['signatureScanLimit'] = 100;

		return $data;
	}

	/**
	 * Convert a date format string to a different language variant, leaving all special characters
	 * unchanged and applying language conversion to the plain text fragments.
	 *
	 * @param string $format
	 * @param ILanguageConverter $langConv
	 * @param string $variant
	 * @return string
	 */
	private static function convertDateFormat(
		string $format,
		ILanguageConverter $langConv,
		string $variant
	) : string {
		$formatLength = strlen( $format );
		$s = '';
		// The supported codes must match CommentParser::getTimestampRegexp()
		for ( $p = 0; $p < $formatLength; $p++ ) {
			$num = false;
			$code = $format[ $p ];
			if ( $code === 'x' && $p < $formatLength - 1 ) {
				$code .= $format[++$p];
			}
			if ( $code === 'xk' && $p < $formatLength - 1 ) {
				$code .= $format[++$p];
			}

			// LAZY SHORTCUTS that might cause bugs:
			// * We assume that result of $langConv->translate() doesn't produce any special codes/characters
			// * We assume that calling $langConv->translate() separately for each character is correct
			switch ( $code ) {
				case 'xx':
				case 'xg':
				case 'd':
				case 'D':
				case 'j':
				case 'l':
				case 'F':
				case 'M':
				case 'n':
				case 'Y':
				case 'xkY':
				case 'G':
				case 'H':
				case 'i':
					// Special code - pass through unchanged
					$s .= $code;
					break;
				case '\\':
					// Plain text (backslash escaping) - convert to language variant
					if ( $p < $formatLength - 1 ) {
						$s .= '\\' . $langConv->translate( $format[++$p], $variant );
					} else {
						$s .= $code;
					}
					break;
				case '"':
					// Plain text (quoted literal) - convert to language variant
					if ( $p < $formatLength - 1 ) {
						$endQuote = strpos( $format, '"', $p + 1 );
						if ( $endQuote === false ) {
							// No terminating quote, assume literal "
							$s .= $code;
						} else {
							$s .= '"' .
								$langConv->translate( substr( $format, $p + 1, $endQuote - $p - 1 ), $variant ) .
								'"';
							$p = $endQuote;
						}
					} else {
						// Quote at end of string, assume literal "
						$s .= $code;
					}
					break;
				default:
					// Plain text - convert to language variant
					$s .= $langConv->translate( $format[$p], $variant );
			}
		}

		return $s;
	}

	/**
	 * Return messages in content language, for use in a ResourceLoader module.
	 *
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @param array $messagesKeys
	 * @return array
	 */
	public static function getContentLanguageMessages(
		ResourceLoaderContext $context, Config $config, array $messagesKeys = []
	) : array {
		return array_combine(
			$messagesKeys,
			array_map( function ( $key ) {
				return wfMessage( $key )->inContentLanguage()->text();
			}, $messagesKeys )
		);
	}

	/**
	 * Add optional dependencies to a ResourceLoader module definition depending on loaded extensions.
	 *
	 * @param array $info
	 * @return ResourceLoaderModule
	 */
	public static function addOptionalDependencies( array $info ) : ResourceLoaderModule {
		$extensionRegistry = ExtensionRegistry::getInstance();

		foreach ( $info['optionalDependencies'] as $ext => $deps ) {
			if ( $extensionRegistry->isLoaded( $ext ) ) {
				$info['dependencies'] = array_merge( $info['dependencies'], (array)$deps );
			}
		}

		$class = $info['class'] ?? ResourceLoaderFileModule::class;
		return new $class( $info );
	}
}
