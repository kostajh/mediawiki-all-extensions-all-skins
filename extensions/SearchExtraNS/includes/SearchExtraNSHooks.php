<?php

class SearchExtraNSHooks {

	/**
	 * @param string $term
	 * @param Title &$title
	 * @return bool
	 */
	public static function NearMatch( $term, &$title ) {
		global $wgSearchExtraNamespaces;

		if ( !is_array( $wgSearchExtraNamespaces ) ) {
			return true;
		}

		foreach ( $wgSearchExtraNamespaces as $ens ) {
			$title = Title::newFromText( $term, $ens );
			if ( $title && $title->exists() ) {
				return false;
			}
		}

		return true;
	}
}
