<?php
/** --------------------------------------------
 * === MediaWiki Extension: Add Article to Category 2 ===
 * @file
 * @ingroup Extensions
 * @version 1.2
 * @author Liang Chen <anything@liang-chen.com> (original code)
 * @author Julien Devincre (exclude categories)
 * @author Cynthia Mattingly - Marketing Factory Consulting (i18n, adding category)
 * @author Mikael Lindmark <mikael.lindmark@umu.se> (category adding optional, input check)
 * @license GPL-3.0-or-later
 *
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
 *
 * --------------------------------------------
 */
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'ArticleToCategory2' );
	wfWarn(
		'Deprecated PHP entry point used for ArticleToCategory2 extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the ArticleToCategory2 extension requires MediaWiki 1.29+' );
}
