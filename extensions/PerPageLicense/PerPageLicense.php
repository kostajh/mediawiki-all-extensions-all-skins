<?php
/**
 * PerPageLicense MediaWiki extension.
 *
 * This extension enables licenses to be set on a per-namespace or per-page basis.
 *
 * Written by Leucosticte
 * https://www.mediawiki.org/wiki/User:Leucosticte
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 */

if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This file is an extension to the MediaWiki software and cannot be used "
                . "standalone.\n" );
        die( 1 );
}

$wgExtensionCredits['other'][] = array(
        'path' => __FILE__,
        'name' => 'PerPageLicense',
        'author' => '[https://mediawiki.org/User:Leucosticte Leucosticte]',
        'url' => 'https://mediawiki.org/wiki/Extension:PerPageLicense',
        'descriptionmsg' => 'perpagelicense-desc',
        'version' => '1.1.0'
);

$wgMessagesDirs['PerPageLicense'] = __DIR__ . '/i18n';
$wgHooks['ParserBeforePreprocess'][] = 'PerPageLicense::getLicense';
$wgHooks['ParserBeforeInternalParse'][] = 'PerPageLicense::getLicense';

// Page from which to obtain templates and associated licenses.
$wgPerPageLicenseTemplatePage = 'MediaWiki:License-templates';

// Path to license files.
// Needs to be 1.24c because version_compare() works in confusing ways
if ( version_compare( $wgVersion, '1.24c', '>=' ) ) {
        if ( $wgResourceBasePath !== null ) {
                $wgPerPageLicensePath = "{$wgResourceBasePath}/resources/assets/licenses";
        } else {
                $wgPerPageLicensePath = "{$wgScriptPath}/resources/assets/licenses";
        }
} else {
        $wgPerPageLicensePath = "{$wgStylePath}/common/images";
}

// Array of licenses.
$wgPerPageLicenseLicenses = array (
        'cc-0' => array(
                'url' => 'http://creativecommons.org/publicdomain/zero/1.0/',
                'src' => "{$wgPerPageLicensePath}/cc-by-sa.png",
                'alt' => 'Creative Commons 0',
        ),
        'cc-by-nc-sa' => array(
                'url' => 'http://creativecommons.org/licenses/by-nc/3.0/',
                'src' => "{$wgPerPageLicensePath}/cc-by-nc-sa.png",
                'alt' => 'Creative Commons Attribution-NonCommercial 3.0 Unported',
        ),
        'cc-by' => array(
                'url' => 'http://creativecommons.org/licenses/by/3.0/',
                'src' => "{$wgPerPageLicensePath}/cc-by.png",
                'alt' => 'Creative Commons Attribution 3.0 Unported',
        ),
        'cc-by-sa' => array(
                'url' => 'http://creativecommons.org/licenses/by-sa/3.0/',
                'src' => "{$wgPerPageLicensePath}/cc-by-sa.png",
                'alt' => 'Creative Commons Attribution Share-Alike 3.0 Unported',
        ),
        'gnu-fdl' => array(
                'url' => 'http://www.gnu.org/copyleft/fdl.html',
                'src' => "{$wgPerPageLicensePath}/gnu-fdl.png",
                'alt' => 'GNU Free Documentation License',
        ),
        'public-domain' => array(
                'url' => 'http://creativecommons.org/licenses/publicdomain/',
                'src' => "{$wgPerPageLicensePath}/public-domain.png",
                'alt' => 'public domain'
        ),
);

// Array of namespaces and their licenses.
$wgPerPageLicenseNamespaces = array();

class PerPageLicense {
        public static function getLicense( $parser, &$text, $strip_state ) {
                global $wgRightsIcon, $wgRightsUrl, $wgRightsText, $wgFooterIcons,
                        $wgPerPageLicenseLicenses, $wgPerPageLicenseTemplatePage,
                        $wgPerPageLicenseNamespaces, $wgVersion;
                // Sometimes this hook runs twice
                static $hasRun = false;
                if ( $hasRun ) {
                        return true;
                } else {
                        $hasRun = true;
                }
                $namespace = $parser->getTitle()->getNamespace();
                if ( isset ( $wgPerPageLicenseNamespaces[$namespace] ) ) {
                        $wgRightsText = $wgPerPageLicenseLicenses
                                [$wgPerPageLicenseNamespaces[$namespace]]['alt'];
                        $wgRightsIcon = $wgPerPageLicenseLicenses
                                [$wgPerPageLicenseNamespaces[$namespace]]['src'];
                        $wgRightsUrl = $wgPerPageLicenseLicenses
                                [$wgPerPageLicenseNamespaces[$namespace]]['url'];
                        $wgFooterIcons['copyright']['copyright'] =
                                $wgPerPageLicenseLicenses[$wgPerPageLicenseNamespaces[$namespace]];
                }
                $licenseTitle = Title::newFromText( $wgPerPageLicenseTemplatePage );
                if ( !$licenseTitle->exists() ) {
                        return true;
                }
                $licenseWikiPage = WikiPage::factory ( $licenseTitle );
                $contents = ContentHandler::getContentText( $licenseWikiPage->getContent( Revision::RAW ) );
                if ( !$contents ) {
                        return true;
                }
                $lines = explode ( "\n", $contents );
                $templates = array();
                foreach ( $lines as $line ) {
                        if ( substr ( $line, 0, 1 ) == '|' && trim ( $line ) != '|'
                                && trim ( $line ) != '|-' && trim ( $line ) != '|}' ) {
                                // Get rid of that |
                                $line = substr ( $line, 1, strlen( $line ) - 1);
                                $lineArr = explode ( '||', $line );
                                if ( isset ( $lineArr[1] ) && trim ( $lineArr[0] ) ) {
                                        // Is it one of the acceptable licenses?
                                        if ( isset ( $wgPerPageLicenseLicenses[trim (
                                                $lineArr[1] )] ) ) {
                                                $templates[ucfirst ( str_replace
                                                        ( ' ', '_', trim ( $lineArr[0] ) ) ) ] =
                                                        trim ( $lineArr[1] );
                                        }
                                }
                        }
                }
                if ( !$templates ) {
                        return true;
                }
                $id = $parser->getTitle()->getArticleID();
                $sql = "tl_from=$id AND (";
                $first = true;
                foreach ( $templates as $key => $template ) {
                        if ( !$first ) {
                                $sql .= ' OR ';
                        }
                        $first = false;
                        $sql .= "(tl_namespace=10 AND tl_title='$key')";
                }
                $sql .= ')';
                $dbr = wfGetDB( DB_REPLICA );
                $res = $dbr->selectRow( 'templatelinks', 'tl_title', array( $sql ) );
                if ( $res ) {
                        $license = $templates[$res->tl_title];
                        $wgRightsText = $wgPerPageLicenseLicenses[$license]['alt'];
                        $wgRightsIcon = $wgPerPageLicenseLicenses[$license]['src'];
                        $wgRightsUrl = $wgPerPageLicenseLicenses[$license]['url'];
                        $wgFooterIcons['copyright']['copyright'] =
                                $wgPerPageLicenseLicenses[$license];
                }
                return true;
        }
}
