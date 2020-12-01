<?php

namespace BlueSpice\Bookshelf\MassAdd;

interface IHandler {

	/**
	 * Creates an instance of the handler
	 *
	 * @param string $root
	 */
	public static function factory( $root );

	/**
	 * Returns unified list of pages
	 * based on root parameter
	 */
	public function getData();
}
