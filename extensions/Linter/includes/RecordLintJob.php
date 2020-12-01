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

namespace MediaWiki\Linter;

use Job;
use MediaWiki\MediaWikiServices;
use Title;

class RecordLintJob extends Job {
	/**
	 * RecordLintJob constructor.
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( Title $title, array $params ) {
		parent::__construct( 'RecordLintJob', $title, $params );
	}

	public function run() {
		if ( isset( $this->params['revision'] )
			&& $this->title->getLatestRevID() != $this->params['revision']
		) {
			// Outdated now, let a later job handle it
			return true;
		}

		// [ 'id' => LintError ]
		$errors = [];
		foreach ( $this->params['errors'] as $errorInfo ) {
			$error = new LintError(
				$errorInfo['type'],
				$errorInfo['location'],
				$errorInfo['params'],
				$errorInfo['dbid']
			);
			// Use unique id as key to get rid of exact dupes
			// (e.g. same category of error in same template)
			$errors[$error->id()] = $error;
		}
		$lintDb = new Database( $this->title->getArticleID() );
		$changes = $lintDb->setForPage( $errors );
		$this->updateStats( $lintDb, $changes );

		return true;
	}

	/**
	 * Send stats to statsd and update totals cache
	 *
	 * @param Database $lintDb
	 * @param array $changes
	 */
	protected function updateStats( Database $lintDb, array $changes ) {
		global $wgLinterStatsdSampleFactor;

		$mwServices = MediaWikiServices::getInstance();

		$totalsLookup = new TotalsLookup(
			new CategoryManager(),
			$mwServices->getMainWANObjectCache()
		);

		if ( $wgLinterStatsdSampleFactor === false ) {
			// Don't send to statsd, but update cache with $changes
			$raw = $changes['added'];
			foreach ( $changes['deleted'] as $cat => $count ) {
				if ( isset( $raw[$cat] ) ) {
					$raw[$cat] -= $count;
				} else {
					// Negative value
					$raw[$cat] = 0 - $count;
				}
			}

			foreach ( $raw as $cat => $count ) {
				if ( $count != 0 ) {
					// There was a change in counts, invalidate the cache
					$totalsLookup->touchCategoryCache( $cat );
				}
			}
			return;
		} elseif ( mt_rand( 1, $wgLinterStatsdSampleFactor ) != 1 ) {
			return;
		}

		$totals = $lintDb->getTotals();
		$wiki = wfWikiID();

		$stats = $mwServices->getStatsdDataFactory();
		foreach ( $totals as $name => $count ) {
			$stats->gauge( "linter.category.$name.$wiki", $count );
		}

		$stats->gauge( "linter.totals.$wiki", array_sum( $totals ) );

		$totalsLookup->touchAllCategoriesCache();
	}

}
