<?php
/**
 * Natural Language List allows for creation of simple lists in
 * natural languages (e.g. 1, 2, 3, ... n-1 and n), and several
 * other sophisticated and useful list related functions.
 *
 *
 * Copyright (C) 2010 'Svip', 'Happy-melon', and others.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version; or the DWTFYWWI License version 1,
 * as detailed below.
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
 * -----------------------------------------------------------------
 *                          DWTFYWWI LICENSE
 *                      Version 1, January 2006
 *
 * Copyright (C) 2006 Ævar Arnfjörð Bjarmason
 *
 *                        DWTFYWWI LICENSE
 *  TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
 * 0. The author grants everyone permission to do whatever the fuck they
 * want with the software, whatever the fuck that may be.
 * -----------------------------------------------------------------
 */

$wgExtensionCredits['parserhook'][] = array(
	'path'        => __FILE__,
	'name'        => 'Natural Language List',
	'author'      => array( 'Svip', 'Happy-melon', 'Conrad Irwin' ),
	'url'         => 'https://www.mediawiki.org/wiki/Extension:NaturalLanguageList',
	'descriptionmsg' => 'nll-desc',
	'version'     => '2.6.0'
);

$dir = dirname(__FILE__);
$wgMessagesDirs['NaturalLanguageList'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['NaturalLanguageListMagic'] = "$dir/NaturalLanguageList.i18n.magic.php";

$wgAutoloadClasses['NaturalLanguageList'] = "$dir/src/NaturalLanguageList.php";
$wgHooks['ParserFirstCallInit'][] = 'NaturalLanguageList::onParserFirstCallInit';

$wgParserTestFiles[] = dirname( __FILE__ ) . "/tests/parser/nllParserTests.txt";

/* global variables */

$wgNllMaxListLength = 1000; # the maximum allowed length of a list.
