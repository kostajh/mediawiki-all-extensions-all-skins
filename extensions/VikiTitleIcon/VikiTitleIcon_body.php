<?php
/*
 * Copyright (c) 2014 The MITRE Corporation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

class VikiTitleIcon {

	static function efVikiTitleIcon_AddResource ( & $parser ) {
		VikiJS::addResourceModule( "ext.VikiTitleIcon" );
		return true;
	}

	static function onRegistration() {
		global $wgExtensionFunctions;
		$wgExtensionFunctions[] = 'VikiTitleIcon::checkForVIKI';

		global $wgVIKI_Function_Hooks;

		if ( !isset( $wgVIKI_Function_Hooks ) )
			$wgVIKI_Function_Hooks = array();

		if ( array_key_exists( 'AfterVisitNodeHook', $wgVIKI_Function_Hooks ) )
			$wgVIKI_Function_Hooks['AfterVisitNodeHook'][] = 'VIKI.VikiTitleIcon.checkForTitleIcon';
		else
			$wgVIKI_Function_Hooks['AfterVisitNodeHook'] = array( 'VIKI.VikiTitleIcon.checkForTitleIcon' );
	}

	static function checkForVIKI () {
		if( !ExtensionRegistry::getInstance()->isLoaded("VIKI") ) {
			die("<b>Error:</b> The extension VikiTitleIcon requires VIKI to be installed. Be sure to call <b>wfLoadExtension(\"VIKI\")</b> in LocalSettings.php.");
		}
	}
}