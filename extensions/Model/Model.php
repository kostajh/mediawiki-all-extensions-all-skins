<?php
/**
 * Initialization file for the Model extension.
 *
 * @file Model.php
 * @ingroup Model
 *
 * @licence GNU GPL v3
 * @author Vedmaka < god.vedmaka@gmail.com >
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die('Not an entry point.');
}

if ( version_compare( $wgVersion, '1.27', '<' ) ) {
    die('<b>Error:</b> This version of Model requires MediaWiki 1.27 or above.');
}

global $wgModel;
$wgModelDir = dirname( __FILE__ );

/* Credits page */
$wgExtensionCredits['specialpage'][] = array(
    'path' => __FILE__,
    'name' => 'Model',
    'version' => '0.2.0',
    'author' => 'Vedmaka',
    'url' => 'https://www.mediawiki.org/wiki/Extension:Model',
    'descriptionmsg' => 'model-desc',
);

/* Message Files */
$wgMessagesDirs['Model'] = __DIR__ . '/i18n';

/* Autoload classes */
$wgAutoloadClasses['Model'] = dirname( __FILE__ ) . '/Model.class.php';
#$wgAutoloadClasses['ModelHooks'] = dirname( __FILE__ ) . '/Model.hooks.php';

/* ORM,MODELS */
#$wgAutoloadClasses['Model_Model_'] = dirname( __FILE__ ) . '/includes/Model_Model_.php';

/* ORM,PAGES */
#$wgAutoloadClasses['ModelSpecial'] = dirname( __FILE__ ) . '/pages/ModelSpecial/ModelSpecial.php';

/* Rights */
#$wgAvailableRights[] = 'example_rights';

/* Permissions */
#$wgGroupPermissions['sysop']['example_rights'] = true;

/* Special Pages */
#$wgSpecialPages['Model'] = 'ModelSpecial';

/* Hooks */
#$wgHooks['example_hook'][] = 'ModelHooks::onExampleHook';
