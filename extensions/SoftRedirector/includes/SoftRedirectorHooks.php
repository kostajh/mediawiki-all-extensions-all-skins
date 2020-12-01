<?php
/**
 * Hooks for SoftRedirector extension
 *
 * @file
 * @ingroup Extensions
 */

class SoftRedirectorHooks {
	/**
	 * @param array &$doubleUnderscoreIDs
	 */
	public static function onGetDoubleUnderscoreIDs( &$doubleUnderscoreIDs ) {
		$doubleUnderscoreIDs[] = 'softredirect';
	}

	/**
	 * Add the SoftRedirector special pages to the list of QueryPages. This
	 * allows direct access via the API.
	 * @param array &$queryPages
	 */
	public static function onwgQueryPages( &$queryPages ) {
		$queryPages[] = [ SpecialSoftRedirectPages::class, 'SoftRedirectPages' ];
		$queryPages[] = [ SpecialSoftRedirectPageLinks::class, 'SoftRedirectPageLinks' ];
	}

	/**
	 * Modify query parameters to ignore soft redirect pages
	 * @param array &$tables
	 * @param array &$conds
	 * @param array &$joinConds
	 */
	private static function excludeSoftRedirectPages( &$tables, &$conds, &$joinConds ) {
		$tables['softredirpp'] = 'page_props';
		$conds['softredirpp.pp_page'] = null;
		$joinConds['softredirpp'] = [
			'LEFT JOIN', [ 'page_id = softredirpp.pp_page', 'softredirpp.pp_propname' => 'softredirect' ]
		];
	}

	/**
	 * Modify the Special:AncientPages query to ignore soft redirect pages
	 * @param array &$tables
	 * @param array &$conds
	 * @param array &$joinConds
	 */
	public static function onAncientPagesQuery( &$tables, &$conds, &$joinConds ) {
		self::excludeSoftRedirectPages( $tables, $conds, $joinConds );
	}

	/**
	 * Modify the Special:LonelyPages query to ignore soft redirect pages
	 * @param array &$tables
	 * @param array &$conds
	 * @param array &$joinConds
	 */
	public static function onLonelyPagesQuery( &$tables, &$conds, &$joinConds ) {
		self::excludeSoftRedirectPages( $tables, $conds, $joinConds );
	}

	/**
	 * Modify the Special:ShortPages query to ignore soft redirect pages
	 * @param array &$tables
	 * @param array &$conds
	 * @param array &$joinConds
	 * @param array &$options
	 */
	public static function onShortPagesQuery( &$tables, &$conds, &$joinConds, &$options ) {
		self::excludeSoftRedirectPages( $tables, $conds, $joinConds );
	}

	/**
	 * Modify the Special:Random query to ignore soft redirect pages
	 * @param array &$tables
	 * @param array &$conds
	 * @param array &$joinConds
	 */
	public static function onRandomPageQuery( &$tables, &$conds, &$joinConds ) {
		self::excludeSoftRedirectPages( $tables, $conds, $joinConds );
	}

	/**
	 * Convenience function for testing whether or not a page is a soft redirect page
	 * @param Title $title object of a page
	 * @param bool $includeRedirects Whether to consider redirects to soft redirects as
	 *   soft redirects.
	 * @return bool
	 */
	public static function isSoftRedirectPage( Title $title, $includeRedirects = true ) {
		$res = static::filtersoftredirectPageIds(
			[ $title->getArticleID() ], $includeRedirects );
		return (bool)count( $res );
	}

	/**
	 * Convenience function for testing whether or not pages are soft redirect pages
	 * @param int[] $pageIds
	 * @param bool $includeRedirects Whether to consider redirects to soft redirects as
	 *   soft redirects.
	 * @return int[] The page ids corresponding to pages that are soft redirects
	 */
	private static function filtersoftredirectPageIds(
		array $pageIds, $includeRedirects = true
	) {
		// Don't needlessly check non-existent and special pages
		$pageIds = array_filter(
			$pageIds,
			function ( $id ) {
				return $id > 0;
			}
		);

		$output = [];
		if ( $pageIds ) {
			$dbr = wfGetDB( DB_REPLICA );

			$redirects = [];
			if ( $includeRedirects ) {
				// resolve redirects
				$res = $dbr->select(
					[ 'page', 'redirect' ],
					[ 'page_id', 'rd_from' ],
					[ 'rd_from' => $pageIds ],
					__METHOD__,
					[],
					[ 'page' => [ 'INNER JOIN', [
						'rd_namespace=page_namespace',
						'rd_title=page_title'
					] ] ]
				);

				foreach ( $res as $row ) {
					// Key is the destination page ID, value is the source page ID
					$redirects[$row->page_id] = $row->rd_from;
				}
			}
			$pageIdsWithRedirects = array_merge( array_keys( $redirects ),
				array_diff( $pageIds, array_values( $redirects ) ) );
			$res = $dbr->select(
				'page_props',
				'pp_page',
				[ 'pp_page' => $pageIdsWithRedirects, 'pp_propname' => 'softredirect' ],
				__METHOD__
			);

			foreach ( $res as $row ) {
				$softredirectPageId = $row->pp_page;
				if ( array_key_exists( $softredirectPageId, $redirects ) ) {
					$output[] = $redirects[$softredirectPageId];
				}
				if ( in_array( $softredirectPageId, $pageIds ) ) {
					$output[] = $softredirectPageId;
				}
			}
		}

		return $output;
	}

	/**
	 * Add 'mw-softredir' CSS class to links to soft redirect pages.
	 * @param array $pageIdToDbKey Prefixed DB keys of the pages linked to, indexed by page_id
	 * @param array &$colours CSS classes, indexed by prefixed DB keys
	 */
	public static function onGetLinkColours( $pageIdToDbKey, &$colours ) {
		global $wgSoftRedirectIndicateLinks;
		if ( !$wgSoftRedirectIndicateLinks ) {
			return;
		}

		$pageIds = static::filtersoftredirectPageIds( array_keys( $pageIdToDbKey ) );
		foreach ( $pageIds as $pageId ) {
			if ( isset( $colours[ $pageIdToDbKey[$pageId] ] ) ) {
				$colours[ $pageIdToDbKey[$pageId] ] .= ' mw-softredir';
			} else {
				$colours[ $pageIdToDbKey[$pageId] ] = 'mw-softredir';
			}
		}
	}
}
