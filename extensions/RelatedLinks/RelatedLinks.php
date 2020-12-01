<?php

/***********************************************************
 * Name:     RelatedLinks
 * Desc:     Custom Related Link SideBar
 *
 * Version:  1.0.0
 *
 * Author:   sleepinglion
 * Homepage: https://www.mediawiki.org/wiki/Extension:RelatedLinks
 * 			 		 https://github.com/sleepinglion
 *           http://www.sleepinglion.pe.kr
 *
 *
 * License:  MIT
 *
 ***********************************************************
 */

if (function_exists('wfLoadExtension')) {
    wfLoadExtension('RelatedLinks');

    return true;
} else {
    //die( 'This version of the Nuke extension requires MediaWiki 1.25+' );

    $wgExtensionCredits['specialpage'][] = array(
        'path' => __FILE__,
        'name' => 'RelatedLinks',
        'author' => 'sleepinglion',
        'url' => 'https://www.mediawiki.org/wiki/Extension:RelatedLinks',
        'descriptionmsg' => 'Custom Related Link SideBar',
        'version' => '1.0'
    );

    $wgAutoloadClasses['SpecialRelatedLinks'] = __DIR__ . DIRECTORY_SEPARATOR . 'SpecialRelatedLinks.php';
    $wgMessagesDirs['RelatedLinks'] = __DIR__ . DIRECTORY_SEPARATOR . 'i18n';
    $wgExtensionMessagesFiles['RelatedLinksAlias'] = __DIR__ . DIRECTORY_SEPARATOR . 'RelatedLinks.alias.php';
    $wgSpecialPages['RelatedLinks'] = 'SpecialRelatedLinks';
}
