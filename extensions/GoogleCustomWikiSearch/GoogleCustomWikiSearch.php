<?php
# Alert the user that this is not a valid entry point to MediaWiki
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'GoogleCustomWikiSearch',
	'author' => 'Ike Hecht for [http://wikiworks.com/ WikiWorks]',
	'url' => 'https://www.mediawiki.org/wiki/Extension:GoogleCustomWikiSearch',
	'descriptionmsg' => 'gcws-desc',
	'version' => '0.5.0 beta',
);

$dir = __DIR__ . '/';

$wgAutoloadClasses['GoogleCustomWikiSearch'] = $dir . 'GoogleCustomWikiSearch.class.php';
$wgAutoloadClasses['GoogleCustomWikiSearchHooks'] = $dir . 'GoogleCustomWikiSearch.hooks.php';
$wgAutoloadClasses['SpecialGoogleCustomWikiSearch'] = $dir . 'SpecialGoogleCustomWikiSearch.php';

$wgExtensionMessagesFiles['GoogleCustomWikiSearchAlias'] = $dir .
	'SpecialGoogleCustomWikiSearch.alias.php';
$wgMessagesDirs['GoogleCustomWikiSearch'] = $dir . 'i18n';

$wgSpecialPages['GoogleCustomWikiSearch'] = 'SpecialGoogleCustomWikiSearch';

# Possibly replace the standard search functionality with this extension
$wgHooks['SpecialSearchSetupEngine'][] = 'GoogleCustomWikiSearchHooks::onSpecialSearchSetupEngine';
# Or add it to the standard search page
$wgHooks['SpecialPageAfterExecute'][] = 'GoogleCustomWikiSearchHooks::onSpecialPageAfterExecute';

$wgResourceModules['ext.googleCustomWikiSearch'] = array(
	'styles' => array( 'ext.googleCustomWikiSearch.css' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'GoogleCustomWikiSearch',
);

require_once $dir . 'GoogleCustomWikiSearch.settings.php';

unset( $dir );
