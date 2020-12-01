<?php
/**
 * Main entry point for the Semantic Web Browser extension.
 * @author Anna Kantorovitch, Benedikt Kämpgen and Andreas Adler
 * @file SemanticWebBrowser.php
 * @ingroup SWB
 */

// ask if we are being called directly
if ( !defined( 'MEDIAWIKI' ) ) {
        die( 'Not an entry point.' );
}

// load settings file
require_once dirname( __FILE__ ) . '/SWB_Settings.php';

/**
 * Global functions used for setting up the Semantic WebBrowser extension.
 *
 */
// include for toolbox
global $swbgToolboxBrowseSemWeb, $wgHooks, $wgAutoloadClasses, $swbgIP,
       $wgFooterIcons, $wgExtensionFunctions,
       $wgExtensionMessagesFiles, $wgSpecialPages,
       $smwgNamespace, $wgServer, $wgAPIModules, $wgMessagesDirs;

// Internationalization
$wgMessagesDirs['SemanticWebBrowser'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['SemanticWebBrowserAlias'] = __DIR__ . '/SemanticWebBrowser.alias.php';

// Special Page for Browse Wiki
$wgAutoloadClasses['SWBSpecialBrowseSW'] = $swbgIP . 'specials/SearchTriple/SWB_SpecialBrowseSW.php';
$wgSpecialPages['BrowseSW'] = 'SWBSpecialBrowseSW';

// InfoLink
$wgAutoloadClasses['SWBInfolink'] = $swbgIP . 'includes/SWB_Infolink.php';

// Data values
$wgAutoloadClasses['SWBResolvableUriValue'] = $swbgIP . 'includes/datavalues/SWBResolvableUriValue.php';

// Hooks used
$wgHooks['smwInitProperties'][] = 'registerPropertyTypes';

// And action ...
function registerPropertyTypes() {
	SMWDataValueFactory::registerDatatype( "_rur", "SWBResolvableUriValue",
		SMWDataItem::TYPE_URI, $label = false );

	return true;
}

/*
 * Include in toolbox to show the last article in "Browsing Semantic Web".
 * Has the same functionality as 'Browse properties' in the toolbox.
 */
if ( $swbgToolboxBrowseSemWeb ) {
	$wgHooks['SkinTemplateToolboxEnd'][] = 'swbfShowBrowseSemWeb';
}


function swbfShowBrowseSemWeb( $skintemplate ) {
	if ( $skintemplate->data['isarticle'] ) {
		$browselink = SWBInfolink::newBrowsingLink( wfMessage( 'browsesw' )->text(),
			$skintemplate->data['titleprefixeddbkey'], false );
		echo '<li id="t-smwbrowselink">' . $browselink->getHTML() . '</li>';
	}

	return true;
}
