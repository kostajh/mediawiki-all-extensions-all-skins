<?php
/**
 * WebChat
 *
 * Integrates a web chat client in to a special page, for example Mibbit.
 *
 * @file
 * @ingroup Extensions
 *
 * @link https://www.mediawiki.org/wiki/Extension:WebChat
 *
 * @author Robert Leverington <robert@rhl.me.uk>
 * @copyright Copyright © 2008 - 2009 Robert Leverington.
 * @copyright Copyright © 2009 Marco 27.
 * @license GPL-2.0-or-later
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'WebChat' );
	wfWarn(
		'Deprecated PHP entry point used for WebChat extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the WebChat extension requires MediaWiki 1.29+' );
}
