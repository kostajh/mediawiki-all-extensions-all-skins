<?php
/**
 * This file is part of Extension:WikiLovesMonuments
 * Copyright (C) 2012, Platonides
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
class WikiLovesMonumentsHooks {
	/**
	* @param $parser Parser
	* @return bool
	*/
	public static function registerParserFunctions( $parser ) {
		if ( class_exists( 'CountryNames' ) ) {// Provided by cldr extension
			$parser->setFunctionHook( 'wlm-countries', 'WikiLovesMonuments::countriesParserFunction' );
		}
		$parser->setFunctionHook( 'wlm-country-count', 'WikiLovesMonuments::countryCount' );
		$parser->setFunctionHook( 'wlm-country-website', 'WikiLovesMonuments::countryWebsite' );
		$parser->setFunctionHook( 'wlm-is-running', 'WikiLovesMonuments::wlmIsRunning', Parser::SFH_OBJECT_ARGS );

		return true;
	}

	public static function init() {
		global $wgHooks, $wgWikiLovesMonumentsCountryPortlet, $wgPiwikCustomJS;
		global $wgOut, $wgWikiLovesMonumentsCentralisedStatsId;
		if ( $wgWikiLovesMonumentsCountryPortlet ) {
			$wgHooks['SkinBuildSidebar'][] = 'WikiLovesMonuments::onSkinBuildSidebar';
		}
		if ( $wgWikiLovesMonumentsCentralisedStatsId !== false ) {
			$piwikServer = '//stats.wikilovesmonuments.org';
			$wgOut->addModules( 'ext.WikiLovesMonuments.piwik' );
			$wgOut->addScript( Html::inlineScript( <<<JS
	if(window.mw){ mw.loader.using( 'ext.WikiLovesMonuments.piwik', function() {
		try {
			var piwikTracker = window.Piwik.getTracker('$piwikServer/piwik.php', $wgWikiLovesMonumentsCentralisedStatsId);
			$wgPiwikCustomJS
			piwikTracker.trackPageView();
			piwikTracker.enableLinkTracking();
		} catch( err ) {}
	} ); }
JS
			) . "<noscript>" .
					"<p><img src=\"$piwikServer/piwik.php?idsite=$wgWikiLovesMonumentsCentralisedStatsId\" style=\"border: 0\" alt=\"\" /></p>" .
				"</noscript>"
			);
		}
	}
}
