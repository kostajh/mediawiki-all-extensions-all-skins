<?php
class SubpageSortkey {
	/**
	 * The GetDefaultSortkey hook.
	 * Basically prefixes the normal sortkey with some of the subpage
	 * parts of the current title.
	 *
	 * Example, if configured with "1,3..5,7" and given the
	 * page 0/1/2/3/4/5/6/7/8 it would prefix the sortkey with
	 * 1/3/4/5/7. Adding it to the normal sortkey,
	 * resulting in "0/1/3/4/5/7\n0/1/2/3/4/5/6/7/8".
	 * From there it might be further prefixed with whatever the
	 * {{DEFAULTSORT for a page is.
	 *
	 * Another example: Configuration -3..-1 turns 1/2/3/4/5 -> 3/4
	 * and -3.. turns 1/2/3/4/5 -> 3/4/5
	 * @param Title $title
	 * @param string &$unprefixed
	 * @return true
	 */
	public static function onGetDefaultSortkey( $title, &$unprefixed ) {
		global $wgSubpageSortkeyDefault,
			$wgSubpageSortkeyByNamespace,
			$wgSubpageSortkeyIfNoSubpageUseFullName;

		$newSortkey = [];

		$ns = $title->getNamespace();
		if ( !MWNamespace::hasSubpages( $ns ) ) {
			// Do nothing
			return true;
		}

		if ( isset( $wgSubpageSortkeyByNamespace[$ns] ) ) {
			$descript = $wgSubpageSortkeyByNamespace[$ns];
		} else {
			$descript = $wgSubpageSortkeyDefault;
		}

		$elms = explode( ',', $descript );
		foreach ( $elms as $item ) {
			$ranges = explode( '..', $item, 2 );
			$start = intval( $ranges[0] );
			if ( count( $ranges ) === 1 ) {
				$count = 1;
			} elseif ( $ranges[1] === '' ) {
				$count = false;
			} else {
				$count = intval( $ranges[1] );
			}
			$newSortkey = array_merge( $newSortkey,
				self::getSubpage( $start, $count, $title ) );
		}
		$newPrefix = implode( '/', $newSortkey );

		// Don't prefix an extra \n if the prefix is empty.
		if ( $newPrefix !== ''
			|| !$wgSubpageSortkeyIfNoSubpageUseFullName
		) {
			$unprefixed = $newPrefix . "\n" . $unprefixed;
		}
		return true;
	}

	/**
	 * @param int $index starting index of subpage.
	 * @param int $count how many elements, or false to denote all
	 * @param Title $title
	 * @return string[] array of subpages
	 */
	private static function getSubpage( $index, $count, $title ) {
		$subpages = explode( '/', $title->getText() );
		$numb = count( $subpages );

		if ( $index > $numb ) {
			return [];
		}

		if ( $count === false || $index + $count > $numb + 1 ) {
			$count = $numb + 1 - $index;
		}
		return array_slice( $subpages, $index, $count );
	}
}
