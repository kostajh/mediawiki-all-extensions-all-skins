<?php
namespace BlueSpice\Social\Api\Store;

class TitleQuery extends \BSApiTitleQueryStore {

	/**
	 * Returns a List of Titles for the client side
	 * @param string $query A (maybe prefixed) title, or parts of a title
	 * that the store should look for
	 * @return array of objects
	 */
	protected function makeData( $query = '' ) {
		$data = array_filter( parent::makeData( $query ), function ( $row ) {
			return $row->type === 'wikipage';
		} );
		return array_values( $data );
	}
}
