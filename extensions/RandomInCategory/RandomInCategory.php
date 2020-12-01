<?php
/**
 * Special page to direct the user to a random page in specified category
 *
 * @file
 * @ingroup Extensions
 * @author VasilievVV <vasilvv@gmail.com>, based on SpecialRandompage.php code
 * @license GPL-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'RandomInCategory' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['RandomInCategory'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for RandomInCategory extension. Please use ' .
		'wfLoadExtension instead, see https://www.mediawiki.org/wiki/Extension_registration for more ' .
		'details.'
	);
	return;
} else {
	die( 'This version of the RandomInCategory extension requires MediaWiki 1.25+' );
}
