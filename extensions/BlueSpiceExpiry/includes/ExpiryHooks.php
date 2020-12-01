<?php

class ExpiryHooks {

	/**
	 *
	 * @param array &$wgQueryPages
	 * @return bool
	 */
	public static function onQueryPages( &$wgQueryPages ) {
		$wgQueryPages[] = [ 'SpecialExpiry', 'Expired_Articles' ];
		return true;
	}
}
