<?php
	class GoogleAnalyticsTopPagesHooks {
		/**
		 * LoadExtensionSchemaUpdate Hook handler
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdate
		 */
		public static function onLoadExtensionSchemaUpdates( $updater = null ) {
			global $wgDBname, $wgSharedDB, $wgDBtype;

			// Don't create tables on a shared database
			if (
				!empty( $wgSharedDB ) &&
				$wgSharedDB !== $wgDBname
			) {
				return true;
			}
			// Tables to add to the database
			$tables = [ 'page_google_stats' ];
			// Sql directory inside the extension folder
			$sql = __DIR__ . '/sql';
			// Extension of the table schema file (depending on the database type)
			switch ( $updater !== null ? $updater->getDB()->getType() : $wgDBtype ) {
				default:
					$ext = 'sql';
			}
			// Do the updating
			foreach ( $tables as $table ) {
				// Location of the table schema file
				$schema = "$sql/$table.$ext";
				$updater->addExtensionUpdate( [ 'addTable', $table, $schema, true ] );
			}
			return true;
		}

		/**
		 * ParserFirstCallInit Hook handler
		 *
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
		 * @param Parser $parser The parser object this hook is called from
		 * @return boolean
		 */
		public static function onParserFirstCallInit( Parser $parser ) {
			// set our parser tag
			$parser->setHook( 'gatp', 'GoogleAnalyticsTopPages::renderParserTag' );
		}
	}
