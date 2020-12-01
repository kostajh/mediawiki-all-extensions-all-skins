<?php
/**
 * This file is part of Extension:WikiLovesMonuments
 * Copyright (C) 2012,  Platonides
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
class WikiLovesMonuments {
	/* Active WLM edition, either the last/running edition or in preparation */
	const activeEdition = 2013;

	/* const */ static $countries = array(
		2010 => array( 'nl'	),

		2011 => array( // https://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2011
				'ad', // Andorra
				'at', // Austria
				'be', // Belgium
				'dk', // Denmark
				'ee', // Estonia
				'fr', // France
				'de', // Germany
				'hu', // Hungary (21 September - 21 October)
				'lu', // Luxembourg
				'nl', // Netherlands
				'no', // Norway
				'pl', // Poland
				'pt', // Portugal
				'ro', // Romania
				'ru', // Russia (15 September - 30 September)
				'es', // Spain
				'se', // Sweden
				'ch', // Switzerland (1 July - 30 September)
			),

		2012 => array( // https://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2012/Participating_countries
				'ad', // Andorra
				'ar', // Argentina
				'at', // Austria
				'by', // Belarus
				'be', // Belgium
				'ca', // Canada
				'cl', // Chile
				'co', // Colombia
				'cz', // Czech Republic
				'dk', // Denmark
				'ee', // Estonia
				'fr', // France
				'de', // Germany
				'gh', // Ghana
				'in', // India
				'il', // Israel
				'it', // Italy
				'ke', // Kenya
				'lu', // Luxembourg
				'mx', // Mexico
				'nl', // Netherlands
				'no', // Norway
				'pa', // Panama
				'ph', // Philippines
				'pl', // Poland
				'ro', // Romania
				'ru', // Russia
				'rs', // Serbia
				'sk', // Slovakia
				'za', // South Africa
				'es', // Spain
				'se', // Sweden
				'ch', // Switzerland
				'ua', // Ukraine
				'us', // United States
			),

		2013 => array(
				'dz', // Algeria
				'ad', // Andorra
				'aq', // Antarctica
				'ar', // Argentina
				'am', // Armenia
				'aw', // Aruba
				'at', // Austria
				'az', // Azerbaijan
				'be', // Belgium
				'bo', // Bolivia
				'by', // Belarus
				'cm', // Cameroon
				'ca', // Canada
				'cl', // Chile
				'cn', // China
				'co', // Colombia
				'cz', // Czech Republic
				'eg', // Egypt
				'sv', // El Salvador
				'ee', // Estonia
				'fr', // France
				'de', // Germany
				'hk', // Hong Kong
				'hu', // Hungary
				'in', // India
				'il', // Israel
				'it', // Italy
				'jo', // Jordan
				'lu', // Luxembourg
				'mx', // Mexico
				'nl', // Netherlands
				'no', // Norway
				'np', // Nepal
				'pa', // Panama
				'ph', // Philippines
				'pl', // Poland
				'ro', // Romania
				'ru', // Russia
				'rs', // Serbia
				'sk', // Slovakia
				'za', // South Africa
				'es', // Spain
				'se', // Sweden
				'ch', // Switzerland
				'sy', // Syria
				'tw', // Taiwan
				'th', // Thailand
				'tn', // Tunisia
				'ua', // Ukraine
				'gb', // United Kingdom
				'us', // United States
				'uy', // Uruguay
				've', // Venezuela
			),
	);

	/**
	 * Web sites for the local competition of each country
	 */
	/*const*/ static $websites = array(
		'int' => 'http://www.wikilovesmonuments.org/',
		'eu' => 'http://www.wikilovesmonuments.eu/',

		'ad' => 'http://www.wikilovesmonuments.cat/', // Andorra and Catalan areas WP
		'am' => 'http://www.wikilovesmonuments.am/', // Armenia WP
		'aq' => 'http://www.wikilovesmonuments.aq/', // Antarctica WP
		'ar' => 'http://wikilovesmonuments.com.ar', // Argentina WP
		'at' => 'http://wikilovesmonuments.at/', // Austria WP
		'aw' => 'http://www.wlmaruba.com/', // Aruba WP
		'az' => 'http://az.wikipedia.org/wiki/Vikipediya:Viki_Abid%C9%99l%C9%99ri_Sevir_2013', // Azerbaijan
		'be' => 'http://www.wikilovesmonuments.be/', // Belgium MW
		'bo' => 'http://wlm.wikimedia.org.bo/', // Bolivia WP
		'by' => 'http://wikilovesmonuments.by/', // Belarus WP
		'ca' => 'http://wikimedia.ca/wlmca/', // Canada MW
		'cl' => 'http://www.wikilovesmonuments.cl/', // Chile WP
		'cm' => 'http://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2013_in_Cameroon', // Cameroon
		'cn' => 'http://aiguji.org/', // China WP
		'co' => 'http://www.wikilovesmonuments.co/', // Colombia WP
		'cz' => 'http://www.wikilovesmonuments.cz/', // Czech Republic WP
		'de' => 'http://www.wikilovesmonuments.de/de/', // Germany (redirected from http://wikilovesmonuments.de/)
		'dk' => 'http://da.wikipedia.org/wiki/Wikipedia:Wiki_Loves_Monuments_2012', // Denmark (redirected from http://www.wikilovesmonuments.dk/)
		'dz' => 'http://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2013_in_Algeria',
		'ee' => 'http://wikilovesmonuments.ee/', // Estonia
		'eg' => 'http://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2013_in_Egypt', // Egypt
		'fr' => 'http://wikilovesmonuments.fr/', // France WP
		'gb' => 'http://www.wikilovesmonuments.org.uk/', /// United Kingdom WP
		'gh' => false, // Ghana
		'hk' => 'http://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2013_in_Hong_Kong', // Hong Kong
		'hu' => 'http://www.wikilovesmonuments.hu/', // Hungary custom
		'in' => 'http://www.wikilovesmonuments.in/', // India WP
		'il' => 'http://www.wlm.org.il/', // Israel WP
		'it' => 'http://www.wikilovesmonuments.it/', // Italy WP
		'jo' => 'http://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2013_in_Jordan', // Jordan
		'ke' => 'http://en.wikipedia.org/wiki/Wikipedia:Wiki_Loves_Monuments_Kenya', // Kenya
		'li' => false, // Liechtenstein
		'lu' => 'http://www.wikilovesmonuments.be/', // Luxembourg MW
		'mx' => 'http://wikilovesmonuments.mx/', // Mexico WP
		'nl' => 'http://www.wikilovesmonuments.nl/', // Netherlands WP
		'no' => 'http://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2013_in_Norway', // Norway, http://wikilovesmonuments.no/ is a WP Hello World
		'np' => 'https://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2013_in_Nepal', // Nepal
		'pa' => 'http://www.wlmpanama.org.pa/', // Panama WP
		'ph' => 'http://www.wikilovesmonuments.ph/', // Philippines WP
		'pl' => 'http://wikizabytki.pl/', // Poland WP
		'pt' => 'http://www.wikilovesmonuments.org.pt/', // Portugal custom+MW
		'ro' => 'http://wikilovesmonuments.ro/', // Romania WP
		'ru' => 'http://wikilovesmonuments.ru/', // Russia WP
		'rs' => 'http://wlm.rs/', // Serbia custom
		'sk' => 'http://wikilovesmonuments.sk/', // Slovakia
		'sv' => 'http://wikilovesmonumentselsalvador.org/', // El Salvador
		'sy' => 'http://wikilovesmonuments-sy.org/', // Syria WP
		'th' => 'https://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2013_in_Thailand/th', // Thailand - Redirect from http://th.wikilovesmonuments.org/
		'tn' => 'http://www.wikilovesmonuments.tn/', // Tunisia WP
		'tw' => 'http://www.wikilovesmonuments.tw/', // Taiwan custom
		'es' => 'http://www.wikilm.es/', // Spain MW
		'se' => 'http://wikilovesmonuments.se/', // Sweden WP
		'ch' => 'http://wikilovesmonuments.ch/', // Switzerland WP
		'ua' => 'http://wlm.org.ua/', // Ukraine WP
		'us' => 'http://wikilovesmonuments.us/', // United States WP
		'uy' => 'http://www.wikilovesmonuments.uy/', // Uruguay Drupal
		've' => 'http://wlm.wikimedia.org.ve/', // Venezuela WP
		'za' => 'http://wikilovesmonuments.co.za/', // South Africa MW
	);

	/**
	 * Dates of beginning and end of the competitions
	 */
	/*const*/ static private $dates = array(
		2010 => array( 'default' => array( 'from' => '20100831220000', 'to' => '20100930215959' ) ),
		2011 => array( 'default' => array( 'from' => '20110831220000', 'to' => '20110930215959' ) ),
		2012 => array(
			'default' => array( 'from' => '20120831220000', 'to' => '20120930215959' ),
			'ph' => array( 'from' => '20120831160000', 'to' => '20120930155959' ),
			'in' => array( 'from' => '20120831183000', 'to' => '20120930185959' ),
			'ru' => array( 'from' => '20120831200000', 'to' => '20120930195959' ),
			'by' => array( 'from' => '20120831210000', 'to' => '20120930205959' ),
			'ke' => array( 'from' => '20120831210000', 'to' => '20120930205959' ),
			'ua' => array( 'from' => '20120831210000', 'to' => '20120930205959' ),
			'es' => array( 'to' => '20120930225959' ),
			'gh' => array( 'from' => '20120901000000', 'to' => '20120930235959' ),
			'ca' => array( 'from' => '20120901023000', 'to' => '20121001065959' ),
			'ar' => array( 'from' => '20120901030000', 'to' => '20121001025959' ),
			'cl' => array( 'from' => '20120901040000', 'to' => '20121001025959' ),
			'co' => array( 'from' => '20120901050000', 'to' => '20121001045959' ),
			'mx' => array( 'from' => '20120901050000', 'to' => '20121001065959' ),
			'pa' => array( 'from' => '20120901050000', 'to' => '20121001045959' ),
			'us' => array( 'from' => '20120901100100', 'to' => '20121001100059' ),
			'il' => array( 'from' => '20120913210000', 'to' => '20121014205959' ),
		),
		2013 => array(
			'default' => array( 'from' => '20130831220000', 'to' => '20130930215959' ),
			'dz' => array( 'from' => '20130831230000', 'to' => '20130930225959' ),
			'aq' => array( 'from' => '20130901000000', 'to' => '20130930235959' ),
			'ar' => array( 'from' => '20130901030000', 'to' => '20131001025959' ),
			'am' => array( 'from' => '20130831200000', 'to' => '20130930195959' ),
			'aw' => array( 'from' => '20130901040000', 'to' => '20131001035959' ),
			'az' => array( 'from' => '20130831190000', 'to' => '20130930185959' ),
			'bo' => array( 'from' => '20130901040000', 'to' => '20131001035959' ),
			'by' => array( 'from' => '20130831210000', 'to' => '20130930205959' ),
			'ca' => array( 'from' => '20130901023000', 'to' => '20131001065959' ),
			'cl' => array( 'from' => '20130901040000', 'to' => '20131001055959' ),
			'cn' => array( 'from' => '20130901040000', 'to' => '20130930155959' ),
			'co' => array( 'from' => '20130901050000', 'to' => '20131001045959' ),
			'sv' => array( 'from' => '20130901060000', 'to' => '20131001055959' ),
			'ee' => array( 'from' => '20130831210000', 'to' => '20130930205959' ),
			'hk' => array( 'from' => '20130831210000', 'to' => '20130930155959' ),
			'in' => array( 'from' => '20130831183000', 'to' => '20130930182959' ),
			'il' => array( 'from' => '20130914210000', 'to' => '20131015205959' ),
			'mx' => array( 'from' => '20130901050000', 'to' => '20131001065959' ),
			'np' => array( 'from' => '20130831181500', 'to' => '20130930181459' ),
			'pa' => array( 'from' => '20130901050000', 'to' => '20131001045959' ),
			'ph' => array( 'from' => '20130831160000', 'to' => '20130930155959' ),
			'ro' => array( 'from' => '20130831210000', 'to' => '20130930205959' ),
			'ru' => array( 'from' => '20130831120000', 'to' => '20130930205959' ),
			'es' => array( 'to' => '20130930225959' ),
			'sy' => array( 'from' => '20130831210000', 'to' => '20130930205959' ),
			'tw' => array( 'from' => '20130831160000', 'to' => '20130930155959' ),
			'th' => array( 'from' => '20130831170000', 'to' => '20130930165959' ),
			'tn' => array( 'from' => '20130831230000', 'to' => '20130930225959' ),
			'ua' => array( 'from' => '20130831210000', 'to' => '20130930205959' ),
			'gb' => array( 'from' => '20130831230000', 'to' => '20130930225959' ),
			'us' => array( 'from' => '20130901040100', 'to' => '20131001095959' ),
			'uy' => array( 'from' => '20130901030000', 'to' => '20131001025959' ),
			've' => array( 'from' => '20130901043000', 'to' => '20131001042959' ),

		),
	);

	/**
	 * @param $parser Parser
	 * @param $year
	 * @return string
	 */
	public static function countriesParserFunction( $parser, $year ) {
		$countries = self::countries( (int)$year, $parser->getFunctionLang()->getCode(), 'numeric' );

		if ( $countries === false ) {
			return '<strong class="error">' .
					wfMessage( 'wlm-no-contest-year', $year )->inLanguage( $parser->getFunctionLang() )->plain() .
					'</strong>';
		}

		return $parser->getFunctionLang()->listToText( $countries );
	}

	/**
	 * @param $year
	 * @param $langCode bool|string
	 * @param $keys string
	 * @return array|bool
	 */
	public static function countries( $year, $langCode = false, $keys = 'codes' ) {
		if ( !isset( self::$countries[$year] ) ) {
			return false;
		}

		if ( $langCode === false ) {
			return self::$countries[$year];
		}

		$countries = CountryNames::getNames( $langCode );

		$wlmCountries = array();
		foreach ( self::$countries[$year] as $wlmCountryCode ) {
			if ( $keys == 'codes' )
				$wlmCountries[$wlmCountryCode] = $countries[ strtoupper( $wlmCountryCode ) ];
			else
				$wlmCountries[] = $countries[ strtoupper( $wlmCountryCode ) ];
		}

		return $wlmCountries;
	}

	/**
	 * @param $parser Parser
	 * @param $year
	 * @return string
	 */
	public static function countryCount( $parser, $year ) {
		if ( !isset( self::$countries[(int)$year] ) ) {
			return '<strong class="error">' .
				wfMessage( 'wlm-no-contest-year', $year )->inLanguage( $parser->getFunctionLang() )->plain() .
			 	'</strong>';
		}

		return $parser->getFunctionLang()->formatNum( count( self::$countries[(int)$year] ) );
	}

	/**
	 * @param $countryCode string
	 * @return bool|string mixed the url to the WLM website for that country or false if it isn't a country code
	 */
	public static function getCountryWebsite( $countryCode ) {
		if ( isset( self::$websites[$countryCode] ) && self::$websites[$countryCode] )
			return self::$websites[$countryCode];

		if ( class_exists( 'CountryNames' ) ) {
			$countries = CountryNames::getNames( 'en' );

			// Fallback to the page in Wikimedia Commons
			if ( isset( $countries[ strtoupper( $countryCode ) ] ) )
				return 'https://commons.wikimedia.org/wiki/Commons:Wiki_Loves_Monuments_2012_in_' . $countries[ strtoupper( $countryCode ) ];
		}

		return false;
	}

	/**
	 * @param $skin Skin
	 * @param $bar array
	 * @return bool
	 */
	public static function onSkinBuildSidebar( Skin $skin, &$bar ) {
		global $wgLang, $wgWikiLovesMonumentsCountryPortlet;

		if ( !$wgWikiLovesMonumentsCountryPortlet || !class_exists( 'CountryNames' ) ) {
			return true;
		}

		$skipCountries = array();
		if ( $wgWikiLovesMonumentsCountryPortlet !== true ) {
			$skipCountries = (array)$wgWikiLovesMonumentsCountryPortlet;
		}

		$countries = self::countries( WikiLovesMonuments::activeEdition, $wgLang->getCode() );
		asort( $countries ); // Sort by local name
		$countries = array( 'int' => $skin->msg( 'wlm-international-contest' )->text(),  ) + $countries;

		$wlmSidebar = "			<ul>";
		foreach ( $countries as $countryCode => $name ) {
			if ( in_array( $countryCode, $skipCountries ) ) {
				continue;
			}

			$url = self::getCountryWebsite( $countryCode );

			if ( $url && $name ) {
				$wlmSidebar .= "\n\t\t\t\t" .
					Html::rawElement( 'li',
						array( 'id' => "wlm-country-$countryCode" ),
						Html::element( 'a' , array( 'href' => $url ),
							$name
						)
					);
			}

		}
		$wlmSidebar .= "\n			</ul>\n";

		$bar['wlm-sidebar-portlet'] = $wlmSidebar;
		return true;
	}

	/**
	 * @param $parser Parser
	 * @param $countryCode string
	 * @return string
	 */
	public static function countryWebsite( $parser, $countryCode ) {
		$url = self::getCountryWebsite( strtolower( $countryCode ) );

		if ( $url === false ) {
			return '<strong class="error">' .
				wfMessage( 'wlm-no-url-bad-country', $countryCode )->inLanguage( $parser->getFunctionLang() )->plain() .
				'</strong>';
		}

		return $url;
	}

	/**
	 * Provide the category for the images of this year.
	 * Precondition: $countryCode is the country code of a participating country,
	 * or the special value '*' for all the images from that year.
	 */
	public static function getCountryCategory( $year, $countryCode = '*' ) {
		if ( !class_exists( 'CountryNames' ) ) {
			throw new MWException( 'CLDR extension not loaded' );
		}

		if ( $countryCode == '*' )
			return "Images_from_Wiki_Loves_Monuments_$year";

		$countries = CountryNames::getNames( 'en' );

		if ( !isset( $countries[ strtoupper( $countryCode ) ] ) )
			return false;

		switch ( $countryCode ) {
			case 'gb':
				$country = 'United Kingdom';
				break;
			case 'us':
				$country = 'United States';
				break;
			default:
				$country = $countries[ strtoupper( $countryCode ) ];
		}

		// There's an article in front of the country name:
		static $theCountries = array( 'cz', 'gb', 'nl', 'ph', 'us' );

		if ( in_array( $countryCode, $theCountries ) )
			$country = "the $country";

		$country = strtr( $country, ' ', '_' );

		return "Images_from_Wiki_Loves_Monuments_{$year}_in_$country";
	}

	/**
	 * Provide the dates when the competition begins and/or ends for each country.
	 *
	 * @param $year int           WLM edition
	 * @param $countryCode mixed  country code for that country, true for an array
	 * of all countries or * for the union of all countries dates.
	 * @param $dates string 'begin' or 'end'
	 * @return mixed   MediaWiki timestamp, array of timestamps or array of arrays of timestamps
	 */
	public static function getCountryDate( $year, $countryCode, $dates = 'both' ) {
		if ( $countryCode === true ) {
			$countryDates = array();

			foreach ( self::countries( $year ) as $c ) {
				$countryDates[$c] = self::getCountryDate( $year, $c, $dates );
			}

			return $countryDates;
		}

		if ( $dates == 'both' ) {
			return array( self::getCountryDate( $year, $countryCode, 'begin' ), self::getCountryDate( $year, $countryCode, 'end' ) );
		}

		if ( $countryCode === '*' ) {
			static $begin, $end = null;
			if  ( is_null( $end ) ) {
				$begin = self::$dates[$year]['default']['from'];
				$end = self::$dates[$year]['default']['to'];
				foreach ( self::$dates[$year] as $entry ) {
					if ( isset( $entry['from'] ) )
						$begin = min( $begin, $entry['from'] );
					if ( isset( $entry['to'] ) )
						$end = max( $end, $entry['to'] );
				}
			}
			if ( $dates == 'begin' )
				return $begin;
			if ( $dates == 'end' )
				return $end;
			return array( $begin, $end );
		}

		switch ( $dates ) {
			case 'begin':
				return isset( self::$dates[$year][$countryCode]['from'] ) ? self::$dates[$year][$countryCode]['from'] : self::$dates[$year]['default']['from'];

			case 'end':
				return isset( self::$dates[$year][$countryCode]['to'] ) ? self::$dates[$year][$countryCode]['to'] : self::$dates[$year]['default']['to'];

			default:
				throw new MWException( 'Bad $dates passed to WikiLovesMonuments::getCountryDate()' );
		}
	}

	/**
	 * Provides parserfunction {{#wlm-is-running: year|countrycode|show if yes|before=show if before|after=show if ended|test=fakedate}}
	 * Only the first two parameters are mandatory.
	 */
	static function wlmIsRunning($parser, $frame, $args) {
		// As it's pre-expanded, we can't pass the year to newChild()
		$year = trim( $frame->expand( array_shift( $args ) ) );

		if ( !isset( self::$countries[(int)$year] ) ) {
			return '<strong class="error">' .
				wfMessage( 'wlm-no-contest-year', $year )->inLanguage( $parser->getFunctionLang() )->plain() .
				'</strong>';
		}

		$year = (int)$year;
		$frame = $frame->newChild( $args );

		$countryCode = $frame->getArgument( 1 );

		if ( $countryCode == false ) {
			return '<strong class="error">' .
				wfMessage( 'wlm-country-not-given' )->inLanguage( $parser->getFunctionLang() )->plain() .
				'</strong>';
		}

		$countryCode = trim( $countryCode );
		if ( ! in_array( $countryCode, self::$countries[$year] ) ) {
			return '<strong class="error">' .
				wfMessage( 'wlm-country-not-participating-year', self::countryName( $countryCode, $parser->getFunctionLang()->getCode(), $countryCode), $year )->inLanguage( $parser->getFunctionLang() )->plain() .
				'</strong>';
		}

		list( $begin, $end ) = self::getCountryDate( $year, $countryCode );

		$testDate = wfTimestamp( TS_MW, $frame->getArgument( 'test' ) );

		if ( $testDate < $begin )
			return $frame->getArgument( 'before' );

		if ( $testDate > $end )
			return $frame->getArgument( 'after' );

		return $frame->getArgument( 2 );
	}

	/**
	 * Converts a country code to the country name
	 * @param $countryCode string Country code
	 * @param $langCode string Target language to convert to
	 * @param $default mixed Value to return if the country code is not found (ie. it wasn't a country code)
	 */
	static function countryName( $countryCode, $langCode, $default = false ) {
		if ( !class_exists( 'CountryNames' ) ) {
			throw new MWException( 'CLDR extension not loaded' );
		}

		$countryCode = strtoupper( $countryCode );
		$countries = CountryNames::getNames( $langCode );

		if ( isset( $countries[ $countryCode ] ) )
			return $countries[ $countryCode ];

		return $default;
	}
}
