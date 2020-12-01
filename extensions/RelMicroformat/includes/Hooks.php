<?php
/**
 * (C) 2020 Kunal Mehta <legoktm@member.fsf.org>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace MediaWiki\RelMicroformat;

use MediaWiki\Hook\SkinBuildSidebarHook;
use Skin;

class Hooks implements SkinBuildSidebarHook {
	/**
	 * Hook: SkinBuildSidebar
	 *
	 * @param Skin $skin
	 * @param array &$bar
	 */
	public function onSkinBuildSidebar( $skin, &$bar ) {
		global $wgRelMicroformatUrls;
		foreach ( $bar as &$section ) {
			foreach ( $section as &$item ) {
				// If the URL is set in config, use its value as the new 'rel'
				if ( isset( $wgRelMicroformatUrls[$item['href']] ) ) {
					$item['rel'] = $wgRelMicroformatUrls[$item['href']];
				}
			}
		}
	}
}
