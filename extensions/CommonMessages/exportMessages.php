<?php
/**
 * Copyright (C) 2014 Kunal Mehta <legoktm@member.fsf.org>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../..';
}

// Require base maintenance class
require_once "$IP/maintenance/Maintenance.php";

class ExportMessages extends Maintenance {

	public function execute() {
		global $wgCommonMessagesExportDir;
		$commons = CommonMessages::singleton();
		$dbr = wfGetDB( DB_REPLICA );
		// @todo Can we make this less memory intensive?
		$rows = $dbr->select(
			'page',
			[ 'page_namespace', 'page_title', 'page_id', 'page_latest' ],
			[ 'page_namespace' => NS_MEDIAWIKI ],
			__METHOD__
		);
		$messages = [];
		foreach ( $rows as $row ) {
			$title = Title::newFromRow( $row );
			$exp = explode( '/', $title->getPrefixedText() );
			$lang = $exp[1];
			if ( !Language::isValidCode( $lang ) ) {
				wfDebugLog( 'MessageCommons', 'Found invalid page: ' . $title->getFullText() );
				continue;
			}
			$key = $commons->transformKey( strtolower( $title->getPrefixedText() ) );
			$messages[$lang][$key]
				= Revision::newFromTitle( $title )->getContent( Revision::RAW )->getNativeData();
		}
		// Now export.
		foreach ( $messages as $lang => $data ) {
			$json = FormatJson::encode( $data, true );
			file_put_contents( "$wgCommonMessagesExportDir/$lang.json", $json );
		}
	}
}

$maintClass = 'ExportMessages';
require_once RUN_MAINTENANCE_IF_MAIN;
