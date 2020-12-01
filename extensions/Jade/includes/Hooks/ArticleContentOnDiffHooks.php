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

use DifferenceEngine;
use Jade\EntityBuilder;
use OutputPage;

class ArticleContentOnDiffHooks {

	/**
	 * Add Jade elements before showing the article content below a diff on the Special:Diff page.
	 * NB:
	 * - Jade elements are added based on Jade user preference settings.
	 * - Works only on pages that are not in the Jade namespace.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleContentOnDiff
	 * @param DifferenceEngine $diffEngine The DifferenceEngine.
	 * @param OutputPage $output The OutputPage object ($wgOut).
	 */
	public static function onArticleContentOnDiff( $diffEngine, $output ) {
		$hideJadeOnSpecialIntegrationPages =
			$output->getUser()->getOption( 'hide-jade-on-secondary-integration-pages' );
		if ( !$hideJadeOnSpecialIntegrationPages ) {
			$title = $output->getTitle();
			if ( !$title->inNamespace( NS_JADE ) ) {
				$entityBuilder = new EntityBuilder( $output->getUser() );
				$entityBuilder->loadEntityOnSecondaryIntegrationPage(
					$diffEngine->getNewid(),
					'specialDiffPage',
					$output
				);
			}
		}
	}

}
