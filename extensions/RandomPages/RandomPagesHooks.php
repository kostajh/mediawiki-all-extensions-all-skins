<?php

class RandomPagesHooks {

	/**
	 * Setups the mediawiki hook
	 *
	 * @param Parser $parser
	 */
	public static function aoRandomPagesInit( $parser ) {
		$parser->setHook( 'randompages', [ __CLASS__, 'aoRandomPagesHook' ] );
	}

	/**
	 * Callback that replaces <randompages /> wiki tag with a list of links to random wiki pages
	 *
	 * Available options:
	 *
	 * * limit int, to control how many links should be fetched randomly from the database,
	 * defaults to 150
	 *
	 * * namespace bool, true to restrict only to the global namspace, defaults to false
	 * * levels int, levels of CSS applyed to each entry, defaluts to 5
	 *
	 * Sample Usage:
	 *
	 * <code>
	 * <randompages limit="10" namespace="true" levels="10" />
	 * </code>
	 *
	 * Gets 10 random pages from the global namespace with 10 levels of style
	 *
	 * @param string|null $text
	 * @param array $params Additional parameters passed as attributes to randompages tag
	 * @param Parser $parser The Wiki Parser Object
	 * @return string
	 */
	public static function aoRandomPagesHook( $text, $params, $parser ) {
		global $wgDBprefix;
		// Prevent caching for this wiki page
		$parser->getOutput()->updateCacheExpiry( 0 );
		// Get parameters
		$limit = (int)( $params['limit'] ?? 150 );
		$namespaced = isset( $params['namespace'] ) ? $params['namespace'] == 'true' : false;
		$levels = isset( $params['levels'] ) ? (int)$params['levels'] : 5;
		// Build sql query
		$sql = sprintf( 'select * from %spage where', $wgDBprefix );
		$sql .= $namespaced ? ' page_namespace = 0 ' : ' 1=1 ';
		$sql .= sprintf( 'order by rand() limit %d', $limit );
		// Execute that.
		$dbr = wfGetDB( DB_REPLICA );
		$rs = $dbr->query( $sql );
		$buff = '<div class="randomPages">';
		while ( $row = $rs->fetchObject( $rs ) ) {
			$buff .= '<span class="randomPages_level' . rand( 1, $levels ) . '">';
			# KKM comment out as it is now working.
			# https://www.mediawiki.org/wiki/Extension_talk:SearchBox
			$title = Title::makeTitleSafe( $row->page_namespace, $row->page_title );
			$buff .= sprintf(
				'<a href="%s" title="%s">%s</a><br>',
				htmlspecialchars( $title->getLocalURL() ),
				htmlspecialchars( $title->getPrefixedText() ),
				htmlspecialchars( $title->getPrefixedText() )
			);
			$buff .= '</span>';
		}
		return $buff . '</div>';
	}
}
