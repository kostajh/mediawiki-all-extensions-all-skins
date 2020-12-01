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

use EditPage;
use Jade\EntityBuilder;

class EditPageGetDiffContentHooks {

	/**
	 * Add Jade elements before showing the article edit form below a diff on the undo page.
	 * NB:
	 * - Jade elements are added based on Jade user preference settings.
	 * - Works only on pages that are not in the Jade namespace.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPageGetDiffContent
	 * @param EditPage $editPage The EditPage object.
	 * @param array &$newtext wikitext that will be used as "your version".
	 */
	public static function onEditPageGetDiffContent( $editPage, &$newtext ) {
		$editPageContext = $editPage->getContext();
		$hideJadeOnSpecialIntegrationPages =
			$editPageContext->getUser()->getOption( 'hide-jade-on-secondary-integration-pages' );
		if ( !$hideJadeOnSpecialIntegrationPages ) {
			$title = $editPage->getTitle();
			if ( !$title->inNamespace( NS_JADE ) ) {
				$editPageOutput = $editPageContext->getOutput();
				$entityBuilder = new EntityBuilder( $editPageContext->getUser() );
				$entityBuilder->loadEntityOnSecondaryIntegrationPage(
					$editPage->undidRev,
					'undoEditPage',
					$editPageOutput
				);
			}
		}
	}

}
