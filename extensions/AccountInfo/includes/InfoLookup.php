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

namespace MediaWiki\AccountInfo;

use CheckUserHooks;
use ExtensionRegistry;
use User;
use WebRequest;

class InfoLookup {

	/**
	 * Whether CheckUser extension is installed
	 * @return bool
	 */
	public function isCUInstalled() {
		return ExtensionRegistry::getInstance()->isLoaded( 'CheckUser' );
	}

	/**
	 * Whether IP's are stored in the RecentChanges table
	 * @return bool
	 */
	public function areIPsInRC() {
		global $wgPutIPinRC;

		return $wgPutIPinRC;
	}

	/**
	 * @param WebRequest $req
	 * @return string|false
	 */
	public function getUserAgent( WebRequest $req ) {
		return $req->getHeader( 'User-Agent' );
	}

	/**
	 * Gets the XFF IP from a given WebRequest
	 * Depends on CU being installed
	 * @param WebRequest $req
	 * @return string
	 */
	public function getXFF( WebRequest $req ) {
		if ( !$this->isCUInstalled() ) {
			// Should not be called, but be safe.
			return '';
		}
		list( $xff_ip, $isSquidOnly ) = CheckUserHooks::getClientIPfromXFF(
			$req->getHeader( 'X-Forwarded-For' )
		);

		return ( $xff_ip && !$isSquidOnly ) ? $xff_ip : '';
	}

	/**
	 * @param User $user
	 * @return array
	 */
	public function getUserInfo( User $user ) {
		global $wgRCMaxAge, $wgCUDMaxAge;
		if ( $this->areIPsInRC() && !$this->isCUInstalled() ) {
			// Check the table...
			$rows = wfGetDB( DB_REPLICA )->select(
				'recentchanges',
				'DISTINCT(rc_ip) AS ip',
				[ 'rc_user' => $user->getId() ],
				__METHOD__
			);
			// Convert for table-fication...
			$outRows = [];
			foreach ( $rows as $row ) {
				$outRows[] = [ null, $row->ip, null, null ];
			}

			return [
				'rows' => $outRows,
				'length' => $wgRCMaxAge,
			];
		}

		// Now CheckUser...
		if ( $this->isCUInstalled() ) {
			$rows = wfGetDB( DB_REPLICA )->select(
				'cu_changes',
				[ 'cuc_timestamp', 'cuc_ip', 'cuc_agent', 'cuc_xff' ],
				[ 'cuc_user' => $user->getId() ],
				__METHOD__,
				[ 'GROUP BY' => 'cuc_ip, cuc_agent, cuc_xff' ]
			);
			$outRows = [];
			foreach ( $rows as $row ) {
				$outRows[] = [
					$row->cuc_timestamp,
					$row->cuc_ip,
					$row->cuc_agent,
					$row->cuc_xff,
				];
			}

			return [
				'rows' => $outRows,
				'length' => $wgCUDMaxAge,
			];
		}
	}
}
