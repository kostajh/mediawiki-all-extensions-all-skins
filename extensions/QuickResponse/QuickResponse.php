<?php
/*
QuickResponse Extension for MediaWiki.

@license MIT License
*/

$wgExtensionCredits[ 'other' ][] = array(
    'path' => __FILE__,
    'name' => 'QuickResponse',
    'version' => '0.3.0',
    'url' => '',
    'author' => 'Konarak Ratnakar',
    'descriptionmsg' => 'quickresponse-desc'
);

$wgMessagesDirs['QuickResponse'] = __DIR__ . '/i18n';

$wgResourceModules[ 'QuickResponse' ] = array(
    'scripts' => array('resources/qrcode.js',
                       'resources/jquery.qrcode.js',
                       'resources/quickresponse.js'
                       ),
    'dependencies' =>  'jquery.ui',
    'messages' => array('quickresponse-desc',
                        'quickresponse-text',
                        'quickresponse-title',
                        'quickresponse-tooltip'
                        ),
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'QuickResponse',
);

$wgHooks[ 'BeforePageDisplay' ][] = function ( $out ) {
    $out->addModules( 'QuickResponse' );
    return true;
};
