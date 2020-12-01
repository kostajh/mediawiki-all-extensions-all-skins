<?php
/**
 * Fixed Table Header extension for MediaWiki 1.17+
 * Copyright (C) 2014 Ishmal Lewis
 * https://www.mediawiki.org/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup Extensions
 * @author Ishmal Lewis
 *
 * This extension wraps applies a fixedheadertable css class to the table
 * enclosed in the <fixedheadertable> tag.  It also adds a "fixedcolumn" class
 * to the specified header rows.  The 'jquery.fixedheadertable.js' widget does the rest.
 *
 * Options:
 * style - The style tag is respected; use it adjust height, width, etc...
 * fixedcolumns - This is the amount of columns (from the left) to fix.
 *
 * Notes:
 * This extension assumes the table markup is formatted line-by-line as shown in the example below.
 * Also, the header markup "!" must be used for header rows.
 * Example:
 * <fixedheadertable fixedcolumns="1">
 * {| class="wikitable"
 * !|table
 * !|wikitext 
 * |- 
 * ||markup 
 * ||goes 
 * |- 
 * ||right 
 * ||here
 * |}
 * </fixedheadertable>
 */

use MediaWiki\MediaWikiServices; 

$wgExtensionFunctions[] = 'wfFixedHeaderTable';
$wgExtensionCredits['parserhook'][] = array(
		'name' => 'FixedHeaderTable',
		'descriptionmsg' => 'fixedheadertable-desc',
		'author' => array( 'Ishmal Lewis', 'Mark Malek for JQuery FixedHeaderTable.js widget' ),
		'version' => '1.0.1',
		'url' => 'https://www.mediawiki.org/wiki/Extension:FixedHeaderTable'
);

/**
 * Load i18n file
 */
$wgMessagesDirs['FixedHeaderTable'] = __DIR__ . '/i18n';

/**
 * Register ResourceLoader modules
 */
$commonModuleInfo = array(
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'FixedHeaderTable',
);

$wgResourceModules['ext.jquery.mousewheel'] = array(
	'scripts' => 'modules/jquery.mousewheel.js',
) + $commonModuleInfo;

$wgResourceModules['ext.jquery.fixedheadertable'] = array(
	'scripts' => 'modules/jquery.fixedheadertable.js',
	'styles' => 'modules/jquery.fixedheadertable.css',
	'dependencies' => 'ext.jquery.mousewheel',
) + $commonModuleInfo;

$wgResourceModules['ext.fixedheadertable'] = array(
	'scripts' => 'modules/fixedheadertable.js',
) + $commonModuleInfo;

function wfFixedHeaderTable() {	
	MediaWikiServices::getInstance()->getParser()->setHook('fixedheadertable', 'renderFixedHeaderTable');
}

function renderFixedHeaderTable($input, $args = array(), $parser, $frame ){
	global $wgOut, $wgExtensionAssetsPath;
	
	# Register scripts
	$wgOut->addModules( 'ext.fixedheadertable' );
	
	# Set up class that will define how the widget treats the table
	$divStyle = isset($args['style']) ? "overflow: auto; " . $args['style'] : 'overflow: auto; height: 450px; width: 100%';
	$fixedColumnCount = is_numeric(isset($args['fixedcolumns']) ? $args['fixedcolumns'] : 0) ? floor($args['fixedcolumns']) : 0;
	
	# Split table markup by line
	$tableText = explode( "\n", $input );
	
	# Add fixedheadertable class to table markup
	$i = 0;
	$max = count($tableText);
	while ($i < $max)
	{
		if ( strpos($tableText[$i], '{|') !== FALSE )
		{
			if ( strpos($tableText[$i], 'class="') !== FALSE)
			{
				$tableText[$i] = preg_replace('/class="/', ('class="fixedheadertable '), $tableText[$i], 1);
			} else {
				$tableText[$i] = preg_replace('/\Q{|\E/', ('{| class="fixedheadertable"'), $tableText[$i], 1);
			}
			break;
		}		
		$i++;
	}
	
	# Add fixedcolumn class to table header cells
	$i = 0;
	$j = 0;
	$max = count($tableText);
	while ($i < $max)
	{
		if ( strpos($tableText[$i], '!') !== FALSE )
		{
			if ( strpos($tableText[$i], 'class="') !== FALSE)
			{
				$tableText[$i] = preg_replace('/class="/', ('class="fixedcolumn '), $tableText[$i], 1);				
			} else {
				$tableText[$i] = preg_replace('/\Q!\E/', ('! class="fixedcolumn"'), $tableText[$i], 1);				
			}
			$j++;
		}
		if ($j >= $fixedColumnCount) break;
		$i++;
	}
	
	# Put it all back together and MediaWiki parse
	$newInput = implode( "\n", $tableText);
	$parsedText = $parser->recursiveTagParse( $newInput );
	
	$output = '<div style="%1$s">%2$s</div>';
	
	# Return and let jquery.fixedheadertable do the rest.
	return sprintf($output, $divStyle, $parsedText);	
}