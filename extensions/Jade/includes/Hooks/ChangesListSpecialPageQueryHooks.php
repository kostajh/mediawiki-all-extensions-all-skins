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

class ChangesListSpecialPageQueryHooks {

	/**
	 * Build SQL query for Jade RCFilter highlights.
	 * Used in ChangesListSpecialPageStructuredFiltersHooks under isRowApplicableCallable
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ChangesListSpecialPageQuery
	 * @param string $name name of the special page, e.g. 'Watchlist'
	 * @param array &$tables array of tables to be queried
	 * @param array &$fields array of columns to select
	 * @param array &$conds array of WHERE conditionals for query
	 * @param array &$query_options array of options for the database request
	 * @param array &$join_conds join conditions for the tables
	 * @param \FormOptions $opts FormOptions for this request
	 */
	public static function onChangesListSpecialPageQuery(
		$name,
		&$tables,
		&$fields,
		&$conds,
		&$query_options,
		&$join_conds,
		$opts
	) {
		$tables[] = 'jade_diff_label';
		$join_conds['jade_diff_label'] = [ 'LEFT JOIN', 'jadedl_rev_id=rc_this_oldid' ];
		$fields[] = 'jadedl_damaging';
		$fields[] = 'jadedl_goodfaith';
	}

}
