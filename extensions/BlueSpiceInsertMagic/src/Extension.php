<?php
/**
 * InsertMagic for BlueSpice
 *
 * Provides a dialog box to add magicwords and tags to an articles content in edit mode
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpiceInsertMagic
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\InsertMagic;

/**
 * Base class for InsertMagic extension
 * @package BlueSpiceInsertMagic
 */
class Extension extends \BlueSpice\Extension {

	/**
	 * HINT: https://www.mediawiki.org/wiki/Help:Magic_words
	 * HINT: http://de.wikipedia.org/wiki/Wikipedia:MagicWord
	 * TODO: Migrate to configs and use Data\Store
	 * @var array
	 */
	public static $aMagicWords = [
		'variables' => [
			[ 'bs-insertmagic-currentyear' => '{{CURRENTYEAR}}' ],
			[ 'bs-insertmagic-currentmonth' => '{{CURRENTMONTH}}' ],
			[ 'bs-insertmagic-currentmonthname' => '{{CURRENTMONTHNAME}}' ],
			[ 'bs-insertmagic-currentmonthnamegen' => '{{CURRENTMONTHNAMEGEN}}' ],
			[ 'bs-insertmagic-currentmonthabbrev' => '{{CURRENTMONTHABBREV}}' ],
			[ 'bs-insertmagic-currentday' => '{{CURRENTDAY}}' ],
			[ 'bs-insertmagic-currentday2' => '{{CURRENTDAY2}}' ],
			[ 'bs-insertmagic-currentdow' => '{{CURRENTDOW}}' ],
			[ 'bs-insertmagic-currentdayname' => '{{CURRENTDAYNAME}}' ],
			[ 'bs-insertmagic-currenttime' => '{{CURRENTTIME}}' ],
			[ 'bs-insertmagic-currenthour' => '{{CURRENTHOUR}}' ],
			[ 'bs-insertmagic-currentweek' => '{{CURRENTWEEK}}' ],
			[ 'bs-insertmagic-currenttimestamp' => '{{CURRENTTIMESTAMP}}' ],
			[ 'bs-insertmagic-sitename' => '{{SITENAME}}' ],
			[ 'bs-insertmagic-server' => '{{SERVER}}' ],
			[ 'bs-insertmagic-servername' => '{{SERVERNAME}}' ],
			[ 'bs-insertmagic-scriptpath' => '{{SCRIPTPATH}}' ],
			[ 'bs-insertmagic-stylepath' => '{{STYLEPATH}}' ],
			[ 'bs-insertmagic-currentversion' => '{{CURRENTVERSION}}' ],
			// '{{CONTENTLANG}}',
			[ 'bs-insertmagic-currentlanguage' => '{{CONTENTLANGUAGE}}' ],
			[ 'bs-insertmagic-pageid' => '{{PAGEID}}' ],
			// '{{PAGESIZE:<page name>|R}}',
			[ 'bs-insertmagic-pagesize' => '{{PAGESIZE:pagename}}' ],
			[ 'bs-insertmagic-protectionlevel' => '{{PROTECTIONLEVEL:action}}' ],
			[ 'bs-insertmagic-revisionid' => '{{REVISIONID}}' ],
			[ 'bs-insertmagic-revisionday' => '{{REVISIONDAY}}' ],
			[ 'bs-insertmagic-revisionday2' => '{{REVISIONDAY2}}' ],
			[ 'bs-insertmagic-revisionmonth' => '{{REVISIONMONTH}}' ],
			[ 'bs-insertmagic-revisionmonth1' => '{{REVISIONMONTH1}}' ],
			[ 'bs-insertmagic-revisionyear' => '{{REVISIONYEAR}}' ],
			[ 'bs-insertmagic-revisiontimestamp' => '{{REVISIONTIMESTAMP}}' ],
			[ 'bs-insertmagic-revisionuser' => '{{REVISIONUSER}}' ],
			[ 'bs-insertmagic-displaytitle' => '{{DISPLAYTITLE:title}}' ],
			// '{{DEFAULTSORTKEY:<sortkey>}}',
			// '{{DEFAULTCATEGORYSORT:<sortkey>}}',
			// '{{DEFAULTSORT:<sortkey>|noerror}}',
			// '{{DEFAULTSORT:<sortkey>|noreplace}}',
			[ 'bs-insertmagic-defaultsort' => '{{DEFAULTSORT:sortkey}}' ],
		],
		'behavior-switches' => [
			[ 'bs-insertmagic-notoc' => '__NOTOC__' ],
			[ 'bs-insertmagic-forcetoc' => '__FORCETOC__' ],
			[ 'bs-insertmagic-toc' => '__TOC__' ],
			[ 'bs-insertmagic-noeditsection' => '__NOEDITSECTION__' ],
			[ 'bs-insertmagic-newsectionlink' => '__NEWSECTIONLINK__' ],
			[ 'bs-insertmagic-nonewsectionlink' => '__NONEWSECTIONLINK__' ],
			[ 'bs-insertmagic-nogallery' => '__NOGALLERY__' ],
			[ 'bs-insertmagic-hiddencat' => '__HIDDENCAT__' ],
			// '__NOCC__',
			[ 'bs-insertmagic-nocontentconvert' => '__NOCONTENTCONVERT__' ],
			// '__NOTC__',
			[ 'bs-insertmagic-notitleconvert' => '__NOTITLECONVERT__' ],
			[ 'bs-insertmagic-end' => '__END__' ],
			[ 'bs-insertmagic-index' => '__INDEX__' ],
			[ 'bs-insertmagic-noindex' => '__NOINDEX__' ],
			[ 'bs-insertmagic-staticredirect' => '__STATICREDIRECT__' ]
		],
	];

	/**
	 * TODO: Migrate to configs and use Data\Store
	 * @var array
	 */
	public static $aTags = [
		'gallery' => [ 'bs-insertmagic-gallery' => '<gallery></gallery>' ],
		'nowiki' => [ 'bs-insertmagic-nowiki' => '<nowiki></nowiki>' ],
		'noinclude' => [ 'bs-insertmagic-noinclude' => '<noinclude></noinclude>' ],
		'includeonly' => [ 'bs-insertmagic-includeonly' => '<includeonly></includeonly>' ],
	];

	/**
	 * TODO: Migrate to configs and use Data\Store
	 * @var array
	 */
	public static $aQuickAccess = [];

	/**
	 * TODO: Migrate to configs and use Data\Store
	 * @return array
	 */
	public static function getMagicWords() {
		return self::$aMagicWords;
	}

	/**
	 * TODO: Migrate to configs and use Data\Store
	 * @return array
	 */
	public static function getTags() {
		return self::$aTags;
	}

	/**
	 * TODO: Migrate to configs and use Data\Store
	 * @return array
	 */
	public static function getQuickAccess() {
		return self::$aQuickAccess;
	}

}
