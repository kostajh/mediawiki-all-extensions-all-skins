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

use ChangesListSpecialPage;
use ChangesListStringOptionsFilterGroup;
use IContextSource;
use IDatabase;

class ChangesListSpecialPageStructuredFiltersHooks {

	/**
	 * Add Jade Filters for Edit Review on Recent Changes, Watchlist and Related Changes pages.
	 * NB: These Jade Filters rely on the "preferred" label.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ChangesListSpecialPageStructuredFilters
	 * @param ChangesListSpecialPage $special Instance of Special page which uses
	 * a ChangesList to show query results.
	 */
	public static function onChangesListSpecialPageStructuredFilters(
		ChangesListSpecialPage $special
	) {
		$jadeFiltersGroup = new ChangesListStringOptionsFilterGroup(
			[
				'name' => 'jade',
				'title' => 'jade-rcfilters-group-title',
				'whatsThisHeader' => 'jade-rcfilters-group-whats-this-header',
				'whatsThisBody' => 'jade-rcfilters-group-whats-this-body',
				'whatsThisUrl' => 'https://www.mediawiki.org/wiki/Jade/Edit_quality',
				'whatsThisLinkText' => 'jade-rcfilters-group-whats-this-link-text',
				'default' => ChangesListStringOptionsFilterGroup::NONE,
				'filters' => [
					[
						'name' => 'productivegoodfaith',
						'label' => 'jade-rcfilters-group-productivegoodfaith-label',
						'description' => 'jade-rcfilters-group-productivegoodfaith-desc',
						'cssClassSuffix' => 'productivegoodfaith',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							return intval( $rc->getAttribute( 'jadedl_damaging' ) ) === 0
								&& intval( $rc->getAttribute( 'jadedl_goodfaith' ) ) === 1;
						},
					],
					[
						'name' => 'damaginggoodfaith',
						'label' => 'jade-rcfilters-group-damaginggoodfaith-label',
						'description' => 'jade-rcfilters-group-damaginggoodfaith-desc',
						'cssClassSuffix' => 'damaginggoodfaith',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							return intval( $rc->getAttribute( 'jadedl_damaging' ) ) === 1
								&& intval( $rc->getAttribute( 'jadedl_goodfaith' ) ) === 1;
						},
					],
					[
						'name' => 'damagingbadfaith',
						'label' => 'jade-rcfilters-group-damagingbadfaith-label',
						'description' => 'jade-rcfilters-group-damagingbadfaith-desc',
						'cssClassSuffix' => 'damagingbadfaith',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							return intval( $rc->getAttribute( 'jadedl_damaging' ) ) === 1
								&& intval( $rc->getAttribute( 'jadedl_goodfaith' ) ) === 0;
						},
					],
					[
						'name' => 'unlabeled',
						'label' => 'jade-rcfilters-group-unlabeled-label',
						'description' => 'jade-rcfilters-group-unlabeled-desc',
						'cssClassSuffix' => 'unlabeled',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							return $rc->getAttribute( 'jadedl_damaging' ) === null
								&& $rc->getAttribute( 'jadedl_goodfaith' ) === null;
						},
					]
				],
				'isFullCoverage' => true,
				'queryCallable' => function (
					$specialPageClassName,
					IContextSource $context,
					IDatabase $dbr,
					array &$tables,
					array &$fields,
					array &$conds,
					array &$query_options,
					array &$join_conds,
					array $selectedValues
				) {
					if ( in_array( 'unlabeled', $selectedValues ) ) {
						$join_conds[ 'jade_diff_label' ] = [ 'LEFT JOIN', 'jadedl_rev_id=rc_this_oldid' ];
						$conditionString = 'jadedl_damaging IS NULL';
					} else {
						$join_conds[ 'jade_diff_label' ] = [ 'INNER JOIN', 'jadedl_rev_id=rc_this_oldid' ];
						$conditionValues = [
							'productivegoodfaith' => '( jadedl_damaging=0 AND jadedl_goodfaith=1 )',
							'damaginggoodfaith' => '( jadedl_damaging=1 AND jadedl_goodfaith=1 )',
							'damagingbadfaith' => '( jadedl_damaging=1 AND jadedl_goodfaith=0 )',
						];
						$selectedConditions = [];
						foreach ( $selectedValues as $value ) {
							$selectedConditions[] = $conditionValues[ $value ];
						}
						$conditionString = $dbr->makeList( $selectedConditions, $dbr::LIST_OR );
					}
					$conds[] = $conditionString;
				}
			]
		);
		$special->registerFilterGroup( $jadeFiltersGroup );
	}

}
