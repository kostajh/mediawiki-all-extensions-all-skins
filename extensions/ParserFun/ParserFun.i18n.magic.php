<?php
#coding: utf-8

/**
 * Internationalization file for magic words of the 'Parser Fun' extension.
 *
 * @since 0.1
 *
 * @file ParserFun.i18n.magic.php
 * @ingroup ParserFun
 * @author Daniel Werner < danweetz@web.de >
 */

$magicWords = array();

/** English
 * @author Daniel Werner
 */
$magicWords['en'] = array(
	'parse' => array( 0, 'parse' ),
	'this' => array( 1, 'THIS' ),
	'caller' => array( 1, 'CALLER' ),
);

/** Message documentation (Message documentation)
 * @author Daniel Werner
 */
$magicWords['qqq'] = array(
	'this' => array( 1, 'Keyword to put in front of a variable like "{{THIS:PAGENAME}}". This will output the pagename of the page where it is defined on instead of the page actually being parsed. "THIS" refers to that page.' ),
	'caller' =>  array( 1, 'Variable/Parser function returning an templates direct initiator or with options even all or just specific initiators.' ),
);

/** German (Deutsch)
 * @author Daniel Werner
 */
$magicWords['de'] = array(
	'this' => array( 1, 'DIESER', 'DIESE', 'DIESES' ),
);
