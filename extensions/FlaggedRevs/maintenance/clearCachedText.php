<?php

/**
 * Simple script to clear the fr_text field in a replication-friendly way
 */

// @phan-file-suppress PhanUndeclaredMagicConstant Global scope

use MediaWiki\MediaWikiServices;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( strval( $IP ) == '' ) {
	$IP = __DIR__ . '/../../..';
}
$optionsWithArgs = [ 'backup' ];
require "$IP/maintenance/commandLine.inc";

$pageId = 0;
$revId = 0;

$dbr = wfGetDB( DB_REPLICA );
$dbw = wfGetDB( DB_MASTER );
$batchSize = 1000;
$maxPage = $dbr->selectField( 'flaggedrevs', 'MAX(fr_page_id)', '', __METHOD__ );

if ( !isset( $options['backup'] ) ) {
	echo "Usage: clearCachedText.php --backup=<file>\n";
	exit( 1 );
}

$backupFile = fopen( $options['backup'], 'w' );
if ( !$backupFile ) {
	echo "Unable to open backup file\n";
	exit( 1 );
}

/**
 * @suppress SecurityCheck-XSS
 * @param int $pageId
 * @param int $maxPage
 */
function wfPrintProgress( $pageId, $maxPage ) {
	echo "$pageId / $maxPage\n";
}

$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();

while ( true ) {
	$res = $dbr->select( 'flaggedrevs', '*',
		[
			"fr_page_id > $pageId OR (fr_page_id = $pageId AND fr_rev_id > $revId)",
			"fr_flags NOT LIKE '%dynamic%'",
		], __METHOD__, [ 'LIMIT' => $batchSize ]
	);
	if ( !$res->numRows() ) {
		break;
	}
	foreach ( $res as $row ) {
		$flags = explode( ',', $row->fr_flags );
		$backupRecord = [ $row->fr_page_id, $row->fr_rev_id, $row->fr_flags, $row->fr_text ];
		fwrite( $backupFile, implode( "\t", array_map( 'rawurlencode', $backupRecord ) ) . "\n" );

		$dbw->update( 'flaggedrevs',
			[ /* SET */
				'fr_text' => '',
				'fr_flags' => 'dynamic',
			],
			[ /* WHERE */
				'fr_page_id' => $row->fr_page_id,
				'fr_rev_id' => $row->fr_rev_id,
			],
			__METHOD__
		);
	}
	// @phan-suppress-next-line PhanPossiblyUndeclaredGlobalVariable
	$pageId = $row->fr_page_id;
	// @phan-suppress-next-line PhanPossiblyUndeclaredGlobalVariable
	$revId = $row->fr_rev_id;
	$lbFactory->waitForReplication( [ 'ifWritesSince' => 5 ] );
	wfPrintProgress( $pageId, $maxPage );
}
