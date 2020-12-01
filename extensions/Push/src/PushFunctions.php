<?php

/**
 * Static class with utility methods for the Push extension.
 *
 * @since 0.2
 *
 * @file Push_Functions.php
 * @ingroup Push
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class PushFunctions {

	/**
	 * Returns the latest revision.
	 * Has support for the ApprovedRevs extension, and will
	 * return the latest approved revision where appropriate.
	 *
	 * @since 0.2
	 *
	 * @param Title $title
	 *
	 * @return int
	 */
	public static function getRevisionToPush( Title $title ) {
		if ( defined( 'APPROVED_REVS_VERSION' ) ) {
			$revId = ApprovedRevs::getApprovedRevID( $title );
			return $revId ?: $title->getLatestRevID();
		}

		return $title->getLatestRevID();
	}

	/**
	 * Expand a list of pages to include templates used in those pages.
	 *
	 * @since 0.4
	 *
	 * @param array $inputPages list of titles to look up
	 * @param array $pageSet associative array indexed by titles for output
	 *
	 * @return array associative array index by titles
	 */
	public static function getTemplates( $inputPages, $pageSet ) {
		return self::getLinks( $inputPages, $pageSet,
			'templatelinks',
			[ 'tl_namespace AS namespace', 'tl_title AS title' ],
			[ 'page_id=tl_from' ]
		);
	}

	/**
	 * Expand a list of pages to include items used in those pages.
	 *
	 * @since 0.4
	 * @param array $inputPages
	 * @param array $pageSet
	 * @param string $table
	 * @param array $fields
	 * @param array $join
	 * @return array
	 */
	protected static function getLinks( $inputPages, $pageSet, $table, $fields, $join ) {
		$dbr = wfGetDB( DB_REPLICA );

		foreach ( $inputPages as $page ) {
			$title = Title::newFromText( $page );

			if ( $title ) {
				$pageSet[$title->getPrefixedText()] = true;
				/// @todo Fixme: May or may not be more efficient to batch these
				///        by namespace when given multiple input pages.
				$result = $dbr->select(
					[ 'page', $table ],
					$fields,
					array_merge(
						$join,
						[
							'page_namespace' => $title->getNamespace(),
							'page_title' => $title->getDBkey()
						]
					),
					__METHOD__
				);

				foreach ( $result as $row ) {
					$template = Title::makeTitle( $row->namespace, $row->title );
					$pageSet[$template->getPrefixedText()] = true;
				}
			}
		}

		return $pageSet;
	}

	/**
	 * Function to change the keys of $egPushLoginUsers and $egPushLoginPasswords
	 * from target url to target name using the $egPushTargets array.
	 *
	 * @since 0.5
	 *
	 * @param array &$arr
	 * @param string $id Some string to identify the array and keep track of it having been flipped.
	 */
	public static function flipKeys( array &$arr, $id ) {
		static $handledArrays = [];

		if ( !in_array( $id, $handledArrays ) ) {
			$handledArrays[] = $id;

			global $egPushTargets;

			$flipped = [];

			foreach ( $arr as $key => $value ) {
				if ( array_key_exists( $key, $egPushTargets ) ) {
					$flipped[$egPushTargets[$key]] = $value;
				}
			}

			$arr = $flipped;
		}
	}
}
