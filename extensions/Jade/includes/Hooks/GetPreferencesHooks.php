<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Jade\Hooks;

use User;

class GetPreferencesHooks {

	/**
	 * Add user preference to [[Special:Preferences]] under Beta features tab.
	 * Used for hiding/showing Jade on secondary integration pages.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 * @param User $user User whose preferences are being modified.
	 * @param array &$preferences Preferences description array, to be fed to an HTMLForm object
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		$preferences[ 'hide-jade-on-secondary-integration-pages' ] = [
			'type' => 'toggle',
			'label-message' => 'jade-hide-elements-on-secondary-integration-pages',
			'section' => 'betafeatures/jade',
		];
	}

}
