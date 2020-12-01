<?php
# OpenStreetMap SlippyMap - MediaWiki extension
#
# This defines what happens when <slippymap> tag is placed in the wikitext
#
# We show a map based on the lat/lon/zoom data passed in. This extension brings in
# the OpenLayers javascript, to show a slippy map.
#
# Usage example:
# <slippymap lat=51.485 lon=-0.15 z=11 w=300 h=200 layer=osmarender></slippymap>
#
# Tile images are not cached local to the wiki.
# To achieve this (remove the OSM dependency) you might set up a squid proxy,
# and modify the requests URLs here accordingly.
#
# This file should be placed in the mediawiki 'extensions' directory
# ...and then it needs to be 'included' within LocalSettings.php
#
##################################################################################
#
# Copyright 2008 Harry Wood, Jens Frank, Grant Slater, Raymond Spekking and others
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# @addtogroup Extensions
#

if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

$wgExtensionCredits['parserhook'][] = array(
	'name'           => 'OpenStreetMap Slippy Map',
	'author'         => '[http://harrywood.co.uk Harry Wood], Jens Frank',
	'svn-date'       => '$LastChangedDate: 2008-07-23 22:20:05 +0100 (Wed, 23 Jul 2008) $',
	'svn-revision'   => '$LastChangedRevision: 37977 $',
	'url'            => 'http://wiki.openstreetmap.org/index.php/Slippy_Map_MediaWiki_Extension',
	'descriptionmsg' => 'slippymap_desc',
);

$wgMessagesDirs['SlippyMap'] = __DIR__ . '/i18n';
$wgAutoloadClasses['SlippyMap'] = dirname( __FILE__ ) . '/SlippyMap.class.php';

# Bump this when updating OpenStreetMap.js to help update caches
$wgSlippyMapVersion = 1;

$wgMapOfServiceUrl = "http://osm-tah-cache.firefishy.com/~ojw/MapOf/?";

$wgHooks['ParserFirstCallInit'][] = function( Parser $parser ) {
	global $wgMapOfServiceUrl;
	# register the extension with the WikiText parser
	# the first parameter is the name of the new tag.
	# In this case it defines the tag <slippymap> ... </slippymap>
	# the second parameter is the callback function for
	# processing the text between the tags
	$parser->setHook( 'slippymap', array( 'SlippyMap', 'parse' ) );
};
