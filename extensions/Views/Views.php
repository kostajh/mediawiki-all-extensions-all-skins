<?php
/**
 * Initialization file for the Views extension.
 *
 * @file Views.php
 * @ingroup Views
 *
 * @licence GNU GPL v3
 * @author Vedmaka <god.vedmaka@gmail.com>
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( version_compare( $wgVersion, '1.17', '<' ) ) {
	die( '<b>Error:</b> This version of Views requires MediaWiki 1.17 or above.' );
}

global $wgViews;
$wgViewsDir = dirname( __FILE__ );

/* Credits page */
$wgExtensionCredits['specialpage'][] = array(
    'path' => __FILE__,
    'name' => 'Views',
    'version' => '0.1',
    'author' => 'Vedmaka',
    'url' => '',
);

/* Autoload classes */
$wgAutoloadClasses['Views'] = dirname( __FILE__ ) . '/Views.class.php';

