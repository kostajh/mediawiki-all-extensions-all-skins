<?php

use MediaWiki\Revision\RenderedRevision;
use MediaWiki\Revision\RevisionRecord;

class SofaHooks {
	public static function onLoadExtensionSchemaUpdates( $updater ) {
		$base = dirname( __DIR__ );
		$updater->addExtensionTable( 'sofa_map', "$base/sql/tables.sql" );
	}

	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'sofaset', 'SofaHooks::sofaSet' );
	}

	public static function sofaSet( Parser $parser, $schema = null, $key = null, $value = null ) {
		if ( $schema === null || $key === null ) {
			return '<strong class="error">' .
				wfMessage( 'sofa-noschemakey' )->inContentLanguage()->text() .
				'</strong>';
		}

		try {
			$schema = SofaSchema::normalizeSchema( $schema );
		} catch ( InvalidSofaSchemaException $e ) {
			return '<strong class="error">' .
				wfMessage( 'sofa-invalidschema' )->inContentLanguage()->text() .
				'</strong>';
		}
		$key = SofaDB::normalizeKey( $key );

		if ( $key === null ) {
			return '<strong class="error">' .
				wfMessage( 'sofa-invalidkey' )->inContentLanguage()->text() .
				'</strong>';
		}

		$value = $value === null ?: FormatJson::encode( trim( $value ), false, FormatJson::ALL_OK );

		if ( !SofaDB::validateValue( $value ) ) {
			return '<strong class="error">' .
				wfMessage( 'sofa-invalidvalue' )->inContentLanguage()->text() .
				'</strong>';
		}

		$maps = $parser->getOutput()->getExtensionData( 'SofaMaps' ) ?: [];
		// Note, we can have duplicate keys & values.
		$maps[] = [
			'schema' => $schema,
			'key' => $key,
			'value' => $value,
		];
		$parser->getOutput()->setExtensionData( 'SofaMaps', $maps );
		return '';
	}

	/**
	 * Insert our update job
	 *
	 * @param Title $title
	 * @param RenderedRevision $rendRev Wrapper containing ParserOutput
	 * @param DeferrableUpdate[] &$allUpdates List of updates for this page
	 */
	public static function onRevisionDataUpdates(
		Title $title,
		RenderedRevision $rendRev,
		array &$allUpdates
	) {
		$pout = $rendRev->getRevisionParserOutput();
		$smaps = $pout->getExtensionData( 'SofaMaps' );
		if ( $smaps ) {
			// We have data, so schedule an update
			$allUpdates[] = new SofaMapUpdate( $title, $smaps );
		}
	}

	/**
	 * Hook called for generating page deletion related updates
	 *
	 * @param Title $title Page being deleted
	 * @param RevisionRecord $revRec RevisionRecord of page being deleted
	 * @param DeferrableUpdate[] &$updates List of updates to run
	 */
	public static function onPageDeletionDataUpdates(
		Title $title,
		RevisionRecord $revRec,
		array &$updates
	) {
		$id = $revRec->getPageId();
		if ( !$id ) {
			throw new LogicException( "Unknown page id" );
		}
		// @todo Maybe this should be its own job
		$sofaDB = new SofaDB;
		$updates = array_merge( $updates, $sofaDB->getDeletionUpdates( $id ) );
	}
}
