<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'TopTenPages' );
	 wfWarn(
		'Deprecated entry point for TopTenPages extension. Use wfLoadExtension instead. ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
} else {
	die( 'This version of the TopTenPages extension requires MediaWiki 1.25+' );
}
