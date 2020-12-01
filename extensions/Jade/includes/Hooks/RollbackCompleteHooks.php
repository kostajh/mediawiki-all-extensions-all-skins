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

use Jade\EntityBuilder;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\User\UserIdentity;
use RequestContext;
use User;
use WikiPage;

class RollbackCompleteHooks {

	/**
	 * Add Jade elements above diff on the rollback page.
	 * NB:
	 * - Jade elements are added based on Jade user preference settings.
	 * - Works only on pages that are not in the Jade namespace.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/RollbackComplete
	 * @param WikiPage $wikiPage The article that was edited.
	 * @param UserIdentity $user The user who did the rollback.
	 * @param RevisionRecord $revision The RevisionRecord the page was reverted back to.
	 * @param RevisionRecord $current The RevisionRecord object of the top edit that was reverted.
	 */
	public static function onRollbackComplete( $wikiPage, $user, $revision, $current ) {
		$currentUser = User::newFromIdentity( $user );
		$hideJadeOnSpecialIntegrationPages =
			$currentUser->getOption( 'hide-jade-on-secondary-integration-pages' );
		if ( !$hideJadeOnSpecialIntegrationPages ) {
			$title = $wikiPage->getTitle();
			if ( !$title->inNamespace( NS_JADE ) ) {
				$rollbackPageContext = RequestContext::getMain();
				$rollbackPageOutput = $rollbackPageContext->getOutput();
				$entityBuilder = new EntityBuilder(
					$currentUser
				);
				$entityBuilder->loadEntityOnSecondaryIntegrationPage(
					$current->getId(),
					'rollbackPage',
					$rollbackPageOutput
				);
			}
		}
	}

}
