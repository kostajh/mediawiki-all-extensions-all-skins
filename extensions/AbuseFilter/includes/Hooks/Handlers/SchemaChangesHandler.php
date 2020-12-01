<?php

namespace MediaWiki\Extension\AbuseFilter\Hooks\Handlers;

use DatabaseUpdater;
use FixOldLogEntries;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;
use MWException;
use NormalizeThrottleParameters;
use UpdateVarDumps;
use User;

class SchemaChangesHandler implements LoadExtensionSchemaUpdatesHook {
	/**
	 * @param DatabaseUpdater $updater
	 * @throws MWException
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dbType = $updater->getDB()->getType();
		$dir = __DIR__ . "/../../../db_patches";

		$updater->addExtensionTable(
			'abuse_filter',
			"$dir/$dbType/abusefilter.sql"
		);

		$updater->addExtensionTable(
			'abuse_filter_history',
			"$dir/$dbType/patch-abuse_filter_history.sql"
		);

		if ( $dbType === 'mysql' || $dbType === 'sqlite' ) {
			$updater->addExtensionField(
				'abuse_filter_history',
				'afh_changed_fields',
				"$dir/patch-afh_changed_fields.sql"
			);
			$updater->addExtensionField(
				'abuse_filter',
				'af_deleted',
				"$dir/patch-af_deleted.sql"
			);
			$updater->addExtensionField(
				'abuse_filter',
				'af_actions',
				"$dir/patch-af_actions.sql"
			);
			$updater->addExtensionField(
				'abuse_filter',
				'af_global',
				"$dir/patch-global_filters.sql"
			);
			$updater->addExtensionField(
				'abuse_filter_log',
				'afl_rev_id',
				"$dir/patch-afl_action_id.sql"
			);

			$updater->addExtensionIndex(
				'abuse_filter_log',
				'filter_timestamp',
				"$dir/$dbType/patch-fix-indexes.sql"
			);

			$updater->addExtensionField(
				'abuse_filter',
				'af_group',
				"$dir/patch-af_group.sql"
			);

			$updater->addExtensionIndex(
				'abuse_filter_log',
				'afl_wiki_timestamp',
				"$dir/patch-global_logging_wiki-index.sql"
			);

			$updater->modifyExtensionField(
				'abuse_filter_log',
				'afl_namespace',
				"$dir/$dbType/patch-afl-namespace_int.sql"
			);

			$updater->dropExtensionField(
				'abuse_filter_log',
				'afl_log_id',
				"$dir/$dbType/patch-drop_afl_log_id.sql"
			);

			$updater->addExtensionIndex(
				'abuse_filter_log',
				'filter_timestamp_full',
				"$dir/$dbType/patch-split-afl_filter.sql"
			);

			$updater->modifyExtensionField(
				'abuse_filter_log',
				'afl_patrolled_by',
				"$dir/$dbType/patch-afl_change_deleted_patrolled.sql"
			);
		} elseif ( $dbType === 'postgres' ) {
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter', 'af_actions', "TEXT NOT NULL DEFAULT ''"
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter', 'af_deleted', 'SMALLINT NOT NULL DEFAULT 0'
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter', 'af_global', 'SMALLINT NOT NULL DEFAULT 0'
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter', 'af_group', "TEXT NOT NULL DEFAULT 'default'"
			] );
			$updater->addExtensionUpdate( [
				'addPgExtIndex', 'abuse_filter', 'abuse_filter_group_enabled_id',
				"(af_group, af_enabled, af_id)"
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter_history', 'afh_group', "TEXT"
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter_log', 'afl_wiki', 'TEXT'
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter_log', 'afl_deleted', 'SMALLINT'
			] );
			$updater->addExtensionUpdate( [
				'setDefault', 'abuse_filter_log', 'afl_deleted', '0'
			] );
			$updater->addExtensionUpdate( [
				'changeNullableField', 'abuse_filter_log', 'afl_deleted', 'NOT NULL', true
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter_log', 'afl_patrolled_by', 'INTEGER'
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter_log', 'afl_rev_id', 'INTEGER'
			] );
			$updater->addExtensionUpdate( [
				'changeField', 'abuse_filter_log', 'afl_filter', 'TEXT', ''
			] );
			$updater->addExtensionUpdate( [
				'changeField', 'abuse_filter_log', 'afl_namespace', "INTEGER", ''
			] );
			$updater->addExtensionUpdate( [
				'dropPgIndex', 'abuse_filter_log', 'abuse_filter_log_filter'
			] );
			$updater->addExtensionUpdate( [
				'dropPgIndex', 'abuse_filter_log', 'abuse_filter_log_ip'
			] );
			$updater->addExtensionUpdate( [
				'dropPgIndex', 'abuse_filter_log', 'abuse_filter_log_title'
			] );
			$updater->addExtensionUpdate( [
				'dropPgIndex', 'abuse_filter_log', 'abuse_filter_log_user'
			] );
			$updater->addExtensionUpdate( [
				'dropPgIndex', 'abuse_filter_log', 'abuse_filter_log_user_text'
			] );
			$updater->addExtensionUpdate( [
				'dropPgIndex', 'abuse_filter_log', 'abuse_filter_log_wiki'
			] );
			$updater->addExtensionUpdate( [
				'addPgExtIndex', 'abuse_filter_log', 'abuse_filter_log_filter_timestamp',
				'(afl_filter,afl_timestamp)'
			] );
			$updater->addExtensionUpdate( [
				'addPgExtIndex', 'abuse_filter_log', 'abuse_filter_log_user_timestamp',
				'(afl_user,afl_user_text,afl_timestamp)'
			] );
			$updater->addExtensionUpdate( [
				'addPgExtIndex', 'abuse_filter_log', 'abuse_filter_log_page_timestamp',
				'(afl_namespace,afl_title,afl_timestamp)'
			] );
			$updater->addExtensionUpdate( [
				'addPgExtIndex', 'abuse_filter_log', 'abuse_filter_log_ip_timestamp',
				'(afl_ip, afl_timestamp)'
			] );
			$updater->addExtensionUpdate( [
				'addPgExtIndex', 'abuse_filter_log', 'abuse_filter_log_rev_id',
				'(afl_rev_id)'
			] );
			$updater->addExtensionUpdate( [
				'addPgExtIndex', 'abuse_filter_log', 'abuse_filter_log_wiki_timestamp',
				'(afl_wiki,afl_timestamp)'
			] );
			$updater->addExtensionUpdate( [
				'dropPgField', 'abuse_filter_log', 'afl_log_id' ] );
			$updater->addExtensionUpdate( [
				'setDefault', 'abuse_filter_log', 'afl_filter', ''
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter_log', 'afl_global', 'SMALLINT NOT NULL DEFAULT 0'
			] );
			$updater->addExtensionUpdate( [
				'addPgField', 'abuse_filter_log', 'afl_filter_id', 'INTEGER NOT NULL DEFAULT 0'
			] );
			$updater->addExtensionUpdate( [
				'addPgIndex', 'abuse_filter_log', 'abuse_filter_log_filter_timestamp_full',
				'(afl_global, afl_filter_id, afl_timestamp)'
			] );
			$updater->addExtensionUpdate( [
				'setDefault', 'abuse_filter_log', 'afl_deleted', 0
			] );
			$updater->addExtensionUpdate( [
				'changeNullableField', 'abuse_filter_log', 'afl_deleted', 'NOT NULL', true
			] );
			$updater->addExtensionUpdate( [
				'setDefault', 'abuse_filter_log', 'afl_patrolled_by', 0
			] );
			$updater->addExtensionUpdate( [
				'changeNullableField', 'abuse_filter_log', 'afl_patrolled_by', 'NOT NULL', true
			] );
		}

		$updater->addExtensionUpdate( [ [ __CLASS__, 'createAbuseFilterUser' ] ] );
		$updater->addPostDatabaseUpdateMaintenance( NormalizeThrottleParameters::class );
		$updater->addPostDatabaseUpdateMaintenance( FixOldLogEntries::class );
		$updater->addPostDatabaseUpdateMaintenance( UpdateVarDumps::class );
	}

	/**
	 * Updater callback to create the AbuseFilter user after the user tables have been updated.
	 * @todo Move elsewhere, use DI
	 * @param DatabaseUpdater $updater
	 */
	public static function createAbuseFilterUser( DatabaseUpdater $updater ) : void {
		$username = wfMessage( 'abusefilter-blocker' )->inContentLanguage()->text();
		$user = User::newFromName( $username );

		if ( $user && !$updater->updateRowExists( 'create abusefilter-blocker-user' ) ) {
			$user = User::newSystemUser( $username, [ 'steal' => true ] );
			$updater->insertUpdateRow( 'create abusefilter-blocker-user' );
			// Promote user so it doesn't look too crazy.
			$user->addGroup( 'sysop' );
		}
	}
}
