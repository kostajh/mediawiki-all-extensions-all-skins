<?php
/**
 * EUCopyrightCampaignSkin
 *
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
 * @ingroup Skins
 */

/**
 * Skin subclass for EUCopyrightCampaignSkin
 * @ingroup Skins
 */
class SkinEUCopyrightCampaign extends SkinTemplate {
	public $skinname = 'eucopyrightcampaign';
	public $stylename = 'EUCopyrightCampaignSkin';
	public $template = 'EUCopyrightCampaignSkinTemplate';

	/**
	 * Source: https://en.wikipedia.org/wiki/Languages_of_the_European_Union
	 *
	 * @var array
	 */
	public $euMemberLanguagesLanguageCodes = [ 'bg', 'hr', 'cs', 'da', 'nl', 'en',
		'et', 'fi', 'fr', 'de', 'el', 'hu', 'ga', 'it', 'lv', 'lt', 'mt',
		'pl', 'pt', 'ro', 'sk', 'sl', 'es', 'sv' ];

	/**
	 * Initializes output page and sets up skin-specific parameters
	 * @param OutputPage $out Object to initialize
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

		$out->addMeta( 'viewport', 'width=device-width, initial-scale=1' );
		$out->addMeta(
			'og:image',
			$this->getConfig()->get( 'StylePath' )
			. '/EUCopyrightCampaignSkin/resources/images/fixcopyright.png'
		);
		$out->addModules( 'skins.eucopyrightcampaign.js' );
		$out->addJsConfigVars(
			'euccLanguageCodes',
			$this->euMemberLanguagesLanguageCodes
		);
		$out->enableOOUI();
	}

	/**
	 * Loads skin and user CSS files.
	 * @param OutputPage $out
	 */
	public function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		$out->addModuleStyles( [
			'mediawiki.skinning.interface',
			'skins.eucopyrightcampaign.styles',
		] );
	}

	/**
	 * Whether the logo should be preloaded with an HTTP link header or not
	 * @since 1.29
	 * @return bool
	 */
	public function shouldPreloadLogo() {
		return false;
	}
}
