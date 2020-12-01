<?php
/**
 * VirtualKeyboard extension
 *
 * For more info see https://mediawiki.org/wiki/Extension:VirtualKeyboard
 * Uses VirtualKeyboard by Ilya Lebedev
 *
 * @file
 * @ingroup Extensions
 * @author Ike Hecht, 2015
 * @license GNU General Public Licence 2.0 or later
 */
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'VirtualKeyboard',
	'author' => array(
		'Ike Hecht',
	),
	'version' => '0.2.0',
	'url' => 'https://www.mediawiki.org/wiki/Extension:VirtualKeyboard',
	'descriptionmsg' => 'virtualkeyboard-desc',
);

$wgAutoloadClasses['VirtualKeyboard'] = __DIR__ . '/VirtualKeyboard.class.php';

// The VK library cannot be added to the ResourceLoader because
// of the way vk_popup.js finds "path to this file"
$wgResourceModules['ext.VirtualKeyboad'] = array(
	'scripts' => 'modules/VirtualKeyboard.js',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'VirtualKeyboard'
);

// i18n
$wgMessagesDirs['VirtualKeyboard'] = __DIR__ . '/i18n';

// HOOKS
/**
 * Insert all the scripts and HTML we need.
 */
$wgHooks['BeforePageDisplay'][] = function( OutputPage &$out ) {
	global $wgVirtualKeyboardMode, $wgVirtualKeyboardSkin, $wgExtensionAssetsPath;

	$basePath = "$wgExtensionAssetsPath/VirtualKeyboard/modules/VirtualKeyboard.full.3.7.2/";
	$virtualKeyboard = new VirtualKeyboard(
		$wgVirtualKeyboardMode, $basePath, $wgVirtualKeyboardSkin );
	$out->addScriptFile( $virtualKeyboard->getScriptFile() );
	$out->addScript( $virtualKeyboard->getScript() );

	$out->addModules( 'ext.VirtualKeyboad' );

	if ( $wgVirtualKeyboardMode == VirtualKeyboard::IFRAME ) {
		// Add an empty div for the iframe keyboard. Goes at page bottom, which
		// is not always useful, but may be best spot.
		// We could use hook EditPage::showEditForm:initial but perhaps users want it on non-edit
		// pages, such as for searching.
		$out->addHTML( Html::element( 'div', array( 'id' => 'virtual-keyboard-iframe' ) ) );
	}

	return true;
};

$wgHooks['ResourceLoaderGetConfigVars'][] = function( &$vars ) {
	global $wgVirtualKeyboardMode;
	$vars['wgVirtualKeyboardClassName'] = VirtualKeyboard::getVirtualKeyboardClassName(
			$wgVirtualKeyboardMode );
	return true;
};

/**
 * If appropriate, add a toggle link to the bottom of the toolbox.
 */
$wgHooks['BaseTemplateToolbox'][] = function( BaseTemplate $baseTemplate, array &$toolbox ) {
	global $wgVirtualKeyboardMode;

	$class = VirtualKeyboard::getVirtualKeyboardClassName( $wgVirtualKeyboardMode );
	if ( $class == false ) {
		// This is Easy mode, so no need for toggle
		return true;
	}

	$toolbox['virtualkeyboard'] = array(
		'href' => '#',
		'msg' => 'virtualkeyboard-toggle',
		/** @todo This input must exist! Maybe hack by creating hidden input. */
		/** @todo second parameter is not meaningful unless using IFRAME */
		'onclick' => "$class.toggle('searchInput', 'virtual-keyboard-iframe');",
		'id' => 't-virtualkeyboardlink',
	);
};

// CONFIG
/**
 * Which mode the Virtual Keyboard should use.
 * For possible values, see the VirtualKeyboard class.
 */
$wgVirtualKeyboardMode = VirtualKeyboard::POPUP;

/**
 * Which skin the Virtual Keyboard should use.
 * Can be: air_large, air_mid, air_small, flat_gray, goldie, small, soberTouch, textual, winxp
 * or set to null for no skin
 */
$wgVirtualKeyboardSkin = 'flat_gray';
