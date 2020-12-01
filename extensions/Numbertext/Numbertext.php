<?php

/**
 * Numbertext - An extension for number to text conversion based on http://www.numbertext.org/
 *
 * @link https://www.mediawiki.org/wiki/Extension:Numbertext Documentation
 * @file Numbertext.php
 * @defgroup Numbertext
 * @ingroup Extensions
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license LGPL/BSD dual-license
 */

// Check to see if we are being called as an extension or directly
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is an extension to MediaWiki and thus not a valid entry point.' );
}

// Register this extension on Special:Version
$wgExtensionCredits['parserhook'][] = [
	'path'           => __FILE__,
	'name'           => 'Numbertext',
	'version'        => '0.10.0.0',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:Numbertext',
	'author'         => [ '[[mw:User:Pastakhov|Pavel Astakhov]]', ],
	'descriptionmsg' => 'numbertext-desc'
];

// Tell the whereabouts of files
$dir = __DIR__;

// Allow translations for this extension
$wgMessagesDirs['Numbertext'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['NumbertextMagic'] = $dir . '/Numbertext.i18n.magic.php';

// Preparing classes for autoloading
$wgAutoloadClasses['Numbertext'] = $dir . '/Numbertext.body.php';
$wgAutoloadClasses['Soros'] = $dir . '/Soros.php';

// Specify the function that will initialize the parser function.
$wgHooks['ParserFirstCallInit'][] = function ( Parser &$parser ) {
	$parser->setFunctionHook( 'MAG_NUMBERTEXT', 'Numbertext::numbertext' );
	$parser->setFunctionHook( 'MAG_MONEYTEXT', 'Numbertext::moneytext' );
	return true;
};

$wgNumbertext_defaultLang = $wgLanguageCode;

$wgNumbertextLang = [
	'Hung' => '',
	'Hung_2' => '',
	'Roman' => '',
	'Roman_2' => '',
	'Suzhou' => '',
	'af_ZA' => 'af',
	'ca_ES' => 'ca',
	'cs_CZ' => 'cs',
	'da_DK' => 'da',
	'de_DE' => 'de',
	'el_EL' => 'el',
	'en_IN' => '',
	'en_US' => 'en',
	'en_US_2' => '',
	'eo' => 'eo',
	'es_ES' => 'es',
	'fi_FI' => 'fi',
	'fr_BE' => '',
	'fr_CH' => '',
	'fr_FR' => 'fr',
	'he_IL' => 'he',
	'hu_HU' => 'hu',
	'hu_HU_2' => '',
	'id_ID' => 'id',
	'it_IT' => 'it',
	'ja_JP' => 'ja',
	'ja_JP_2' => '',
	'ko_KP' => 'ko-KP',
	'ko_KR' => 'ko',
	'lb_LU' => 'lb',
	'lt_LT' => 'lt',
	'lv_LV' => 'lv',
	'nl_NL' => 'nl',
	'pl_PL' => 'pl',
	'pt_BR' => 'pt-BR',
	'pt_PT' => 'pt',
	'ro_RO' => 'ro',
	'ru_RU' => 'ru',
	'sh_RS' => 'sh',
	'sl_SI' => 'sl',
	'sr_RS' => 'sr',
	'sv_SE' => 'sv',
	'th_TH' => 'th',
	'tr_TR' => 'tr',
	'vi_VN' => 'vi',
	'zh_ZH' => 'zh',
	'zh_ZH_2' => '',
];
