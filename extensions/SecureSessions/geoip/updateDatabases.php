<?php

/**
 * SecureSessions extension for MediaWiki
 * (c) 2012 Tyler Romeo <tylerromeo@gmail.com>
 *
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
 * @ingroup Extensions
 * @author Tyler Romeo <tylerromeo@gmail.com>
 * @copyright (c) 2012 by Tyler Romeo
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 3.0 or later
 */

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Script to update the GeoIP database files.
 */
class SecureSessionsUpdateDatabase extends Maintenance {

	function __construct() {
		parent::__construct();

		$this->requireExtension( 'SecureSessions' );
	}

	public function execute() {
		if( !function_exists( 'gzuncompress' ) ) {
			$this->error( 'PHP must be installed with Zlib to use this script.', true );
		}

		$files = array(
			'GeoIP.db.dat' => 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz',
			'GeoIP.dbv6.dat' => 'http://geolite.maxmind.com/download/geoip/database/GeoIPv6.dat.gz'
		);

		foreach( $files as $filename => $url ) {
			$path = __DIR__ . "/$filename";
			$timestamp = new MWTimestamp( filemtime( $path ) );
			$request = MWHttpRequest::factory( $url );
			$request->setHeader( 'If-Modified-Since', $timestamp->getTimestamp( TS_RFC2822 ) );

			$status = $request->execute();
			if( $request->getStatus() == 304 ) {
				$this->output( "$filename is up-to-date.\n" );
			} elseif( !$status->isOK() ) {
				var_dump( $request->getStatus() );
				$this->error( "Could not fetch $filename from server.", false );
			} else {
				$content = $request->getContent();
				file_put_contents( $path, gzuncompress( $content ), LOCK_EX );
				$this->output( "Successfully updated $filename.\n" );
			}
		}
	}
}

$maintClass = 'SecureSessionsUpdateDatabase';
require_once( RUN_MAINTENANCE_IF_MAIN );
