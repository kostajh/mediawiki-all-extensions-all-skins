<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

class ExternalLinkConfirmHooks {

	/**
	 * Hides content for the browser with disabled CSS
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @param array &$bodyAttrs
	 */
	public static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$bodyAttrs ) {
		// It is important to don't allow user to click on an external link without showing the confirmation dialog
		// It adds CSS styles that don't allow user to click on external links before thy handled by JS code
		// But user may have CSS and JS disabled, it hides content in case if CSS is disabled.
		$bodyAttrs['hidden'] = "";
	}

	/**
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		// It is important to have this styles injected on the html page because user may not load CSS file
		$styleContent = 'a[href*="//"]:not( .ExternalLinkConfirmHandled ) { pointer-events: none; } ' .
			'body[hidden] { display: block !important; }';
		$style = Html::rawElement( 'style', [], $styleContent );
		$out->addHeadItem( 'ExternalLinkConfirmCSS', $style );
		$out->addModules( 'ext.ExternalLinkConfirm' );

		$config = $out->getConfig();
		$out->addJsConfigVars( [
			'ExternalLinkConfirmWhitelist' => $config->get( 'ExternalLinkConfirmWhitelist' ),
			'ExternalLinkConfirmTarget' => $config->get( 'ExternalLinkConfirmTarget' ),
			'ExternalLinkConfirmDefaultTarget' => $config->get( 'ExternalLinkTarget' )
		] );
	}
}
